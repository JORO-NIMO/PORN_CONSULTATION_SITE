<?php 
require_once 'config/config.php';

$db = Database::getInstance();

// Get dynamic statistics
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
$totalConsultations = $db->fetchOne("SELECT COUNT(*) as count FROM consultations WHERE status = 'completed'")['count'] ?? 0;
$totalResources = $db->fetchOne("SELECT COUNT(*) as count FROM educational_content")['count'] ?? 0;
$successRate = $totalConsultations > 0 ? round(($totalConsultations / max($totalUsers, 1)) * 100) : 100;
// Prepare CardSwap pairs (two items per section)
require_once __DIR__ . '/includes/api_helpers.php';
$pc_include = __DIR__ . '/includes/profile_card.php';
if (file_exists($pc_include)) { require_once $pc_include; }
$affirmations = getAffirmations(2);
$zen = getZenQuotes(2);
$quotes_data = getQuotableQuotes(2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> — <?php echo SITE_TAGLINE; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.95) 0%, rgba(139, 92, 246, 0.95) 50%, rgba(6, 182, 212, 0.95) 100%),
                        url('bg.png') center/cover;
            color: white;
            padding: 3.5rem 2rem;
            text-align: center;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            animation: fadeIn 1s ease-out;
        }
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }
        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .hero-buttons .btn {
            padding: 1rem 2rem;
            font-size: 1.125rem;
        }
        .features {
            background: white;
            padding: 4rem 2rem;
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
    <?php include 'includes/header.php'; ?>
    <section class="hero">
        <div class="container">
            <h1>🌟 Nurturing Mental Wellness for Youth and Parents</h1>
            <p><?php echo SITE_TAGLINE; ?>. Access trusted guidance, caring professionals, and practical tools.</p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn btn-primary" style="background: white; color: var(--primary);">Join <?php echo SITE_NAME; ?></a>
                <a href="exercises.php" class="btn btn-secondary" style="background: rgba(255,255,255,0.2); color: white;">Try Breathing Exercises</a>
            </div>
        </div>
    </section>
    <div class="container" style="display:flex; justify-content:center; margin-top:-2rem; margin-bottom:2rem;">
        <?php if (function_exists('renderProfileCard')) renderProfileCard([
            'contactText' => 'Contact Me',
            'enableTilt' => true,
            'enableMobileTilt' => false,
            'showUserInfo' => true,
        ]); ?>
    </div>
    
    <!-- Motivational Cards (CardSwap) -->
    <section class="daily-motivation">
        <div class="container">
            <h2 style="text-align:center; margin-bottom: 2rem;">Daily Motivation</h2>
            <div class="motivation-container">
                <div class="lightning-bg"></div>
                <div class="motivation-image-container">
                    <img id="motivation-image" src="" alt="Daily Motivation" style="width:100%; height:auto; border-radius: 8px; object-fit: cover;">
                </div>
                <div class="cardswap" data-card-distance="60" data-vertical-distance="70" data-delay="8000" data-pause-on-hover="false">
                    <div class="cs-card">
                        <h3>Affirmations</h3>
                        <?php foreach ($affirmations as $a): ?>
                          <p>“<?php echo htmlspecialchars($a); ?>”</p>
                        <?php endforeach; ?>
                    </div>
                    <div class="cs-card">
                        <h3>ZenQuotes</h3>
                        <?php foreach ($zen as $z): ?>
                          <p><?php echo htmlspecialchars($z['q'] ?? ''); ?> — <em><?php echo htmlspecialchars($z['a'] ?? ''); ?></em></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="cs-card">
                        <h3>Quotable</h3>
                        <?php foreach ($quotes_data as $q): ?>
                          <p><?php echo htmlspecialchars($q['content'] ?? ''); ?> — <em><?php echo htmlspecialchars($q['author'] ?? ''); ?></em></p>
                        <?php endforeach; ?>
                    </div>
                </div>
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
    <script src="assets/js/main.js"></script>
    <style>
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
