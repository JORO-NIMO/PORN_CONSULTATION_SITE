// Authentication page functionality
(function() {
    const forms = document.querySelectorAll('.auth-form');
    
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            // Allow normal form submission for now
            // Can be enhanced with AJAX later
        });
    });
    
    // Password strength indicator for registration
    const passwordInput = document.getElementById('password');
    if (passwordInput && window.location.pathname.includes('register')) {
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const strength = calculatePasswordStrength(password);
            showPasswordStrength(strength);
        });
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        return strength;
    }
    
    function showPasswordStrength(strength) {
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#ef4444', '#f59e0b', '#eab308', '#10b981', '#059669'];
        
        let indicator = document.getElementById('password-strength');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'password-strength';
            indicator.style.marginTop = '0.5rem';
            indicator.style.fontSize = '0.875rem';
            passwordInput.parentElement.appendChild(indicator);
        }
        
        indicator.textContent = `Password Strength: ${labels[strength]}`;
        indicator.style.color = colors[strength];
    }
})();
