document.addEventListener('DOMContentLoaded', () => {
    const passwordInput = document.querySelector('[data-password-strength]');
    const meterFill = document.querySelector('[data-password-meter] span');

    if (passwordInput && meterFill) {
        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            let score = 0;

            if (value.length >= 8) score += 25;
            if (/[A-Z]/.test(value)) score += 25;
            if (/[a-z]/.test(value)) score += 25;
            if (/[0-9]/.test(value)) score += 25;

            meterFill.style.width = `${score}%`;
            meterFill.style.background = score >= 75 ? '#198754' : score >= 50 ? '#d18a34' : '#dc3545';
        });
    }

    document.querySelectorAll('[data-confirm]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(button.dataset.confirm || 'Continue?')) {
                event.preventDefault();
            }
        });
    });

    const imageInput = document.querySelector('[data-image-preview-input]');
    const imagePreview = document.querySelector('[data-image-preview]');

    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files && imageInput.files[0];

            if (!file) return;

            imagePreview.src = URL.createObjectURL(file);
        });
    }
});

