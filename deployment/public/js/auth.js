// Funciones para validación visual por campo (definidas globalmente)
function showFieldError(fieldName, message) {
    const field = document.getElementById(fieldName);
    const errorDiv = document.getElementById(`${fieldName}Error`);
    
    if (field) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
    }
    
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
    }
}

function hideFieldError(fieldName) {
    const field = document.getElementById(fieldName);
    const errorDiv = document.getElementById(`${fieldName}Error`);
    
    if (field) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    }
    
    if (errorDiv) {
        errorDiv.classList.add('d-none');
    }
}

function clearAllFieldErrors() {
    const fields = ['registerName', 'registerEmail', 'registerPhone', 'registerPassword', 'registerPasswordConfirm'];
    fields.forEach(fieldName => {
        hideFieldError(fieldName);
    });
}

// Función de validación del formulario de registro
function validateRegistrationForm() {
    const name = document.getElementById('registerName').value.trim();
    const email = document.getElementById('registerEmail').value.trim();
    const phone = document.getElementById('registerPhone').value.trim();
    const password = document.getElementById('registerPassword').value;
    const passwordConfirm = document.getElementById('registerPasswordConfirm').value;

    const errors = [];

    // Validar nombre
    if (name.length < 2) {
        errors.push('El nombre debe tener al menos 2 caracteres');
    }

        // Validar email (validación básica, la real se hace en el servidor)
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('El formato del email no es válido');
        }

    // Validar teléfono (formato internacional)
    if (!/^\+[1-9]\d{8,14}$/.test(phone)) {
        errors.push('El formato del número de teléfono no es válido. Use formato internacional: +1 234 567 8900');
    } else {
        // Verificar que no sea un número de prueba
        const testNumbers = [
            '+34612345678', '+34612345679', '+34612345680',
            '+34600000000', '+34611111111', '+34622222222',
            '+34633333333', '+34644444444', '+34655555555',
            '+34666666666', '+34677777777', '+34688888888',
            '+34699999999', '+3461234567', '+346123456',
            '+34612345', '+3461234', '+346123',
            '+34612', '+3461', '+346',
            '+1234567890', '+123456789', '+12345678',
            '+1111111111', '+2222222222', '+3333333333',
            '+4444444444', '+5555555555', '+6666666666',
            '+7777777777', '+8888888888', '+9999999999'
        ];
        
        if (testNumbers.includes(phone)) {
            errors.push('No se permiten números de teléfono de prueba');
        }
    }

    // Validar contraseña
    if (password.length < 8) {
        errors.push('La contraseña debe tener al menos 8 caracteres');
    }

    if (!/[a-z]/.test(password)) {
        errors.push('La contraseña debe contener al menos una letra minúscula');
    }

    if (!/[A-Z]/.test(password)) {
        errors.push('La contraseña debe contener al menos una letra mayúscula');
    }

    if (!/[0-9]/.test(password)) {
        errors.push('La contraseña debe contener al menos un número');
    }

    if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) {
        errors.push('La contraseña debe contener al menos un carácter especial');
    }

    if (password !== passwordConfirm) {
        errors.push('Las contraseñas no coinciden');
    }

    return errors;
}

