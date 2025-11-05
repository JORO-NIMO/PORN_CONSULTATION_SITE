</div></div>
<footer style="background: white; padding: 2rem 0; margin-top: 2rem; box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);">
    <div class="container">
        <!-- Partners Section -->
        <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
            <h3 style="text-align: center; color: var(--dark); margin-bottom: 1rem; font-size: 1.125rem;">Our Trusted Partners</h3>
            <div class="partners-slider">
                <div class="partners-track">
                    <?php
                    // Dynamically load partner images
                    for ($i = 1; $i <= 6; $i++) {
                        $partnerImg = '';
                        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
                        
                        // Check for different image formats
                        if (file_exists($basePath . "assets/images/partners/partner{$i}.png")) {
                            $partnerImg = "assets/images/partners/partner{$i}.png";
                        } elseif (file_exists($basePath . "assets/images/partners/partner{$i}.jpg")) {
                            $partnerImg = "assets/images/partners/partner{$i}.jpg";
                        } elseif (file_exists($basePath . "assets/images/partners/partner{$i}.jpeg")) {
                            $partnerImg = "assets/images/partners/partner{$i}.jpeg";
                        }
                        
                        // Fallback SVG placeholder
                        $fallback = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='100'%3E%3Crect fill='%23f5f3ff' width='200' height='100'/%3E%3Ctext x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='16' fill='%236366f1'%3EPartner {$i}%3C/text%3E%3C/svg%3E";
                        
                        echo "<div class='partner-item'>";
                        if ($partnerImg) {
                            echo "<img src='{$partnerImg}' alt='Partner {$i}' loading='lazy'>";
                        } else {
                            echo "<img src='{$fallback}' alt='Partner {$i}'>";
                        }
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 0.5rem;">
            <div>
                <h3 style="color: var(--primary); margin-bottom: 1rem;"><?php echo SITE_NAME; ?></h3>
                <p style="color: var(--text-light);">Supporting individuals on their journey to freedom from pornography addiction through professional help, education, and community.</p>
            </div>
            <div>
                <h4 style="margin-bottom: 1rem;">Quick Links</h4>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 0.5rem;"><a href="education.php" style="color: var(--text-light); text-decoration: none;">Educational Resources</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="psychiatrists.php" style="color: var(--text-light); text-decoration: none;">Find a Psychiatrist</a></li>
                    <li style="margin-bottom: 0.5rem;"><a href="messages.php" style="color: var(--text-light); text-decoration: none;">Anonymous Support</a></li>
                </ul>
            </div>
            <div>
                <h4 style="margin-bottom: 1rem;">Contact Us</h4>
                <p style="color: var(--text-light); margin-bottom: 0.5rem;">Get in touch with us:</p>
                <p style="color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;">
                    📱 WhatsApp: <a href="https://wa.me/256726128513" target="_blank" style="color: var(--primary); text-decoration: none;">+256 726 128513</a>
                </p>
                <p style="color: var(--primary); font-weight: 600;">
                    📧 Email: <a href="mailto:joronimoamanya@gmail.com" style="color: var(--primary); text-decoration: none;">joronimoamanya@gmail.com</a>
                </p>
            </div>
        </div>
        <div style="text-align: center; padding-top: 0.5rem; border-top: 1px solid var(--border); color: var(--text-light); font-size: 0.875rem;">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. | <a href="privacy-policy.php" style="color: var(--primary); text-decoration: none; transition: all 0.3s ease;">Privacy Policy</a> | <a href="terms-of-service.php" style="color: var(--primary); text-decoration: none; transition: all 0.3s ease;">Terms of Service</a></p>
        </div>
    </div>
</footer>

<style>
/* Partners Slider Styles - Global */
.partners-slider {
    position: relative;
    overflow: hidden;
    padding: 0.5rem 0;
}

.partners-track {
    display: flex;
    gap: 2rem;
    animation: slidePartners 40s linear infinite;
}

.partner-item {
    flex-shrink: 0;
    width: 120px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
    padding: 0.5rem;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.08);
    transition: all 0.3s ease;
}

.partner-item:hover {
    transform: translateY(-3px) scale(1.03);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.15);
}

.partner-item img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.partner-item:hover img {
    transform: scale(1.05);
}

@keyframes slidePartners {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(calc(-120px * 12 - 2rem * 12));
    }
}

.partners-track:hover {
    animation-play-state: paused;
}
</style>

<script>
// Duplicate partners track for seamless loop
if (typeof partnersInitialized === 'undefined') {
    window.partnersInitialized = true;
    document.addEventListener('DOMContentLoaded', function() {
        const partnersTrack = document.querySelector('.partners-track');
        if (partnersTrack && partnersTrack.children.length > 0) {
            const clone = partnersTrack.cloneNode(true);
            partnersTrack.parentElement.appendChild(clone);
        }
    });
}
</script>

<!-- CardSwap animation -->
<script src="/assets/js/cardswap.js"></script>
<!-- ProfileCard interaction -->
<script src="/assets/js/profilecard.js"></script>
