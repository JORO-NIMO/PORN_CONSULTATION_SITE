<?php
require_once 'config/config.php';

// Video testimonials data - Using YouTube videos about pornography recovery
// TO ADD YOUR YOUTUBE VIDEOS: Replace the youtube_id with actual video IDs from youtube.com
// Example: If video URL is https://www.youtube.com/watch?v=ABC123xyz, use 'ABC123xyz'
$testimonials = [
    [
        'id' => 1,
        'title' => 'Breaking Free from Pornography Addiction',
        'speaker' => 'Recovery Story',
        'duration' => '12:45',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'A powerful testimony about overcoming pornography addiction and finding freedom.',
        'views' => 15200
    ],
    [
        'id' => 2,
        'title' => 'My Journey to Freedom - Porn Addiction Recovery',
        'speaker' => 'Anonymous Testimony',
        'duration' => '18:30',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'Real story of recovery from pornography addiction with practical steps.',
        'views' => 23400
    ],
    [
        'id' => 3,
        'title' => 'How I Quit Porn After 15 Years',
        'speaker' => 'Recovery Journey',
        'duration' => '16:20',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'Honest account of breaking free from long-term pornography addiction.',
        'views' => 18900
    ],
    [
        'id' => 4,
        'title' => 'The Science of Porn Addiction and Recovery',
        'speaker' => 'Expert Talk',
        'duration' => '22:15',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'Understanding the neuroscience behind addiction and the path to recovery.',
        'views' => 31680
    ],
    [
        'id' => 5,
        'title' => 'From Addiction to Freedom - My Story',
        'speaker' => 'Personal Testimony',
        'duration' => '14:50',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'Inspiring story of transformation and healing from pornography addiction.',
        'views' => 21120
    ],
    [
        'id' => 6,
        'title' => 'Rebuilding Life After Porn Addiction',
        'speaker' => 'Recovery Success',
        'duration' => '19:40',
        'youtube_id' => 'dQw4w9WgXcQ', // REPLACE with actual YouTube video ID
        'description' => 'How recovery restored relationships, career, and mental health.',
        'views' => 28900
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recovery Stories - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
    .testimonials-page {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #06b6d4 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .testimonials-page .page-header {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }
    
    .testimonials-page .page-header h1 {
        font-size: 3rem;
        margin-bottom: 1rem;
        text-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
        color: white;
        animation: fadeInUp 0.8s ease-out;
    }
    
    .testimonials-page .page-header .subtitle {
        font-size: 1.25rem;
        color: rgba(255, 255, 255, 0.9);
        animation: fadeInUp 0.8s ease-out 0.2s both;
    }
    
    .videos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    .video-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        animation: scaleIn 0.6s ease-out both;
    }
    
    .video-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 50px rgba(99, 102, 241, 0.3);
    }
    
    .video-thumbnail {
        position: relative;
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #f5f3ff 0%, #eff6ff 100%);
        overflow: hidden;
    }
    
    .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    
    .video-card:hover .video-thumbnail img {
        transform: scale(1.1);
    }
    
    .play-button {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        background: rgba(99, 102, 241, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .video-card:hover .play-button {
        background: var(--primary);
        transform: translate(-50%, -50%) scale(1.1);
    }
    
    .play-button::after {
        content: '';
        width: 0;
        height: 0;
        border-left: 20px solid white;
        border-top: 12px solid transparent;
        border-bottom: 12px solid transparent;
        margin-left: 4px;
    }
    
    .video-duration {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .video-content {
        padding: 1.5rem;
    }
    
    .video-content h3 {
        color: var(--dark);
        margin-bottom: 0.5rem;
        font-size: 1.25rem;
    }
    
    .video-speaker {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
    }
    
    .video-description {
        color: var(--text);
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .video-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-light);
        font-size: 0.875rem;
    }
    
    .video-views {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    /* Video Modal */
    .video-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.95);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease-out;
    }
    
    .video-modal.active {
        display: flex;
    }
    
    .video-modal-content {
        position: relative;
        width: 90%;
        max-width: 1000px;
        background: black;
        border-radius: 12px;
        overflow: hidden;
        animation: scaleIn 0.4s ease-out;
    }
    
    .video-modal video {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .video-modal-close {
        position: absolute;
        top: -40px;
        right: 0;
        background: none;
        border: none;
        color: white;
        font-size: 2rem;
        cursor: pointer;
        padding: 0.5rem;
        transition: transform 0.3s ease;
    }
    
    .video-modal-close:hover {
        transform: scale(1.2);
    }
    
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
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .video-card:nth-child(1) { animation-delay: 0.1s; }
    .video-card:nth-child(2) { animation-delay: 0.2s; }
    .video-card:nth-child(3) { animation-delay: 0.3s; }
    .video-card:nth-child(4) { animation-delay: 0.4s; }
    .video-card:nth-child(5) { animation-delay: 0.5s; }
    .video-card:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="testimonials-page">
        <div class="container">
            <div class="page-header">
                <h1>Recovery Stories</h1>
                <p class="subtitle">Real people sharing their journeys to freedom from pornography addiction</p>
            </div>
            
            <div class="videos-grid">
                <?php foreach ($testimonials as $video): ?>
                <div class="video-card" data-video-id="<?php echo $video['id']; ?>" data-youtube-id="<?php echo $video['youtube_id']; ?>">
                    <div class="video-thumbnail">
                        <img src="https://img.youtube.com/vi/<?php echo $video['youtube_id']; ?>/maxresdefault.jpg" 
                             alt="<?php echo sanitize($video['title']); ?>"
                             onerror="this.src='https://img.youtube.com/vi/<?php echo $video['youtube_id']; ?>/hqdefault.jpg'">
                        <div class="play-button"></div>
                        <div class="video-duration"><?php echo $video['duration']; ?></div>
                    </div>
                    <div class="video-content">
                        <h3><?php echo sanitize($video['title']); ?></h3>
                        <div class="video-speaker">👤 <?php echo sanitize($video['speaker']); ?></div>
                        <p class="video-description"><?php echo sanitize($video['description']); ?></p>
                        <div class="video-meta">
                            <div class="video-views">
                                <span>👁️</span>
                                <span><?php echo number_format($video['views']); ?> views</span>
                            </div>
                            <span><?php echo $video['duration']; ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="background: white; border-radius: 20px; padding: 3rem; text-align: center; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);">
                <h2 style="color: var(--dark); margin-bottom: 1rem;">Want to Share Your Story?</h2>
                <p style="color: var(--text); font-size: 1.125rem; margin-bottom: 2rem;">Your experience could inspire others on their recovery journey. All submissions can be anonymous.</p>
                <a href="messages.php" class="btn btn-primary" style="font-size: 1.125rem; padding: 1rem 2rem;">Contact Us</a>
            </div>
        </div>
    </main>
    
    <!-- Video Modal -->
    <div id="videoModal" class="video-modal">
        <div class="video-modal-content">
            <button class="video-modal-close">&times;</button>
            <div id="youtubePlayer" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden;">
                <iframe id="youtubeIframe" 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"
                        src="" 
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Video modal functionality
    const videoCards = document.querySelectorAll('.video-card');
    const videoModal = document.getElementById('videoModal');
    const modalVideo = document.getElementById('modalVideo');
    const closeBtn = document.querySelector('.video-modal-close');
    
    const youtubeIframe = document.getElementById('youtubeIframe');
    
    videoCards.forEach(card => {
        card.addEventListener('click', function() {
            const youtubeId = this.dataset.youtubeId;
            youtubeIframe.src = `https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0`;
            videoModal.classList.add('active');
        });
    });
    
    closeBtn.addEventListener('click', closeModal);
    videoModal.addEventListener('click', function(e) {
        if (e.target === videoModal) {
            closeModal();
        }
    });
    
    function closeModal() {
        videoModal.classList.remove('active');
        youtubeIframe.src = '';
    }
    
    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && videoModal.classList.contains('active')) {
            closeModal();
        }
    });
    </script>
</body>
</html>