document.addEventListener('DOMContentLoaded', function() {
    // Configurar validación de formularios
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Función para alternar la visibilidad de la contraseña
    function setupPasswordToggle(inputId, toggleId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        if (input && toggle) {
            toggle.addEventListener('click', () => {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                toggle.querySelector('i').classList.toggle('fa-eye');
                toggle.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
    }

    // Configurar toggles de contraseña
    setupPasswordToggle('loginPassword', 'toggleLoginPassword');
    setupPasswordToggle('registerPassword', 'toggleRegisterPassword');
    setupPasswordToggle('registerPasswordConfirm', 'toggleRegisterPasswordConfirm');
    setupPasswordToggle('newPassword', 'toggleNewPassword');
    setupPasswordToggle('confirmNewPassword', 'toggleConfirmNewPassword');

    // Configurar validaciones sofisticadas para el modal de registro
    setupAdvancedRegistrationValidation();
    
    // Manejar el formulario de registro
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const errorDiv = document.getElementById('registerError');
            if (errorDiv) {
                errorDiv.classList.add('d-none');
                errorDiv.innerHTML = '';
            }

            // Validar formulario
            const errors = validateRegistrationForm();
            if (errors.length > 0) {
                if (errorDiv) {
                    errorDiv.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }

            // Verificar reCAPTCHA
            const recaptchaResponse = grecaptcha.getResponse();
            if (!recaptchaResponse) {
                const recaptchaError = document.getElementById('recaptchaError');
                if (recaptchaError) {
                    recaptchaError.textContent = 'Por favor, completa el reCAPTCHA';
                    recaptchaError.classList.remove('d-none');
                }
                return;
            }

            const formData = new FormData(this);
            formData.append('g-recaptcha-response', recaptchaResponse);
            
            try {
                const response = await fetch(`${APP_URL}/auth/register`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('El servidor devolvió una respuesta no válida. Verifica que el servidor esté funcionando correctamente.');
                }
                
                const data = await response.json();
            
             if (data.success) {
                 // Mostrar mensaje de éxito
                 if (errorDiv) {
                     errorDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                     errorDiv.classList.remove('d-none');
                 }
                 
                 // Cerrar el modal de registro
                 const registerModalElement = document.getElementById('registerModal');
                 if (registerModalElement) {
                     const registerModal = bootstrap.Modal.getInstance(registerModalElement);
                     if (registerModal) {
                         registerModal.hide();
                     }
                 }
                 
                 // Limpiar formulario
                 this.reset();
                 
                 // Redirigir después de un breve delay
                 setTimeout(() => {
                     if (data.data && data.data.redirect) {
                         window.location.href = data.data.redirect;
                     } else {
                         window.location.href = `${APP_URL}/auth/login?registered=1`;
                     }
                 }, 1500);
            } else {
                // Limpiar errores anteriores
                clearAllFieldErrors();
                
                if (data.data && Array.isArray(data.data)) {
                    // Mapear errores específicos a campos
                    data.data.forEach(error => {
                        if (error.includes('nombre') || error.includes('Nombre')) {
                            showFieldError('registerName', error);
                        } else if (error.includes('email') || error.includes('Email') || error.includes('dominio') || error.includes('MX')) {
                            showFieldError('registerEmail', error);
                        } else if (error.includes('teléfono') || error.includes('Teléfono') || error.includes('teléfono')) {
                            showFieldError('registerPhone', error);
                        } else if (error.includes('contraseña') || error.includes('Contraseña') || error.includes('secuencias') || error.includes('común')) {
                            showFieldError('registerPassword', error);
                        } else if (error.includes('coinciden') || error.includes('confirmar')) {
                            showFieldError('registerPasswordConfirm', error);
                        } else {
                            // Error general
                            if (errorDiv) {
                                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + error;
                                errorDiv.classList.remove('d-none');
                            }
                        }
                    });
                } else if (errorDiv) {
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
                    errorDiv.classList.remove('d-none');
                }
            }
        } catch (error) {
            console.error('Error en registro:', error);
            if (errorDiv) {
                // Determinar el tipo de error específico
                let errorMessage = 'Error al procesar la solicitud.';
                
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Error de conexión. Verifica que el servidor esté funcionando.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Error al procesar la respuesta del servidor.';
                } else {
                    errorMessage = `Error: ${error.message}`;
                }
                
                errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}`;
                errorDiv.classList.remove('d-none');
            }
        }
    });
    }

    // Manejar el formulario de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const errorDiv = document.getElementById('loginError');
            
            try {
                const response = await fetch(`${APP_URL}/auth/login`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Usar actualización rápida en lugar de redirección completa
                    if (window.quickAuthUpdate && data.data && data.data.user) {
                        // Actualizar UI dinámicamente
                        await window.quickAuthUpdate.updateAfterLogin(data.data.user);
                        
                        // Si hay una redirección específica, ir a ella después de un breve delay
                        if (data.data.redirect && data.data.redirect !== window.location.href) {
                            setTimeout(() => {
                                window.location.href = data.data.redirect;
                            }, 1500);
                        }
                    } else {
                        // Fallback a redirección normal si no hay datos de usuario
                    window.location.href = data.data.redirect || APP_URL;
                    }
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Error en login:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'Error al procesar la solicitud. Por favor, inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                }
            }
        });
    }

    // Las funciones de validación están definidas globalmente al inicio del archivo
    
    // Validación en tiempo real para limpiar errores
    function setupRealTimeValidation() {
        const fields = ['registerName', 'registerEmail', 'registerPhone', 'registerPassword', 'registerPasswordConfirm'];
        
        fields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field) {
                field.addEventListener('input', function() {
                    // Limpiar error del campo cuando el usuario empiece a escribir
                    hideFieldError(fieldName);
                });
                
                field.addEventListener('blur', function() {
                    // Validación básica en tiempo real
                    if (fieldName === 'registerEmail' && this.value) {
                        const email = this.value;
                        const domain = email.split('@')[1];
                        const commonDomains = ['gmail.com', 'outlook.com', 'hotmail.com', 'yahoo.com', 'live.com'];
                        
                        if (domain && !commonDomains.includes(domain.toLowerCase())) {
                            showFieldError(fieldName, 'Usa un email de un proveedor confiable (Gmail, Outlook, Yahoo, etc.)');
                        }
                    }
                    
                    if (fieldName === 'registerPassword' && this.value) {
                        const password = this.value;
                        if (password.length < 8) {
                            showFieldError(fieldName, 'La contraseña debe tener al menos 8 caracteres');
                        }
                    }
                    
                    if (fieldName === 'registerPasswordConfirm' && this.value) {
                        const password = document.getElementById('registerPassword').value;
                        const confirmPassword = this.value;
                        
                        if (password !== confirmPassword) {
                            showFieldError(fieldName, 'Las contraseñas no coinciden');
                }
            }
        });
    }
        });
    }
    
    // Inicializar validación en tiempo real
    setupRealTimeValidation();
    
    // El manejador del formulario de registro se define más abajo en el archivo

    // Configurar botones para abrir modales
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetModal = this.getAttribute('data-bs-target');
            const modalId = targetModal.replace('#', '');
            
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                // Limpiar formulario si existe
        const form = modalElement.querySelector('form');
        const errorDiv = modalElement.querySelector('.alert');
        if (form) {
            form.reset();
            form.classList.remove('was-validated');
        }
        if (errorDiv) {
            errorDiv.classList.add('d-none');
            errorDiv.classList.remove('alert-success');
            errorDiv.classList.add('alert-danger');
        }

                // Mostrar modal
                let modal = bootstrap.Modal.getInstance(modalElement);
                if (!modal) {
                    modal = new bootstrap.Modal(modalElement);
                }
                modal.show();
            }
        });
    });


    // Configurar eventos de los modales
    ['loginModal', 'registerModal', 'forgotPasswordModal', 'resetPasswordModal'].forEach(modalId => {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function() {
                const form = this.querySelector('form');
                const errorDiv = this.querySelector('.alert');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
                if (errorDiv) {
                    errorDiv.classList.add('d-none');
                    errorDiv.classList.remove('alert-success');
                    errorDiv.classList.add('alert-danger');
                }
                
                // Resetear pasos del modal de recuperación
                if (modalId === 'forgotPasswordModal') {
                    document.getElementById('forgotPasswordStep1').classList.remove('d-none');
                    document.getElementById('forgotPasswordStep2').classList.add('d-none');
                }
            });
        }
    });

    // Configurar enlace "¿Olvidaste tu contraseña?"
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Cerrar modal de login
            const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
            if (loginModal) {
                loginModal.hide();
            }
            
            // Abrir modal de recuperación
            setTimeout(() => {
                const forgotModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
                forgotModal.show();
            }, 300);
        });
    }

    // Manejar formulario de recuperación de contraseña
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('forgotEmail').value.trim();
            const errorDiv = document.getElementById('forgotPasswordError');
            
            if (!email) {
                if (errorDiv) {
                    errorDiv.textContent = 'Por favor ingrese su email';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const response = await fetch(`${APP_URL}/auth/forgot-password`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar paso 2 (email enviado)
                    document.getElementById('forgotPasswordStep1').classList.add('d-none');
                    document.getElementById('forgotPasswordStep2').classList.remove('d-none');
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Error en recuperación de contraseña:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'Error al procesar la solicitud. Por favor, inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                }
            }
        });
    }

    // Manejar formulario de reset de contraseña
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const token = document.getElementById('resetToken').value;
            const password = document.getElementById('newPassword').value;
            const passwordConfirm = document.getElementById('confirmNewPassword').value;
            const errorDiv = document.getElementById('resetPasswordError');
            
            if (!token) {
                if (errorDiv) {
                    errorDiv.textContent = 'Token de recuperación no válido';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            
            if (password !== passwordConfirm) {
                if (errorDiv) {
                    errorDiv.textContent = 'Las contraseñas no coinciden';
                    errorDiv.classList.remove('d-none');
                }
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('token', token);
                formData.append('password', password);
                formData.append('password_confirm', passwordConfirm);
                
                const response = await fetch(`${APP_URL}/auth/reset-password`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar mensaje de éxito y cerrar modal
                    if (errorDiv) {
                        errorDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                        errorDiv.classList.remove('d-none');
                    }
                    
                    // Cerrar modal después de un delay
                    setTimeout(() => {
                        const resetModal = bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal'));
                        if (resetModal) {
                            resetModal.hide();
                        }
                        
                        // Abrir modal de login
                        setTimeout(() => {
                            const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                            loginModal.show();
                        }, 300);
                    }, 2000);
                } else {
                    if (errorDiv) {
                        errorDiv.textContent = data.message;
                        errorDiv.classList.remove('d-none');
                    }
                }
            } catch (error) {
                console.error('Error en reset de contraseña:', error);
                if (errorDiv) {
                    errorDiv.textContent = 'Error al procesar la solicitud. Por favor, inténtalo de nuevo.';
                    errorDiv.classList.remove('d-none');
                }
            }
        });
    }

    // Función para abrir modal de reset con token (desde URL)
    window.openResetPasswordModal = function(token) {
        if (token) {
            document.getElementById('resetToken').value = token;
            const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            resetModal.show();
        }
    };
});

// Función para configurar validaciones sofisticadas del registro
function setupAdvancedRegistrationValidation() {
    const passwordInput = document.getElementById('registerPassword');
    const phoneInput = document.getElementById('registerPhone');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const registerForm = document.getElementById('registerForm');

    if (!passwordInput || !phoneInput || !registerForm) return;

    // Validación de contraseña en tiempo real
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        
        if (passwordStrength) {
            passwordStrength.style.width = strength.score + '%';
            passwordStrength.className = 'progress-bar ' + strength.class;
        }
        if (passwordStrengthText) {
            passwordStrengthText.textContent = strength.text;
            passwordStrengthText.className = 'text-' + strength.textClass;
        }
    });

    // Validación de teléfono en tiempo real
    phoneInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9+]/g, '');
        if (value && !value.startsWith('+')) {
            value = '+' + value;
        }
        this.value = value;
    });

    // Función para calcular la fortaleza de la contraseña
    function calculatePasswordStrength(password) {
        let score = 0;
        let feedback = [];

        if (password.length >= 8) score += 20;
        else feedback.push('Al menos 8 caracteres');

        if (/[a-z]/.test(password)) score += 20;
        else feedback.push('Letras minúsculas');

        if (/[A-Z]/.test(password)) score += 20;
        else feedback.push('Letras mayúsculas');

        if (/[0-9]/.test(password)) score += 20;
        else feedback.push('Números');

        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~`]/.test(password)) score += 20;
        else feedback.push('Caracteres especiales');

        if (password.length > 12) score += 10;
        if (password.length > 16) score += 10;

        let className = 'bg-danger';
        let textClass = 'danger';
        let text = 'Muy débil';

        if (score >= 80) {
            className = 'bg-success';
            textClass = 'success';
            text = 'Muy fuerte';
        } else if (score >= 60) {
            className = 'bg-warning';
            textClass = 'warning';
            text = 'Fuerte';
        } else if (score >= 40) {
            className = 'bg-info';
            textClass = 'info';
            text = 'Media';
        } else if (score >= 20) {
            className = 'bg-warning';
            textClass = 'warning';
            text = 'Débil';
        }

        if (feedback.length > 0) {
            text += ' - Falta: ' + feedback.join(', ');
        }

        return { score, class: className, text, textClass };
    }

    // La función validateRegistrationForm ya está definida globalmente al inicio del archivo

    // El manejador del formulario de registro ya está definido arriba
    /*
    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const errorDiv = document.getElementById('registerError');
        if (errorDiv) {
            errorDiv.classList.add('d-none');
            errorDiv.innerHTML = '';
        }

        // Validar formulario
        const errors = validateRegistrationForm();
        if (errors.length > 0) {
            if (errorDiv) {
                errorDiv.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
                errorDiv.classList.remove('d-none');
            }
            return;
        }

        const formData = new FormData(this);
        
            try {
                const response = await fetch(`${APP_URL}/auth/register`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('El servidor devolvió una respuesta no válida. Verifica que el servidor esté funcionando correctamente.');
                }
                
                const data = await response.json();
            
            if (data.success) {
                if (errorDiv) {
                    errorDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    errorDiv.classList.remove('d-none');
                }
                
                // Cerrar modal y redirigir después de un breve delay
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                    if (modal) modal.hide();
                    
                    if (data.data && data.data.redirect) {
                        window.location.href = data.data.redirect;
                    } else {
                        window.location.href = `${APP_URL}/auth/login?registered=1`;
                    }
                }, 1500);
            } else {
                // Limpiar errores anteriores
                clearAllFieldErrors();
                
                if (data.data && Array.isArray(data.data)) {
                    // Mapear errores específicos a campos
                    data.data.forEach(error => {
                        if (error.includes('nombre') || error.includes('Nombre')) {
                            showFieldError('registerName', error);
                        } else if (error.includes('email') || error.includes('Email') || error.includes('dominio') || error.includes('MX')) {
                            showFieldError('registerEmail', error);
                        } else if (error.includes('teléfono') || error.includes('Teléfono') || error.includes('teléfono')) {
                            showFieldError('registerPhone', error);
                        } else if (error.includes('contraseña') || error.includes('Contraseña') || error.includes('secuencias') || error.includes('común')) {
                            showFieldError('registerPassword', error);
                        } else if (error.includes('coinciden') || error.includes('confirmar')) {
                            showFieldError('registerPasswordConfirm', error);
                        } else {
                            // Error general
                            if (errorDiv) {
                                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + error;
                                errorDiv.classList.remove('d-none');
                            }
                        }
                    });
                } else if (errorDiv) {
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + data.message;
                    errorDiv.classList.remove('d-none');
                }
            }
        } catch (error) {
            console.error('Error en registro:', error);
            if (errorDiv) {
                // Determinar el tipo de error específico
                let errorMessage = 'Error al procesar la solicitud.';
                
                if (error.name === 'TypeError' && error.message.includes('fetch')) {
                    errorMessage = 'Error de conexión. Verifica que el servidor esté funcionando.';
                } else if (error.message.includes('JSON')) {
                    errorMessage = 'Error al procesar la respuesta del servidor.';
                } else {
                    errorMessage = `Error: ${error.message}`;
                }
                
                errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${errorMessage}`;
                errorDiv.classList.remove('d-none');
            }
        }
    });
    */
} 