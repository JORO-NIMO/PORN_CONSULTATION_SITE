<?php 
require_once 'config/config.php';

// Set page title
$pageTitle = SITE_NAME . ' — AI-Powered Mental Wellness Assistant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-color: #4a6fa5;
            --secondary-color: #6c757d;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .chat-container {
            max-width: 1000px;
            margin: 2rem auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: white;
            height: 80vh;
            display: flex;
            flex-direction: column;
        }
        
        .chat-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3a5a8c 100%);
            color: white;
            padding: 1.2rem;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8f9fa;
        }
        
        .chat-message {
            margin-bottom: 1rem;
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            line-height: 1.5;
            position: relative;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .chat-message.user {
            background-color: var(--primary-color);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 0.25rem;
        }
        
        .chat-message.assistant {
            background-color: #e9ecef;
            color: var(--dark-color);
            margin-right: auto;
            border-bottom-left-radius: 0.25rem;
        }
        
        .chat-input-container {
            display: flex;
            padding: 1rem;
            background: white;
            border-top: 1px solid #e9ecef;
            gap: 0.5rem;
        }
        
        #chat-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            border-radius: 2rem;
            font-size: 1rem;
            resize: none;
            outline: none;
            transition: border-color 0.2s;
        }
        
        #chat-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 111, 165, 0.25);
        }
        
        #send-message {
            background: linear-gradient(135deg, var(--primary-color) 0%, #3a5a8c 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        #send-message:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        #send-message:disabled {
            background: #9bb3d8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .typing-indicator {
            display: none;
            align-items: center;
            padding: 0.5rem 1rem;
            margin-bottom: 1rem;
            background: #e9ecef;
            border-radius: 1rem;
            width: fit-content;
            max-width: 80%;
        }
        
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #6c757d;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chat-container {
                margin: 0;
                height: 100vh;
                border-radius: 0;
            }
            
            .chat-message {
                max-width: 90%;
            }
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            text-align: center;
            padding: 2rem;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
            transition: var(--transition);
            border: 1px solid rgba(99, 102, 241, 0.1);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .stats-section {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .stats-section * {
            color: white;
        }
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(6, 182, 212, 0.2) 0%, transparent 50%);
            pointer-events: none;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 2rem auto 0;
        }
        .stat-item h3 {
            font-size: 3rem;
            background: linear-gradient(135deg, #a78bfa 0%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .stat-item p {
            color: white;
        }
        .cta-section {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 -10px 40px rgba(99, 102, 241, 0.2);
        }
        .cta-section * {
            color: white;
        }
        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: white;
        }
        .cta-section p {
            color: white;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <i class="fas fa-comments me-2"></i>
            Mental Wellness Assistant
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <div class="typing-indicator" id="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <input type="text" id="chat-input" placeholder="Type your message here..." autocomplete="off">
            <button id="send-message" aria-label="Send message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <!-- Add Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add our chat script -->
    <script src="assets/js/wellness-chat.js"></script>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn btn-primary" style="background: white; color: var(--primary);">Join <?php echo SITE_NAME; ?></a>
                <a href="exercises.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white;">Try Breathing Exercises</a>
            </div>
        </div>
    </section>
    
    <section class="features">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem; color: var(--dark);">How We Help You</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">👨‍⚕️</div>
                    <h3>Professional Psychiatrists</h3>
                    <p>Connect with experienced specialists who understand addiction and recovery</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Anonymous Messaging</h3>
                    <p>Reach out for support in a safe, confidential environment</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📚</div>
                    <h3>Educational Resources</h3>
                    <p>Learn about the science, effects, and recovery process</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📹</div>
                    <h3>Video Consultations</h3>
                    <p>Secure, anonymous video calls with psychiatrists</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Progress Tracking</h3>
                    <p>Customizable forms to monitor your recovery journey</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Community Support</h3>
                    <p>Connect with others on the same path to freedom</p>
                </div>
            </div>
        </div>
    </section>
    
    <section class="stats-section">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">The Reality of Pornography Addiction</h2>
            <p style="font-size: 1.25rem; opacity: 0.9; margin-bottom: 2rem;">Understanding the problem is the first step to recovery</p>
            <div class="stats-container">
                <div class="stat-item">
                    <h3><?php echo number_format($totalUsers); ?>+</h3>
                    <p>people on their recovery journey</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo number_format($totalConsultations); ?>+</h3>
                    <p>successful consultations completed</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo number_format($totalResources); ?>+</h3>
                    <p>educational resources available</p>
                </div>
                <div class="stat-item">
                    <h3><?php echo $successRate; ?>%</h3>
                    <p>recovery success rate with support</p>
                </div>
            </div>
        </div>
    </section>
    
    
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Start Your Recovery Journey?</h2>
            <p style="font-size: 1.25rem; margin-bottom: 2rem;">Join thousands who have found freedom. Take the first step today.</p>
            <a href="auth/register.php" class="btn" style="background: white; color: var(--primary); padding: 1rem 3rem; font-size: 1.25rem;">Create Free Account</a>
        </div>
    </section>
    <?php include 'includes/footer.php'; ?>
    <footer class="chat-footer text-center py-3">
        <div class="social-links">
            <a href="https://www.youtube.com/@MFPug" target="_blank" rel="noopener noreferrer" class="social-link" title="YouTube">
                <i class="fab fa-youtube"></i>
            </a>
            <a href="https://twitter.com/ishabarundo" target="_blank" rel="noopener noreferrer" class="social-link" title="X (Twitter)">
                <i class="fab fa-x-twitter"></i>
            </a>
            <a href="https://www.tiktok.com/@ishabarundo" target="_blank" rel="noopener noreferrer" class="social-link" title="TikTok">
                <i class="fab fa-tiktok"></i>
            </a>
            <a href="https://www.instagram.com/ishabarundo" target="_blank" rel="noopener noreferrer" class="social-link" title="Instagram">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
        <div class="mt-2 text-muted small">© <?php echo date('Y'); ?> Mental Freedom Path. All rights reserved.</div>
    </footer>

    <script src="assets/js/main.js"></script>
    <style>
    /* Social Media Links */
    .chat-footer {
        background: var(--light-color);
        border-top: 1px solid #dee2e6;
        margin-top: 1rem;
    }
    
    .social-links {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .social-link {
        color: var(--secondary-color);
        font-size: 1.5rem;
        transition: color 0.3s ease;
    }
    
    .social-link:hover {
        color: var(--primary-color);
        text-decoration: none;
    }
    
    /* Enhanced Home Page Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-10px);
        }
    }
    
    /* Apply animations to elements */
    .hero h1 {
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }
    
    .hero p {
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }
    
    .hero-buttons {
        animation: fadeInUp 0.8s ease-out 0.6s both;
    }
    
    .feature-card {
        animation: scaleIn 0.6s ease-out both;
    }
    
    .feature-card:nth-child(1) { animation-delay: 0.1s; }
    .feature-card:nth-child(2) { animation-delay: 0.2s; }
    .feature-card:nth-child(3) { animation-delay: 0.3s; }
    .feature-card:nth-child(4) { animation-delay: 0.4s; }
    .feature-card:nth-child(5) { animation-delay: 0.5s; }
    .feature-card:nth-child(6) { animation-delay: 0.6s; }
    
    .feature-icon {
        animation: float 3s ease-in-out infinite;
    }
    
    .stat-item {
        animation: fadeInUp 0.8s ease-out both;
    }
    
    .stat-item:nth-child(1) { animation-delay: 0.2s; }
    .stat-item:nth-child(2) { animation-delay: 0.4s; }
    .stat-item:nth-child(3) { animation-delay: 0.6s; }
    .stat-item:nth-child(4) { animation-delay: 0.8s; }
    
    .cta-section h2 {
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }
    
    .cta-section p {
        animation: fadeInUp 0.8s ease-out 0.4s both;
    }
    
    .cta-section .btn {
        animation: scaleIn 0.6s ease-out 0.6s both;
    }
    </style>
    
    <script>
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements for scroll animations
    document.querySelectorAll('.features, .stats-section, .cta-section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
        observer.observe(el);
    });
    </script>
</body>
</html>
