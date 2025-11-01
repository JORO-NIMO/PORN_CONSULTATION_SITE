<?php
namespace App\Models;

use PDO;
use App\Core\App;

class Counselor {
    protected $db;
    
    public function __construct() {
        $this->db = App::getInstance()->db();
    }
    
    /**
     * Find counselor by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare(
            "SELECT u.*, 
                    c.license_number, 
                    c.specialization, 
                    c.years_of_experience,
                    c.qualifications,
                    c.languages,
                    c.hourly_rate,
                    c.availability,
                    c.working_hours,
                    c.about,
                    c.consultation_fee,
                    c.is_available,
                    c.rating,
                    c.total_sessions
             FROM users u
             JOIN counselors c ON u.id = c.user_id
             WHERE u.id = ? AND u.role = 'counselor' AND u.deleted_at IS NULL"
        );
        
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }
    
    /**
     * Get all available counselors
     */
    public function getAvailableCounselors($filters = []) {
        $where = ["u.role = 'counselor' AND u.is_active = 1 AND c.is_available = 1"];
        $params = [];
        
        // Apply filters
        if (!empty($filters['specialization'])) {
            $where[] = "c.specialization = ?";
            $params[] = $filters['specialization'];
        }
        
        if (!empty($filters['min_rating'])) {
            $where[] = "c.rating >= ?";
            $params[] = $filters['min_rating'];
        }
        
        if (!empty($filters['language'])) {
            $where[] = "c.languages LIKE ?";
            $params[] = '%' . $filters['language'] . '%';
        }
        
        $sql = "SELECT u.id, 
                       u.first_name, 
                       u.last_name, 
                       u.profile_image,
                       c.specialization,
                       c.years_of_experience,
                       c.rating,
                       c.total_sessions,
                       c.consultation_fee,
                       c.about
                FROM users u
                JOIN counselors c ON u.id = c.user_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY c.rating DESC, c.total_sessions DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Create or update counselor profile
     */
    public function saveProfile($userId, $data) {
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'bio' => $data['bio'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'postal_code' => $data['postal_code'] ?? null
        ];
        
        // Handle profile image upload
        if (!empty($_FILES['profile_image']['name'])) {
            $uploadPath = $this->handleProfileImageUpload($_FILES['profile_image'], $userId);
            if ($uploadPath) {
                $userData['profile_image'] = $uploadPath;
            }
        }
        
        // Update user details
        $userModel = new User();
        $userModel->update($userId, $userData);
        
        // Prepare counselor data
        $counselorData = [
            'license_number' => $data['license_number'],
            'specialization' => $data['specialization'],
            'years_of_experience' => $data['years_of_experience'],
            'qualifications' => $data['qualifications'],
            'languages' => is_array($data['languages'] ?? null) ? 
                json_encode($data['languages']) : 
                ($data['languages'] ?? '["English"]'),
            'hourly_rate' => $data['hourly_rate'],
            'availability' => json_encode($data['availability'] ?? []),
            'working_hours' => json_encode($data['working_hours'] ?? []),
            'about' => $data['about'] ?? null,
            'consultation_fee' => $data['consultation_fee'] ?? 0,
            'is_available' => $data['is_available'] ?? 1
        ];
        
        // Check if counselor profile exists
        $existing = $this->db->prepare(
            "SELECT id FROM counselors WHERE user_id = ?"
        );
        
        $existing->execute([$userId]);
        
        if ($existing->fetch()) {
            // Update existing profile
            $this->updateCounselorProfile($userId, $counselorData);
        } else {
            // Create new profile
            $this->createCounselorProfile($userId, $counselorData);
        }
        
        return true;
    }
    
    /**
     * Create counselor profile
     */
    protected function createCounselorProfile($userId, $data) {
        $sql = "INSERT INTO counselors (
                    user_id, 
                    license_number, 
                    specialization, 
                    years_of_experience,
                    qualifications,
                    languages,
                    hourly_rate,
                    availability,
                    working_hours,
                    about,
                    consultation_fee,
                    is_available,
                    created_at,
                    updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $data['license_number'],
            $data['specialization'],
            $data['years_of_experience'],
            $data['qualifications'],
            $data['languages'],
            $data['hourly_rate'],
            $data['availability'],
            $data['working_hours'],
            $data['about'],
            $data['consultation_fee'],
            $data['is_available']
        ]);
    }
    
    /**
     * Update counselor profile
     */
    protected function updateCounselorProfile($userId, $data) {
        $sql = "UPDATE counselors SET 
                    license_number = ?,
                    specialization = ?,
                    years_of_experience = ?,
                    qualifications = ?,
                    languages = ?,
                    hourly_rate = ?,
                    availability = ?,
                    working_hours = ?,
                    about = ?,
                    consultation_fee = ?,
                    is_available = ?,
                    updated_at = NOW()
                WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['license_number'],
            $data['specialization'],
            $data['years_of_experience'],
            $data['qualifications'],
            $data['languages'],
            $data['hourly_rate'],
            $data['availability'],
            $data['working_hours'],
            $data['about'],
            $data['consultation_fee'],
            $data['is_available'],
            $userId
        ]);
    }
    
    /**
     * Handle profile image upload
     */
    protected function handleProfileImageUpload($file, $userId) {
        $uploadDir = ROOT_PATH . '/public/uploads/profiles/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Generate unique filename
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'profile_' . $userId . '_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Resize and crop image
            $this->resizeImage($targetPath, 300, 300);
            return '/uploads/profiles/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Resize and crop image
     */
    protected function resizeImage($filePath, $width, $height) {
        // Implementation similar to the one in DashboardController
        // ...
    }
    
    /**
     * Update counselor availability
     */
    public function updateAvailability($userId, $availability) {
        $data = [
            'availability' => json_encode($availability),
            'is_available' => !empty($availability['is_available'])
        ];
        
        return $this->updateCounselorProfile($userId, $data);
    }
    
    /**
     * Update working hours
     */
    public function updateWorkingHours($userId, $workingHours) {
        $data = [
            'working_hours' => json_encode($workingHours)
        ];
        
        return $this->updateCounselorProfile($userId, $data);
    }
    
    /**
     * Get counselor's upcoming appointments
     */
    public function getUpcomingAppointments($counselorId, $limit = 5) {
        $appointmentModel = new Appointment();
        return $appointmentModel->getByCounselor($counselorId, [
            'start_date' => date('Y-m-d H:i:s'),
            'status' => 'scheduled',
            'limit' => $limit
        ]);
    }
    
    /**
     * Get counselor's availability for a specific date
     */
    public function getAvailability($counselorId, $date) {
        $counselor = $this->find($counselorId);
        
        if (!$counselor) {
            return [];
        }
        
        $workingHours = json_decode($counselor->working_hours, true) ?? [];
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        
        if (empty($workingHours[$dayOfWeek]['enabled'])) {
            return [];
        }
        
        $availability = [
            'start' => $workingHours[$dayOfWeek]['start'],
            'end' => $workingHours[$dayOfWeek]['end'],
            'breaks' => $workingHours[$dayOfWeek]['breaks'] ?? [],
            'is_available' => $counselor->is_available
        ];
        
        // Get booked appointments for the day
        $appointmentModel = new Appointment();
        $appointments = $appointmentModel->getByCounselor($counselorId, [
            'start_date' => date('Y-m-d', strtotime($date)) . ' 00:00:00',
            'end_date' => date('Y-m-d', strtotime($date)) . ' 23:59:59',
            'status' => 'scheduled'
        ]);
        
        // Add booked slots to availability data
        $bookedSlots = [];
        foreach ($appointments as $appt) {
            $bookedSlots[] = [
                'start' => date('H:i', strtotime($appt->appointment_date)),
                'end' => date('H:i', strtotime($appt->appointment_date) + ($appt->duration * 60))
            ];
        }
        
        $availability['booked_slots'] = $bookedSlots;
        
        return $availability;
    }
    
    /**
     * Get counselor's schedule for a date range
     */
    public function getSchedule($counselorId, $startDate, $endDate) {
        $schedule = [];
        $currentDate = new \DateTime($startDate);
        $endDate = new \DateTime($endDate);
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $schedule[$dateStr] = $this->getAvailability($counselorId, $dateStr);
            $currentDate->modify('+1 day');
        }
        
        return $schedule;
    }
    
    /**
     * Get counselor statistics
     */
    public function getStats($counselorId) {
        $appointmentModel = new Appointment();
        $stats = $appointmentModel->getStats(null, $counselorId);
        
        // Add additional counselor-specific stats
        $counselor = $this->find($counselorId);
        
        if ($counselor) {
            $stats['average_rating'] = (float)$counselor->rating;
            $stats['total_earnings'] = $counselor->total_earnings ?? 0;
            $stats['response_rate'] = $this->calculateResponseRate($counselorId);
            $stats['client_retention'] = $this->calculateClientRetention($counselorId);
        }
        
        return $stats;
    }
    
    /**
     * Calculate response rate
     */
    protected function calculateResponseRate($counselorId) {
        // Implementation depends on your messaging system
        // This is a simplified example
        $sql = "SELECT 
                    COUNT(*) as total_messages,
                    SUM(CASE WHEN responded_at IS NOT NULL THEN 1 ELSE 0 END) as responded
                FROM messages
                WHERE counselor_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$counselorId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($result->total_messages > 0) {
            return round(($result->responded / $result->total_messages) * 100, 1);
        }
        
        return 0;
    }
    
    /**
     * Calculate client retention rate
     */
    protected function calculateClientRetention($counselorId) {
        // Implementation depends on your business logic
        // This is a simplified example
        $sql = "SELECT 
                    COUNT(DISTINCT user_id) as total_clients,
                    COUNT(DISTINCT CASE WHEN appointment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as returning_clients
                FROM appointments
                WHERE counselor_id = ?
                AND status = 'completed'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$counselorId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($result->total_clients > 0) {
            return round(($result->returning_clients / $result->total_clients) * 100, 1);
        }
        
        return 0;
    }
}
