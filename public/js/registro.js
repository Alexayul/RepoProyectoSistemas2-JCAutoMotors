function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.style.animation = 'slideOut 0.5s forwards';
        setTimeout(() => alert.remove(), 500);

        setTimeout(() => {
            const container = document.getElementById('alertContainer');
            if (container && container.children.length === 0) {
                container.remove();
            }
        }, 600);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-tailwind');
    let delay = 0;
    
    alerts.forEach((alert) => {
        setTimeout(() => {
            if (alert && alert.id) {
                closeAlert(alert.id);
            }
        }, 5000 + delay);
        delay += 500;
    });
    
    initializeFormValidation();
});

function togglePasswordVisibility(fieldId, iconId) {
    const passwordField = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(iconId);

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.innerHTML = '<i class="bi bi-eye-slash"></i>';
    } else {
        passwordField.type = "password";
        eyeIcon.innerHTML = '<i class="bi bi-eye"></i>';
    }
}

document.getElementById('eye-icon-password').addEventListener('click', function() {
    togglePasswordVisibility('password', 'eye-icon-password');
});

document.getElementById('eye-icon-confirm').addEventListener('click', function() {
    togglePasswordVisibility('confirm_password', 'eye-icon-confirm');
});

function showError(fieldId, message) {
    const container = document.getElementById(fieldId + '-container');
    const errorElement = document.getElementById(fieldId + '-error');
    
    if (container && errorElement) {
        container.classList.add('error');
        errorElement.textContent = message;
    }
}

function clearError(fieldId) {
    const container = document.getElementById(fieldId + '-container');
    const errorElement = document.getElementById(fieldId + '-error');
    
    if (container && errorElement) {
        container.classList.remove('error');
        errorElement.textContent = '';
    }
}

function showAlertError(message) {
    const container = document.getElementById('alertContainer') || createAlertContainer();
    const alertId = 'errorAlert-' + Date.now();
    
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = 'alert-tailwind';
    alertElement.innerHTML = `
        <div class="alert-tailwind-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="alert-tailwind-content">
            <p class="alert-tailwind-title">Error</p>
            <p class="alert-tailwind-message">${message}</p>
        </div>
        <button type="button" class="alert-tailwind-close" onclick="closeAlert('${alertId}')">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    container.appendChild(alertElement);
    
    setTimeout(() => closeAlert(alertId), 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alertContainer';
    container.className = 'alert-container';
    document.body.appendChild(container);
    return container;
}

function checkPasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password)
    };
    
    updateRequirementUI('req-length', requirements.length);
    updateRequirementUI('req-uppercase', requirements.uppercase);
    updateRequirementUI('req-lowercase', requirements.lowercase);
    updateRequirementUI('req-number', requirements.number);
    updateRequirementUI('req-special', requirements.special);
    
    const strength = Object.values(requirements).filter(Boolean).length;
    
    const meter = document.getElementById('password-strength-meter');
    meter.style.width = `${strength * 20}%`;
    
    if (strength <= 2) {
        meter.style.backgroundColor = '#EF4444'; // Rojo - débil
    } else if (strength <= 3) {
        meter.style.backgroundColor = '#F59E0B'; // Ámbar - medio
    } else if (strength <= 4) {
        meter.style.backgroundColor = '#10B981'; // Verde - fuerte
    } else {
        meter.style.backgroundColor = '#059669'; // Verde oscuro - muy fuerte
    }
    
    return requirements;
}

function updateRequirementUI(reqId, isValid) {
    const reqElement = document.getElementById(reqId);
    if (reqElement) {
        if (isValid) {
            reqElement.classList.add('valid');
            reqElement.classList.remove('invalid');
            reqElement.querySelector('i').className = 'bi bi-check-circle';
        } else {
            reqElement.classList.add('invalid');
            reqElement.classList.remove('valid');
            reqElement.querySelector('i').className = 'bi bi-x-circle';
        }
    }
}

function initializeFormValidation() {
    const form = document.getElementById('register-form');
    
    const nombreInput = document.getElementById('nombre');
    nombreInput.addEventListener('blur', function() {
        if (!this.value.trim()) {
            showError('nombre', 'El nombre completo es obligatorio');
        } else {
            clearError('nombre');
        }
    });
    
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        if (!this.value.trim()) {
            showError('email', 'El correo electrónico es obligatorio');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
            showError('email', 'El formato del correo electrónico no es válido');
        } else {
            clearError('email');
        }
    });
    
    const usuarioInput = document.getElementById('usuario');
    usuarioInput.addEventListener('blur', function() {
        if (!this.value.trim()) {
            showError('usuario', 'El nombre de usuario es obligatorio');
        } else if (this.value.length < 4) {
            showError('usuario', 'El nombre de usuario debe tener al menos 4 caracteres');
        } else {
            clearError('usuario');
        }
    });
    
    const passwordInput = document.getElementById('password');
    passwordInput.addEventListener('input', function() {
        const requirements = checkPasswordRequirements(this.value);
        const allValid = Object.values(requirements).every(Boolean);
        
        if (!this.value) {
            showError('password', 'La contraseña es obligatoria');
        } else if (!allValid) {
            showError('password', 'La contraseña no cumple con todos los requisitos');
        } else {
            clearError('password');
        }
        
        const confirmPassword = document.getElementById('confirm_password');
        if (confirmPassword.value) {
            validatePasswordMatch();
        }
    });
    
    const confirmPasswordInput = document.getElementById('confirm_password');
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
    
    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (!confirmPassword) {
            showError('confirm-password', 'Debe confirmar su contraseña');
        } else if (password !== confirmPassword) {
            showError('confirm-password', 'Las contraseñas no coinciden');
        } else {
            clearError('confirm-password');
        }
    }
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validar todos los campos
        let isValid = true;
        
        // Nombre
        if (!nombreInput.value.trim()) {
            showError('nombre', 'El nombre completo es obligatorio');
            isValid = false;
        }
        
        // Email
        if (!emailInput.value.trim()) {
            showError('email', 'El correo electrónico es obligatorio');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
            showError('email', 'El formato del correo electrónico no es válido');
            isValid = false;
        }
        
        // Usuario
        if (!usuarioInput.value.trim()) {
            showError('usuario', 'El nombre de usuario es obligatorio');
            isValid = false;
        } else if (usuarioInput.value.length < 4) {
            showError('usuario', 'El nombre de usuario debe tener al menos 4 caracteres');
            isValid = false;
        }
        
        // Contraseña
        if (!passwordInput.value) {
            showError('password', 'La contraseña es obligatoria');
            isValid = false;
        } else {
            const requirements = checkPasswordRequirements(passwordInput.value);
            const allValid = Object.values(requirements).every(Boolean);
            
            if (!allValid) {
                showError('password', 'La contraseña no cumple con todos los requisitos');
                isValid = false;
            }
        }
        
        // Confirmar contraseña
        if (!confirmPasswordInput.value) {
            showError('confirm-password', 'Debe confirmar su contraseña');
            isValid = false;
        } else if (passwordInput.value !== confirmPasswordInput.value) {
            showError('confirm-password', 'Las contraseñas no coinciden');
            isValid = false;
        }
        
        // Si el formulario es válido, enviarlo
        if (isValid) {
            this.submit();
        } else {
            // Mostrar alerta general
            showAlertError('Por favor, corrija los errores en el formulario antes de continuar');
            
            // Hacer scroll al primer error
            const firstErrorField = document.querySelector('.input-container.error');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}