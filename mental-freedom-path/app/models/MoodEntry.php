<?php
namespace App\Models;

use PDO;
use App\Core\App;

class MoodEntry {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Create a new mood entry
     */
    public function create($data) {
        $sql = "INSERT INTO mood_entries (user_id, mood_level, notes, entry_date) 
                VALUES (?, ?, ?, ?)";
                
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['user_id'],
            $data['mood_level'],
            $data['notes'] ?? null,
            $data['entry_date'] ?? date('Y-m-d')
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update a mood entry
     */
    public function update($id, $data) {
        $updates = [];
        $params = [];
        
        $allowedFields = ['mood_level', 'notes', 'entry_date'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $updates[] = "$key = ?";
                $params[] = $value;
            }
        }
        
        if (empty($updates)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE mood_entries SET " . implode(', ', $updates) . " 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Get mood entry by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM mood_entries WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get mood entry by user and date
     */
    public function getByDate($userId, $date) {
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries 
             WHERE user_id = ? AND DATE(entry_date) = ? 
             ORDER BY created_at DESC LIMIT 1"
        );
        
        $stmt->execute([$userId, $date]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get recent mood entries for a user
     */
    public function getRecentByUser($userId, $limit = 7) {
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries 
             WHERE user_id = ? 
             ORDER BY entry_date DESC 
             LIMIT ?"
        );
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get mood entries for a specific month
     */
    public function getByMonth($userId, $month, $year) {
        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries 
             WHERE user_id = ? 
             AND entry_date BETWEEN ? AND ? 
             ORDER BY entry_date"
        );
        
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get mood entries within a date range
     */
    public function getDateRange($userId, $start, $end) {
        $stmt = $this->db->prepare(
            "SELECT * FROM mood_entries 
             WHERE user_id = ? 
             AND entry_date BETWEEN ? AND ? 
             ORDER BY entry_date"
        );
        
        $stmt->execute([$userId, $start, $end]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get mood statistics
     */
    public function getStats($userId, $month = null, $year = null) {
        $stats = [
            'average' => 0,
            'count' => 0,
            'by_day' => [],
            'by_mood' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0]
        ];
        
        // Base query
        $sql = "SELECT 
                    AVG(mood_level) as avg_mood,
                    COUNT(*) as total_entries,
                    DAYOFWEEK(entry_date) as day_of_week,
                    mood_level,
                    COUNT(*) as mood_count
                FROM mood_entries 
                WHERE user_id = ?";
        
        $params = [$userId];
        
        // Add date filters if provided
        if ($month && $year) {
            $startDate = "{$year}-{$month}-01";
            $endDate = date('Y-m-t', strtotime($startDate));
            
            $sql .= " AND entry_date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        // Group by day of week and mood level
        $sql .= " GROUP BY DAYOFWEEK(entry_date), mood_level WITH ROLLUP";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        // Process results
        foreach ($results as $row) {
            if ($row->day_of_week === null && $row->mood_level === null) {
                // This is the grand total row
                $stats['average'] = round($row->avg_mood, 1);
                $stats['count'] = $row->total_entries;
            } elseif ($row->mood_level === null) {
                // This is a day of week subtotal
                $stats['by_day'][$row->day_of_week] = round($row->avg_mood, 1);
            } elseif ($row->day_of_week === null) {
                // This is a mood level subtotal
                $stats['by_mood'][$row->mood_level] = $row->mood_count;
            }
        }
        
        return $stats;
    }
    
    /**
     * Get mood trends over time
     */
    public function getTrends($userId, $period = 'week') {
        $endDate = date('Y-m-d');
        
        switch ($period) {
            case 'month':
                $startDate = date('Y-m-d', strtotime('-1 month'));
                $groupBy = 'DATE(entry_date)';
                break;
            case 'year':
                $startDate = date('Y-m-d', strtotime('-1 year'));
                $groupBy = 'WEEK(entry_date, 1)';
                break;
            case 'week':
            default:
                $startDate = date('Y-m-d', strtotime('-1 week'));
                $groupBy = 'DATE(entry_date)';
                break;
        }
        
        $sql = "SELECT 
                    {$groupBy} as period,
                    AVG(mood_level) as avg_mood,
                    COUNT(*) as entry_count
                FROM mood_entries 
                WHERE user_id = ? 
                AND entry_date BETWEEN ? AND ?
                GROUP BY {$groupBy}
                ORDER BY period";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $startDate, $endDate]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get mood correlations with activities
     */
    public function getActivityCorrelations($userId) {
        // This would require an activities table and user_activities table
        // Implementation depends on your specific database schema
        return [];
    }
}
