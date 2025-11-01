<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Mailer;

class AuthController extends Controller {
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * Show login form
     */
    public function showLoginForm() {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', [
            'title' => 'Login',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Handle login
     */
    public function login() {
        $this->validateCsrfToken();
        
        $email = $this->request->post('email');
        $password = $this->request->post('password');
        $remember = $this->request->post('remember') === 'on';
        
        // Validate input
        $errors = [];
        
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (!empty($errors)) {
            $this->session->setFlash('error', 'Please correct the errors below');
            $this->session->setFlash('errors', $errors);
            $this->redirectBack();
        }
        
        // Attempt to authenticate user
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($password, $user->password_hash)) {
            $this->session->setFlash('error', 'Invalid email or password');
            $this->redirectBack();
        }
        
        // Check if account is active
        if (!$user->is_active) {
            $this->session->setFlash('error', 'Your account has been deactivated. Please contact support.');
            $this->redirectBack();
        }
        
        // Log the user in
        $this->auth->login($user, $remember);
        
        // Update last login
        $this->userModel->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);
        
        // Redirect to intended URL or dashboard
        $redirectTo = $this->session->get('redirect_after_login', '/dashboard');
        $this->session->delete('redirect_after_login');
        
        $this->redirect($redirectTo);
    }
    
    /**
     * Show registration form
     */
    public function showRegistrationForm() {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/register', [
            'title' => 'Create Account',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Handle registration
     */
    public function register() {
        $this->validateCsrfToken();
        
        $data = [
            'first_name' => $this->request->post('first_name'),
            'last_name' => $this->request->post('last_name'),
            'email' => $this->request->post('email'),
            'username' => $this->request->post('username'),
            'password' => $this->request->post('password'),
            'password_confirmation' => $this->request->post('password_confirmation'),
            'terms' => $this->request->post('terms')
        ];
        
        // Validate input
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            $this->session->setFlash('error', 'Please correct the errors below');
            $this->session->setFlash('errors', $errors);
            $this->session->setFlash('old', $data);
            $this->redirectBack();
        }
        
        // Create user
        $userId = $this->userModel->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password_hash' => $this->userModel->hashPassword($data['password']),
            'role' => 'user',
            'is_verified' => 0, // Email verification required
            'verification_token' => bin2hex(random_bytes(32))
        ]);
        
        if (!$userId) {
            $this->session->setFlash('error', 'Failed to create account. Please try again.');
            $this->redirectBack();
        }
        
        // Send verification email
        $this->sendVerificationEmail($userId);
        
        $this->session->setFlash('success', 'Registration successful! Please check your email to verify your account.');
        $this->redirect('/login');
    }
    
    /**
     * Validate registration data
     */
    protected function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } elseif ($this->userModel->findByEmail($data['email'])) {
            $errors['email'] = 'Email is already registered';
        }
        
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores';
        } elseif ($this->userModel->findByUsername($data['username'])) {
            $errors['username'] = 'Username is already taken';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        if (empty($data['terms'])) {
            $errors['terms'] = 'You must accept the terms and conditions';
        }
        
        return $errors;
    }
    
    /**
     * Send verification email
     */
    protected function sendVerificationEmail($userId) {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $verificationUrl = url("/verify-email?token={$user->verification_token}");
        
        $mailer = new Mailer();
        return $mailer->send(
            $user->email,
            'Verify Your Email Address',
            'emails/verify-email',
            [
                'name' => $user->first_name,
                'verificationUrl' => $verificationUrl
            ]
        );
    }
    
    /**
     * Verify email
     */
    public function verifyEmail() {
        $token = $this->request->get('token');
        
        if (empty($token)) {
            $this->session->setFlash('error', 'Invalid verification link');
            $this->redirect('/login');
        }
        
        $user = $this->userModel->findByVerificationToken($token);
        
        if (!$user) {
            $this->session->setFlash('error', 'Invalid or expired verification link');
            $this->redirect('/login');
        }
        
        // Mark email as verified
        $this->userModel->update($user->id, [
            'is_verified' => 1,
            'verification_token' => null,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->session->setFlash('success', 'Your email has been verified. You can now log in.');
        $this->redirect('/login');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm() {
        if ($this->auth->check()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/forgot-password', [
            'title' => 'Reset Password',
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Handle forgot password
     */
    public function forgotPassword() {
        $this->validateCsrfToken();
        
        $email = $this->request->post('email');
        
        if (empty($email)) {
            $this->session->setFlash('error', 'Please enter your email address');
            $this->redirectBack();
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Generate password reset token
            $token = $this->userModel->createPasswordResetToken($user->email);
            
            if ($token) {
                // Send password reset email
                $resetUrl = url("/reset-password?token={$token}");
                
                $mailer = new Mailer();
                $mailer->send(
                    $user->email,
                    'Reset Your Password',
                    'emails/reset-password',
                    [
                        'name' => $user->first_name,
                        'resetUrl' => $resetUrl
                    ]
                );
            }
        }
        
        // Always show success message to prevent email enumeration
        $this->session->setFlash('success', 'If your email exists in our system, you will receive a password reset link.');
        $this->redirect('/login');
    }
    
    /**
     * Show reset password form
     */
    public function showResetPasswordForm() {
        $token = $this->request->get('token');
        
        if (empty($token)) {
            $this->session->setFlash('error', 'Invalid password reset link');
            $this->redirect('/forgot-password');
        }
        
        $resetToken = $this->userModel->validatePasswordResetToken($token);
        
        if (!$resetToken) {
            $this->session->setFlash('error', 'Invalid or expired password reset link');
            $this->redirect('/forgot-password');
        }
        
        $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    /**
     * Handle reset password
     */
    public function resetPassword() {
        $this->validateCsrfToken();
        
        $token = $this->request->post('token');
        $password = $this->request->post('password');
        $passwordConfirmation = $this->request->post('password_confirmation');
        
        // Validate token
        $resetToken = $this->userModel->validatePasswordResetToken($token);
        
        if (!$resetToken) {
            $this->session->setFlash('error', 'Invalid or expired password reset link');
            $this->redirect('/forgot-password');
        }
        
        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $this->session->setFlash('error', 'Please correct the errors below');
            $this->session->setFlash('errors', $errors);
            $this->redirectBack();
        }
        
        // Find user by email from token
        $user = $this->userModel->findByEmail($resetToken->email);
        
        if (!$user) {
            $this->session->setFlash('error', 'User not found');
            $this->redirect('/forgot-password');
        }
        
        // Update password
        $this->userModel->update($user->id, [
            'password_hash' => $this->userModel->hashPassword($password)
        ]);
        
        // Mark token as used
        $this->userModel->markTokenAsUsed($resetToken->id);
        
        $this->session->setFlash('success', 'Your password has been reset. You can now log in with your new password.');
        $this->redirect('/login');
    }
    
    /**
     * Logout
     */
    public function logout() {
        $this->auth->logout();
        $this->redirect('/');
    }
}
