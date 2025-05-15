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
    const alertId = 'errorAlert-' + Date.now() + Math.random().toString(36).substr(2, 5);
    
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = 'alert-tailwind';
    alertElement.style.animation = 'slideIn 0.3s forwards';
    alertElement.style.marginBottom = '10px';
    alertElement.innerHTML = `
        <div class="alert-tailwind-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="alert-tailwind-content">
            <p class="alert-tailwind-message">${message}</p>
        </div>
        <button type="button" class="alert-tailwind-close" onclick="closeAlert('${alertId}')">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    container.appendChild(alertElement);
    
    // Posicionar el contenedor de alertas correctamente
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.right = '20px';
    container.style.zIndex = '1000';
    
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
    const emailInput = document.getElementById('email');
    const usuarioInput = document.getElementById('usuario');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');

    // Función para validar el nombre
    function validateNombre() {
        const value = nombreInput.value.trim();
        if (!value) {
            showError('nombre', 'El nombre completo es obligatorio');
            return false;
        }
        if (value.length < 3) {
            showError('nombre', 'El nombre debe tener al menos 3 caracteres');
            return false;
        }
        clearError('nombre');
        return true;
    }

    // Función para validar el email
    function validateEmail() {
        const value = emailInput.value.trim();
        if (!value) {
            showError('email', 'El correo electrónico es obligatorio');
            return false;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showError('email', 'El formato del correo electrónico no es válido');
            return false;
        }
        clearError('email');
        return true;
    }

    // Función para validar el usuario
    function validateUsuario() {
        const value = usuarioInput.value.trim();
        if (!value) {
            showError('usuario', 'El nombre de usuario es obligatorio');
            return false;
        }
        if (value.length < 4) {
            showError('usuario', 'El nombre de usuario debe tener al menos 4 caracteres');
            return false;
        }
        if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            showError('usuario', 'Solo se permiten letras, números y guiones bajos');
            return false;
        }
        clearError('usuario');
        return true;
    }

    // Función para validar la contraseña
    function validatePassword() {
        const value = passwordInput.value;
        if (!value) {
            showError('password', 'La contraseña es obligatoria');
            return false;
        }

        const requirements = checkPasswordRequirements(value);
        const allValid = Object.values(requirements).every(Boolean);
        
        if (!allValid) {
            showError('password', 'La contraseña no cumple con todos los requisitos');
            return false;
        }
        
        clearError('password');
        return true;
    }

    // Función para validar la confirmación de contraseña
    function validateConfirmPassword() {
        const value = confirmPasswordInput.value;
        if (!value) {
            showError('confirm-password', 'Debe confirmar su contraseña');
            return false;
        }
        if (value !== passwordInput.value) {
            showError('confirm-password', 'Las contraseñas no coinciden');
            return false;
        }
        clearError('confirm-password');
        return true;
    }

    // Event listeners para validación en tiempo real
    nombreInput.addEventListener('blur', validateNombre);
    emailInput.addEventListener('blur', validateEmail);
    usuarioInput.addEventListener('blur', validateUsuario);
    passwordInput.addEventListener('input', function() {
        checkPasswordRequirements(this.value);
        validatePassword();
        if (confirmPasswordInput.value) {
            validateConfirmPassword();
        }
    });
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);

    // Manejo del envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Limpiar alertas anteriores
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            alertContainer.innerHTML = '';
        }

        // Validar todos los campos
        const isNombreValid = validateNombre();
        const isEmailValid = validateEmail();
        const isUsuarioValid = validateUsuario();
        const isPasswordValid = validatePassword();
        const isConfirmPasswordValid = validateConfirmPassword();

        // Recolectar mensajes de error
        const errorMessages = [];
        
        if (!isNombreValid) {
            const error = document.getElementById('nombre-error').textContent;
            if (error) errorMessages.push(error);
        }
        
        if (!isEmailValid) {
            const error = document.getElementById('email-error').textContent;
            if (error) errorMessages.push(error);
        }
        
        if (!isUsuarioValid) {
            const error = document.getElementById('usuario-error').textContent;
            if (error) errorMessages.push(error);
        }
        
        if (!isPasswordValid) {
            const value = passwordInput.value;
            const requirements = checkPasswordRequirements(value);
            
            if (!value) {
                errorMessages.push('La contraseña es obligatoria');
            } else {
                if (!requirements.length) errorMessages.push('La contraseña debe tener al menos 8 caracteres');
                if (!requirements.uppercase) errorMessages.push('La contraseña debe contener al menos una letra mayúscula');
                if (!requirements.lowercase) errorMessages.push('La contraseña debe contener al menos una letra minúscula');
                if (!requirements.number) errorMessages.push('La contraseña debe contener al menos un número');
                if (!requirements.special) errorMessages.push('La contraseña debe contener al menos un carácter especial');
            }
        }
        
        if (!isConfirmPasswordValid) {
            const error = document.getElementById('confirm-password-error').textContent;
            if (error) errorMessages.push(error);
        }

        // Mostrar alertas de error
        if (errorMessages.length > 0) {
            // Mostrar cada mensaje de error como una alerta individual
            errorMessages.forEach((message, index) => {
                setTimeout(() => {
                    showAlertError(message);
                }, index * 300);
            });

            // Hacer scroll al primer campo con error
            const firstErrorField = document.querySelector('.input-container.error');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } else {
            // Si todo es válido, enviar el formulario
            this.submit();
        }
    });

    // Función para mostrar el estado de los requisitos de contraseña
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
        
        if (meter) {
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
        }
        
        return requirements;
    }

    // Función para actualizar la UI de los requisitos
    function updateRequirementUI(reqId, isValid) {
        const reqElement = document.getElementById(reqId);
        if (reqElement) {
            if (isValid) {
                reqElement.classList.add('valid');
                reqElement.classList.remove('invalid');
                reqElement.querySelector('i').className = 'bi bi-check-circle';
                reqElement.querySelector('i').style.color = '#10B981';
            } else {
                reqElement.classList.add('invalid');
                reqElement.classList.remove('valid');
                reqElement.querySelector('i').className = 'bi bi-x-circle';
                reqElement.querySelector('i').style.color = '#EF4444';
            }
        }
    }
}