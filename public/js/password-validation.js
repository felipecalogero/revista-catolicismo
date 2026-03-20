/**
 * Shared Password Validation Logic
 */

const PasswordValidation = {
    // Default configuration
    defaults: {
        passwordId: 'password',
        confirmationId: 'password_confirmation',
        submitButtonId: 'submit-button',
        matchMessageId: 'password-match-message',
        requirements: {
            length: 'req-length',
            uppercase: 'req-uppercase',
            lowercase: 'req-lowercase',
            number: 'req-number',
            symbol: 'req-symbol'
        },
        // Optional callback for custom validity logic
        onValidityChange: null
    },

    /**
     * Initialize validation on a page
     */
    init(options = {}) {
        const config = { ...this.defaults, ...options };
        if (options.requirements) {
            config.requirements = { ...this.defaults.requirements, ...options.requirements };
        }

        const passwordInput = document.getElementById(config.passwordId);
        const confirmationInput = document.getElementById(config.confirmationId);

        if (!passwordInput) return;

        // Attach event listeners
        passwordInput.addEventListener('input', () => {
            this.validateStrength(passwordInput.value, config);
            this.validateMatch(config);
            this.updateSubmitButton(config);
        });

        if (confirmationInput) {
            confirmationInput.addEventListener('input', () => {
                this.validateMatch(config);
                this.updateSubmitButton(config);
            });
        }

        // Run initial validation
        if (passwordInput.value) {
            this.validateStrength(passwordInput.value, config);
            this.validateMatch(config);
        }
        this.updateSubmitButton(config);
    },

    /**
     * Validate password strength and update UI
     */
    validateStrength(password, config) {
        const strength = this.getStrength(password);

        Object.keys(config.requirements).forEach(key => {
            this.updateRequirementUI(config.requirements[key], strength[key]);
        });
    },

    /**
     * Get strength metrics for a password
     */
    getStrength(password) {
        return {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[!@#$%^&*()_+\-=\[\]{}|;:,.<>?]/.test(password)
        };
    },

    /**
     * Update individual requirement UI
     */
    updateRequirementUI(id, isValid) {
        const element = document.getElementById(id);
        if (!element) return;

        const icon = element.querySelector('.requirement-icon');
        const text = element.querySelector('span:last-child');

        if (isValid) {
            if (icon) {
                icon.textContent = '✓';
                icon.className = 'requirement-icon mr-2 text-green-600 font-bold';
            }
            if (text) text.className = 'text-green-700';
        } else {
            if (icon) {
                icon.textContent = '✗';
                icon.className = 'requirement-icon mr-2 text-red-600';
            }
            if (text) text.className = 'text-gray-700';
        }
    },

    /**
     * Validate if passwords match and update UI
     */
    validateMatch(config) {
        const passwordInput = document.getElementById(config.passwordId);
        const confirmationInput = document.getElementById(config.confirmationId);
        const messageDiv = document.getElementById(config.matchMessageId);

        if (!passwordInput || !confirmationInput) return;

        const password = passwordInput.value;
        const passwordConfirmation = confirmationInput.value;

        if (passwordConfirmation.length === 0) {
            if (messageDiv) messageDiv.classList.add('hidden');
            confirmationInput.classList.remove('border-green-500', 'border-red-500');
            confirmationInput.classList.add('border-gray-300');
            return;
        }

        if (messageDiv) messageDiv.classList.remove('hidden');

        if (password === passwordConfirmation && password.length > 0) {
            if (messageDiv) {
                messageDiv.textContent = '✓ As senhas coincidem';
                messageDiv.className = 'mt-2 text-sm text-green-600 font-medium';
            }
            confirmationInput.classList.remove('border-red-500', 'border-gray-300');
            confirmationInput.classList.add('border-green-500');
        } else {
            if (messageDiv) {
                messageDiv.textContent = '✗ As senhas não coincidem';
                messageDiv.className = 'mt-2 text-sm text-red-600 font-medium';
            }
            confirmationInput.classList.remove('border-green-500', 'border-gray-300');
            confirmationInput.classList.add('border-red-500');
        }
    },

    /**
     * Check overall validity and update submit button state
     */
    updateSubmitButton(config) {
        const passwordInput = document.getElementById(config.passwordId);
        const confirmationInput = document.getElementById(config.confirmationId);
        const submitButton = document.getElementById(config.submitButtonId);

        if (!passwordInput || !submitButton) return;

        const password = passwordInput.value;
        const strength = this.getStrength(password);
        const isStrengthValid = Object.values(strength).every(v => v);

        let isMatchValid = true;
        if (confirmationInput) {
            isMatchValid = (password.length > 0 && password === confirmationInput.value);
        }

        let isValid = isStrengthValid && isMatchValid;

        // Allow custom override (e.g., for edit pages where empty password is allowed)
        if (config.onValidityChange) {
            isValid = config.onValidityChange(isValid, {
                password,
                passwordConfirmation: confirmationInput ? confirmationInput.value : '',
                isStrengthValid,
                isMatchValid
            });
        }

        submitButton.disabled = !isValid;
    }
};

// Global legacy access if needed
window.validatePassword = (val) => PasswordValidation.validateStrength(val, PasswordValidation.defaults);
window.validatePasswordMatch = () => PasswordValidation.validateMatch(PasswordValidation.defaults);
window.getPasswordStrength = (val) => PasswordValidation.getStrength(val);
