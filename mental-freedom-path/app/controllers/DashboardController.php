<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\MoodEntry;

class DashboardController extends Controller {
    protected $userModel;
    protected $appointmentModel;
    protected $moodModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->appointmentModel = new Appointment();
        $this->moodModel = new MoodEntry();
        $this->requireAuth();
    }
    
    /**
     * Show user dashboard
     */
    public function index() {
        $user = $this->auth->user();
        
        // Get upcoming appointments
        $upcomingAppointments = $this->appointmentModel->getUpcomingByUser($user->id, 3);
        
        // Get recent mood entries
        $moodEntries = $this->moodModel->getRecentByUser($user->id, 7);
        
        // Calculate mood statistics
        $moodStats = $this->moodModel->getStats($user->id);
        
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'upcomingAppointments' => $upcomingAppointments,
            'moodEntries' => $moodEntries,
            'moodStats' => $moodStats
        ]);
    }
    
    /**
     * Show user profile
     */
    public function profile() {
        $user = $this->auth->user();
        
        if ($this->request->isPost()) {
            $this->updateProfile($user);
        }
        
        $this->view('dashboard/profile', [
            'title' => 'My Profile',
            'user' => $user
        ]);
    }
    
    /**
     * Update user profile
     */
    protected function updateProfile($user) {
        $data = $this->request->only([
            'first_name', 'last_name', 'email', 'phone', 'bio',
            'date_of_birth', 'gender', 'address', 'city', 'country'
        ]);
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $this->handleAvatarUpload($_FILES['avatar'], $user->id);
            if ($avatarPath) {
                $data['avatar'] = $avatarPath;
            }
        }
        
        if ($this->userModel->update($user->id, $data)) {
            $this->session->setFlash('success', 'Profile updated successfully');
            $this->redirect('/dashboard/profile');
        } else {
            $this->session->setFlash('error', 'Failed to update profile');
        }
    }
    
    /**
     * Handle avatar upload
     */
    protected function handleAvatarUpload($file, $userId) {
        $uploadDir = ROOT_PATH . '/public/uploads/avatars/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = 'user_' . $userId . '_' . time() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->session->setFlash('error', 'Invalid file type. Only JPG, PNG and GIF are allowed.');
            return false;
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Resize image if needed
            $this->resizeImage($targetPath, 200, 200);
            return '/uploads/avatars/' . $fileName;
        }
        
        return false;
    }
    
    /**
     * Resize image
     */
    protected function resizeImage($filePath, $width, $height) {
        $info = getimagesize($filePath);
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filePath);
                break;
            default:
                return false;
        }
        
        $srcWidth = imagesx($image);
        $srcHeight = imagesy($image);
        
        // Calculate aspect ratio
        $srcRatio = $srcWidth / $srcHeight;
        $dstRatio = $width / $height;
        
        if ($dstRatio > $srcRatio) {
            $newHeight = $height;
            $newWidth = $height * $srcRatio;
        } else {
            $newWidth = $width;
            $newHeight = $width / $srcRatio;
        }
        
        // Create new image
        $newImage = imagecreatetruecolor($width, $height);
        
        // Handle transparency for PNG/GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }
        
        // Resize and crop
        $dstX = ($width - $newWidth) / 2;
        $dstY = ($height - $newHeight) / 2;
        
        imagecopyresampled(
            $newImage, $image,
            $dstX, $dstY, 0, 0,
            $newWidth, $newHeight,
            $srcWidth, $srcHeight
        );
        
        // Save the image
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($newImage, $filePath, 90);
                break;
            case 'image/png':
                imagepng($newImage, $filePath, 9);
                break;
            case 'image/gif':
                imagegif($newImage, $filePath);
                break;
        }
        
        // Free up memory
        imagedestroy($image);
        imagedestroy($newImage);
        
        return true;
    }
    
    /**
     * Show appointment scheduling
     */
    public function appointments() {
        $user = $this->auth->user();
        
        if ($this->request->isPost()) {
            $this->handleAppointmentRequest($user->id);
        }
        
        // Get available counselors
        $counselorModel = new \App\Models\Counselor();
        $counselors = $counselorModel->getAvailableCounselors();
        
        // Get user's appointments
        $appointments = $this->appointmentModel->getByUser($user->id);
        
        $this->view('dashboard/appointments', [
            'title' => 'My Appointments',
            'counselors' => $counselors,
            'appointments' => $appointments
        ]);
    }
    
    /**
     * Handle appointment scheduling
     */
    protected function handleAppointmentRequest($userId) {
        $data = $this->request->only([
            'counselor_id', 'appointment_date', 'duration', 'notes'
        ]);
        
        // Validate input
        $errors = [];
        
        if (empty($data['counselor_id'])) {
            $errors['counselor_id'] = 'Please select a counselor';
        }
        
        if (empty($data['appointment_date'])) {
            $errors['appointment_date'] = 'Please select a date and time';
        } elseif (strtotime($data['appointment_date']) < time()) {
            $errors['appointment_date'] = 'Appointment date must be in the future';
        }
        
        if (empty($data['duration'])) {
            $errors['duration'] = 'Please select appointment duration';
        }
        
        if (!empty($errors)) {
            $this->session->setFlash('error', 'Please correct the errors below');
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', $data);
            $this->redirectBack();
        }
        
        // Create appointment
        $appointmentId = $this->appointmentModel->create([
            'user_id' => $userId,
            'counselor_id' => $data['counselor_id'],
            'appointment_date' => $data['appointment_date'],
            'duration' => $data['duration'],
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled'
        ]);
        
        if ($appointmentId) {
            // Send notification to counselor
            $this->sendAppointmentNotification($appointmentId);
            
            $this->session->setFlash('success', 'Appointment scheduled successfully');
            $this->redirect('/dashboard/appointments');
        } else {
            $this->session->setFlash('error', 'Failed to schedule appointment');
            $this->redirectBack();
        }
    }
    
    /**
     * Send appointment notification
     */
    protected function sendAppointmentNotification($appointmentId) {
        $appointment = $this->appointmentModel->find($appointmentId);
        $counselor = (new \App\Models\User())->find($appointment->counselor_id);
        
        if ($counselor) {
            $mailer = new \App\Core\Mailer();
            $mailer->send(
                $counselor->email,
                'New Appointment Request',
                'emails/appointment-request',
                [
                    'counselor' => $counselor,
                    'appointment' => $appointment,
                    'user' => $this->auth->user()
                ]
            );
        }
    }
    
    /**
     * Cancel appointment
     */
    public function cancelAppointment($id) {
        $appointment = $this->appointmentModel->find($id);
        
        if (!$appointment || $appointment->user_id != $this->auth->id()) {
            $this->session->setFlash('error', 'Appointment not found');
            $this->redirect('/dashboard/appointments');
        }
        
        if ($this->appointmentModel->cancel($id)) {
            $this->session->setFlash('success', 'Appointment cancelled successfully');
        } else {
            $this->session->setFlash('error', 'Failed to cancel appointment');
        }
        
        $this->redirect('/dashboard/appointments');
    }
    
    /**
     * Mood tracking
     */
    public function mood() {
        $user = $this->auth->user();
        
        if ($this->request->isPost()) {
            $this->saveMoodEntry($user->id);
        }
        
        // Get mood entries for the current month
        $month = $this->request->get('month', date('m'));
        $year = $this->request->get('year', date('Y'));
        
        $moodEntries = $this->moodModel->getByMonth($user->id, $month, $year);
        $moodStats = $this->moodModel->getStats($user->id, $month, $year);
        
        $this->view('dashboard/mood', [
            'title' => 'Mood Tracker',
            'moodEntries' => $moodEntries,
            'moodStats' => $moodStats,
            'currentMonth' => $month,
            'currentYear' => $year,
            'moods' => [
                1 => '😢 Very Sad',
                2 => '😞 Sad',
                3 => '😐 Neutral',
                4 => '🙂 Happy',
                5 => '😊 Very Happy'
            ]
        ]);
    }
    
    /**
     * Save mood entry
     */
    protected function saveMoodEntry($userId) {
        $mood = $this->request->post('mood');
        $notes = $this->request->post('notes');
        $date = $this->request->post('date', date('Y-m-d'));
        
        if (empty($mood)) {
            $this->session->setFlash('error', 'Please select a mood');
            $this->redirectBack();
        }
        
        // Check if entry already exists for this date
        $existingEntry = $this->moodModel->getByDate($userId, $date);
        
        if ($existingEntry) {
            // Update existing entry
            $this->moodModel->update($existingEntry->id, [
                'mood_level' => $mood,
                'notes' => $notes
            ]);
            
            $message = 'Mood entry updated successfully';
        } else {
            // Create new entry
            $this->moodModel->create([
                'user_id' => $userId,
                'mood_level' => $mood,
                'notes' => $notes,
                'entry_date' => $date
            ]);
            
            $message = 'Mood entry saved successfully';
        }
        
        $this->session->setFlash('success', $message);
        $this->redirect('/dashboard/mood');
    }
    
    /**
     * Get mood data for calendar
     */
    public function moodCalendarData() {
        $user = $this->auth->user();
        $start = $this->request->get('start');
        $end = $this->request->get('end');
        
        $entries = $this->moodModel->getDateRange($user->id, $start, $end);
        
        $events = [];
        
        foreach ($entries as $entry) {
            $events[] = [
                'title' => $this->getMoodEmoji($entry->mood_level),
                'start' => $entry->entry_date,
                'className' => 'mood-level-' . $entry->mood_level,
                'extendedProps' => [
                    'mood' => $entry->mood_level,
                    'notes' => $entry->notes
                ]
            ];
        }
        
        $this->json($events);
    }
    
    /**
     * Get mood emoji
     */
    protected function getMoodEmoji($level) {
        $emojis = [
            1 => '😢',
            2 => '😞',
            3 => '😐',
            4 => '🙂',
            5 => '😊'
        ];
        
        return $emojis[$level] ?? '❓';
    }
}
