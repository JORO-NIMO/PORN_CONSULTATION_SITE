<?php
namespace App\Models;

use PDO;
use App\Core\App;

class Appointment {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Create a new appointment
     */
    public function create($data) {
        $sql = "INSERT INTO appointments (
                    user_id, 
                    counselor_id, 
                    appointment_date, 
                    duration, 
                    notes, 
                    status,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['user_id'],
            $data['counselor_id'],
            $data['appointment_date'],
            $data['duration'] ?? 60, // Default to 60 minutes
            $data['notes'] ?? null,
            $data['status'] ?? 'scheduled'
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update an appointment
     */
    public function update($id, $data) {
        $allowedFields = [
            'counselor_id', 'appointment_date', 'duration', 
            'notes', 'status', 'video_meeting_id', 'meeting_url'
        ];
        
        $updates = [];
        $params = [];
        
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
        
        $sql = "UPDATE appointments SET " . implode(', ', $updates) . 
               ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Cancel an appointment
     */
    public function cancel($id) {
        return $this->update($id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get appointment by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare(
            "SELECT a.*, 
                    u.first_name as user_first_name,
                    u.last_name as user_last_name,
                    u.email as user_email,
                    c.first_name as counselor_first_name,
                    c.last_name as counselor_last_name,
                    c.specialization as counselor_specialization
             FROM appointments a
             LEFT JOIN users u ON a.user_id = u.id
             LEFT JOIN users c ON a.counselor_id = c.id
             WHERE a.id = ?"
        );
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get upcoming appointments for a user
     */
    public function getUpcomingByUser($userId, $limit = 5) {
        $stmt = $this->db->prepare(
            "SELECT a.*, 
                    c.first_name as counselor_first_name,
                    c.last_name as counselor_last_name,
                    c.specialization as counselor_specialization,
                    c.profile_image as counselor_image
             FROM appointments a
             LEFT JOIN users c ON a.counselor_id = c.id
             WHERE a.user_id = ? 
             AND a.appointment_date >= NOW() 
             AND a.status = 'scheduled'
             ORDER BY a.appointment_date
             LIMIT ?"
        );
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get all appointments for a user
     */
    public function getByUser($userId, $filters = []) {
        $params = [$userId];
        $where = ["a.user_id = ?"];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "a.appointment_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "a.appointment_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql = "SELECT a.*, 
                       c.first_name as counselor_first_name,
                       c.last_name as counselor_last_name,
                       c.specialization as counselor_specialization,
                       c.profile_image as counselor_image
                FROM appointments a
                LEFT JOIN users c ON a.counselor_id = c.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.appointment_date DESC";
        
        // Add pagination if needed
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Get appointments for a counselor
     */
    public function getByCounselor($counselorId, $filters = []) {
        $params = [$counselorId];
        $where = ["a.counselor_id = ?"];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "a.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $where[] = "a.appointment_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $where[] = "a.appointment_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        $sql = "SELECT a.*, 
                       u.first_name as user_first_name,
                       u.last_name as user_last_name,
                       u.email as user_email,
                       u.phone as user_phone
                FROM appointments a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.appointment_date";
        
        // Add pagination if needed
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int)$filters['limit'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Check counselor availability
     */
    public function isCounselorAvailable($counselorId, $startTime, $duration, $excludeAppointmentId = null) {
        $endTime = date('Y-m-d H:i:s', strtotime($startTime) + ($duration * 60));
        
        $sql = "SELECT COUNT(*) as count 
                FROM appointments 
                WHERE counselor_id = ? 
                AND status = 'scheduled'
                AND (
                    (appointment_date <= ? AND DATE_ADD(appointment_date, INTERVAL duration MINUTE) > ?) OR
                    (appointment_date < ? AND DATE_ADD(appointment_date, INTERVAL duration MINUTE) >= ?) OR
                    (appointment_date >= ? AND DATE_ADD(appointment_date, INTERVAL duration MINUTE) <= ?)
                )";
        
        $params = [
            $counselorId,
            $startTime, $startTime,
            $endTime, $endTime,
            $startTime, $endTime
        ];
        
        // Exclude current appointment when updating
        if ($excludeAppointmentId) {
            $sql .= " AND id != ?";
            $params[] = $excludeAppointmentId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result->count == 0;
    }
    
    /**
     * Get available time slots for a counselor
     */
    public function getAvailableSlots($counselorId, $date, $duration = 60) {
        // Get counselor's working hours
        $counselorModel = new Counselor();
        $counselor = $counselorModel->find($counselorId);
        
        if (!$counselor) {
            return [];
        }
        
        $workingHours = json_decode($counselor->working_hours, true) ?? [];
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        if (empty($workingHours[$dayOfWeek]['enabled'])) {
            return [];
        }
        
        $startTime = strtotime($workingHours[$dayOfWeek]['start']);
        $endTime = strtotime($workingHours[$dayOfWeek]['end']);
        $slotDuration = $duration * 60; // in seconds
        $availableSlots = [];
        
        // Get existing appointments for the day
        $appointments = $this->getByCounselor($counselorId, [
            'start_date' => date('Y-m-d', strtotime($date)) . ' 00:00:00',
            'end_date' => date('Y-m-d', strtotime($date)) . ' 23:59:59',
            'status' => 'scheduled'
        ]);
        
        // Convert appointments to time ranges for easier comparison
        $bookedRanges = [];
        foreach ($appointments as $appt) {
            $bookedRanges[] = [
                'start' => strtotime($appt->appointment_date),
                'end' => strtotime($appt->appointment_date) + ($appt->duration * 60)
            ];
        }
        
        // Generate available slots
        $currentTime = $startTime;
        
        while (($currentTime + $slotDuration) <= $endTime) {
            $slotStart = $currentTime;
            $slotEnd = $currentTime + $slotDuration;
            $isAvailable = true;
            
            // Check if slot overlaps with any booked appointments
            foreach ($bookedRanges as $range) {
                if ($slotStart < $range['end'] && $slotEnd > $range['start']) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = [
                    'start' => date('H:i', $slotStart),
                    'end' => date('H:i', $slotEnd),
                    'timestamp' => date('Y-m-d H:i:s', $slotStart)
                ];
            }
            
            // Move to next slot (15-minute intervals)
            $currentTime += 900; // 15 minutes in seconds
        }
        
        return $availableSlots;
    }
    
    /**
     * Get appointment statistics
     */
    public function getStats($userId = null, $counselorId = null, $period = 'month') {
        $stats = [
            'total' => 0,
            'completed' => 0,
            'upcoming' => 0,
            'cancelled' => 0,
            'by_status' => [],
            'by_month' => []
        ];
        
        $where = [];
        $params = [];
        
        if ($userId) {
            $where[] = "user_id = ?";
            $params[] = $userId;
        }
        
        if ($counselorId) {
            $where[] = "counselor_id = ?";
            $params[] = $counselorId;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        
        // Get counts by status
        $sql = "SELECT 
                    status, 
                    COUNT(*) as count
                FROM appointments
                $whereClause
                GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $statusCounts = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($statusCounts as $row) {
            $stats['by_status'][$row->status] = (int)$row->count;
            $stats['total'] += $row->count;
            
            if ($row->status === 'completed') {
                $stats['completed'] = (int)$row->count;
            } elseif ($row->status === 'scheduled') {
                $stats['upcoming'] = (int)$row->count;
            } elseif ($row->status === 'cancelled') {
                $stats['cancelled'] = (int)$row->count;
            }
        }
        
        // Get monthly stats
        $sql = "SELECT 
                    DATE_FORMAT(appointment_date, '%Y-%m') as month,
                    COUNT(*) as count
                FROM appointments
                $whereClause
                AND appointment_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
                GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
                ORDER BY month";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $monthlyStats = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($monthlyStats as $row) {
            $stats['by_month'][$row->month] = (int)$row->count;
        }
        
        return $stats;
    }
    
    /**
     * Send appointment reminders
     */
    public function sendReminders() {
        // Get appointments starting in the next 24 hours
        $sql = "SELECT a.*, 
                       u.first_name as user_first_name,
                       u.last_name as user_last_name,
                       u.email as user_email,
                       c.first_name as counselor_first_name,
                       c.last_name as counselor_last_name,
                       c.email as counselor_email
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                JOIN users c ON a.counselor_id = c.id
                WHERE a.status = 'scheduled'
                AND a.appointment_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
                AND (a.reminder_sent IS NULL OR a.reminder_sent < DATE_SUB(NOW(), INTERVAL 12 HOUR))";
        
        $stmt = $this->db->query($sql);
        $appointments = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $mailer = new \App\Core\Mailer();
        $reminderCount = 0;
        
        foreach ($appointments as $appt) {
            // Send email to user
            $mailer->send(
                $appt->user_email,
                'Upcoming Appointment Reminder',
                'emails/appointment-reminder',
                [
                    'user' => [
                        'first_name' => $appt->user_first_name,
                        'last_name' => $appt->user_last_name
                    ],
                    'counselor' => [
                        'first_name' => $appt->counselor_first_name,
                        'last_name' => $appt->counselor_last_name
                    ],
                    'appointment' => [
                        'date' => date('F j, Y', strtotime($appt->appointment_date)),
                        'time' => date('g:i A', strtotime($appt->appointment_date)),
                        'duration' => $appt->duration,
                        'meeting_url' => $appt->meeting_url
                    ]
                ]
            );
            
            // Update reminder sent timestamp
            $this->update($appt->id, ['reminder_sent' => date('Y-m-d H:i:s')]);
            $reminderCount++;
        }
        
        return $reminderCount;
    }
}
