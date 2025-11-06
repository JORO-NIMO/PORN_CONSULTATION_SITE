// Main JavaScript functionality
(function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Confirm dangerous actions
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation helper
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    showFieldError(field, 'This field is required');
                } else {
                    field.classList.remove('error');
                    clearFieldError(field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill in all required fields', 'error');
            }
        });
    });
    
    function showFieldError(field, message) {
        let errorEl = field.parentElement.querySelector('.field-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'field-error';
            errorEl.style.color = 'var(--error)';
            errorEl.style.fontSize = '0.875rem';
            errorEl.style.marginTop = '0.25rem';
            field.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
    }
    
    function clearFieldError(field) {
        const errorEl = field.parentElement.querySelector('.field-error');
        if (errorEl) {
            errorEl.remove();
        }
    }
    
    function showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.top = '2rem';
        alert.style.right = '2rem';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
    
    // Loading state helper
    window.setLoading = function(element, isLoading) {
        if (isLoading) {
            element.dataset.originalText = element.textContent;
            element.disabled = true;
            element.innerHTML = '<span class="spinner"></span> Loading...';
        } else {
            element.disabled = false;
            element.textContent = element.dataset.originalText;
        }
    };
    
    // Expose showAlert globally
    window.showAlert = showAlert;

    // Daily Motivation Image Rotator
    const motivationImages = [
        'https://nappy.co/wp-content/uploads/2022/07/nappy-1658422631-1.jpg',
        'https://nappy.co/wp-content/uploads/2022/07/nappy-1658422631-2.jpg',
        'https://nappy.co/wp-content/uploads/2022/07/nappy-1658422631-3.jpg',
        'https://nappy.co/wp-content/uploads/2022/07/nappy-1658422631-4.jpg',
        'https://nappy.co/wp-content/uploads/2022/07/nappy-1658422631-6.jpg' // Replaced problematic image
    ];

    let currentImageIndex = 0;
    const motivationImageElement = document.getElementById('motivation-image');

    function changeMotivationImage() {
        if (motivationImageElement) {
            currentImageIndex = (currentImageIndex + 1) % motivationImages.length;
            motivationImageElement.src = motivationImages[currentImageIndex];
        }
    }

    // Initial image load
    if (motivationImageElement) {
        motivationImageElement.src = motivationImages[currentImageIndex];
    }

    // Change image every 5 minutes (300000 milliseconds)
    setInterval(changeMotivationImage, 300000);
})();
