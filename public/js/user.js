// public/js/user.js - Sistema de usuario dinámico

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Inicializando sistema de usuario dinámico');
    
    // Inicializar todas las funcionalidades
    initUserSystem();
    initProfileValidation();
    initReservationActions();
    initDashboardRefresh();
    initRealTimeUpdates();
});

/**
 * Inicializar sistema principal de usuario
 */
function initUserSystem() {
    // Configurar validación de formularios (excluyendo formularios de autenticación y recuperación)
    const forms = document.querySelectorAll('.needs-validation:not(#loginForm):not(#registerForm):not(#forgotPasswordForm):not(#resetPasswordForm):not(#resetPasswordPageForm):not(#profileForm)');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });

    // Configurar tooltips
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Configurar modales dinámicos
    initDynamicModals();
}

/**
 * Manejar envío de formularios con AJAX
 */
function handleFormSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Mostrar loading
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;
    
    // Determinar la URL del formulario
    const url = form.action || `${APP_URL}/ajax_routes.php/user/updateProfile`;
    
    // Verificar si la URL es válida antes de hacer la petición
    if (url.includes('ajax_routes.php')) {
        showNotification('error', 'Función temporalmente no disponible');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            
            // Si es actualización de perfil, actualizar la navbar
            if (url.includes('updateProfile')) {
                updateNavbarUserInfo(data.user);
            }
            
            // Redirigir si se especifica
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error en formulario:', error);
        showNotification('error', 'Error al procesar la solicitud');
    })
    .finally(() => {
        // Restaurar botón
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

/**
 * Inicializar validación de perfil
 */
function initProfileValidation() {
    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const currentPasswordInput = document.getElementById('current_password');
    
    if (newPasswordInput && confirmPasswordInput) {
        // Validación en tiempo real de contraseñas
        newPasswordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
        
        // Validación de fortaleza de contraseña
        newPasswordInput.addEventListener('input', validatePasswordStrength);
    }
    
    if (currentPasswordInput) {
        // Verificar contraseña actual en tiempo real
        currentPasswordInput.addEventListener('blur', validateCurrentPassword);
    }
}

/**
 * Validar contraseñas en tiempo real
 */
function validatePasswords() {
    const newPassword = document.getElementById('new_password')?.value;
    const confirmPassword = document.getElementById('confirm_password')?.value;
    
    if (newPassword && confirmPassword) {
        if (newPassword !== confirmPassword) {
            document.getElementById('confirm_password').setCustomValidity('Las contraseñas no coinciden');
            showFieldError('confirm_password', 'Las contraseñas no coinciden');
        } else {
            document.getElementById('confirm_password').setCustomValidity('');
            hideFieldError('confirm_password');
        }
    }
}

/**
 * Validar fortaleza de contraseña
 */
function validatePasswordStrength() {
    const password = this.value;
    const strengthIndicator = document.getElementById('password-strength');
    
    if (!strengthIndicator) {
        // Crear indicador de fortaleza si no existe
        const indicator = document.createElement('div');
        indicator.id = 'password-strength';
        indicator.className = 'mt-2';
        this.parentNode.appendChild(indicator);
    }
    
    const strength = calculatePasswordStrength(password);
    const strengthText = ['Muy débil', 'Débil', 'Media', 'Fuerte', 'Muy fuerte'];
    const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
    
    if (password.length > 0) {
        strengthIndicator.innerHTML = `
            <div class="progress mb-2" style="height: 5px;">
                <div class="progress-bar bg-${strengthColors[strength]}" style="width: ${(strength + 1) * 20}%"></div>
            </div>
            <small class="text-${strengthColors[strength]}">${strengthText[strength]}</small>
        `;
    } else {
        strengthIndicator.innerHTML = '';
    }
}

/**
 * Calcular fortaleza de contraseña
 */
function calculatePasswordStrength(password) {
    let score = 0;
    
    if (password.length >= 8) score++;
    if (/[a-z]/.test(password)) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;
    
    return Math.min(score - 1, 4);
}

/**
 * Validar contraseña actual con AJAX
 */
function validateCurrentPassword() {
    const currentPassword = this.value;
    const userId = getCurrentUserId();
    
    if (!currentPassword || !userId) return;
    
    // Función temporalmente deshabilitada - endpoint no implementado
    // fetch(`${APP_URL}/ajax_routes.php/user/validatePassword`, {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-Requested-With': 'XMLHttpRequest'
    //     },
    //     body: JSON.stringify({
    //         current_password: currentPassword,
    //         user_id: userId
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (!data.valid) {
    //         showFieldError('current_password', 'Contraseña actual incorrecta');
    //     } else {
    //         hideFieldError('current_password');
    //     }
    // })
    // .catch(error => {
    //     console.error('Error validando contraseña:', error);
    // });
}

/**
 * Inicializar acciones de reservas
 */
function initReservationActions() {
    // Configurar botones de cancelación de reservas
    document.querySelectorAll('.cancel-reservation-btn').forEach(btn => {
        btn.addEventListener('click', handleReservationCancel);
    });
    
    // Configurar filtros de reservas
    const filterSelect = document.getElementById('reservation-filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', filterReservations);
    }
}

/**
 * Manejar cancelación de reservas
 */
function handleReservationCancel(event) {
    event.preventDefault();
    
    const reservationId = event.target.dataset.reservationId;
    const reservationCard = event.target.closest('.card');
    
    if (confirm('¿Estás seguro de que quieres cancelar esta reserva?')) {
        // Mostrar loading
        const originalText = event.target.innerHTML;
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelando...';
        event.target.disabled = true;
        
            // Función temporalmente deshabilitada - endpoint no implementado
            // fetch(`${APP_URL}/ajax_routes.php/user/cancelReservation`, {
            //     method: 'POST',
            //     headers: {
            //         'Content-Type': 'application/json',
            //         'X-Requested-With': 'XMLHttpRequest'
            //     },
            //     body: JSON.stringify({
            //         reservation_id: reservationId
            //     })
            // })
            //     .then(response => response.json())
            //     .then(data => {
            //         if (data.success) {
            //             showNotification('success', data.message);
            //             
            //             // Actualizar la tarjeta de reserva
            //             updateReservationCard(reservationCard, 'cancelada');
            //             
            //             // Actualizar estadísticas
            //             updateReservationStats();
            //         } else {
            //             showNotification('error', data.message);
            //         }
            //     })
            //     .catch(error => {
            //         console.error('Error cancelando reserva:', error);
            //         showNotification('error', 'Error al cancelar la reserva');
            //     })
            //     .finally(() => {
            //         // Restaurar botón
            //         event.target.innerHTML = originalText;
            //         event.target.disabled = false;
            //     });
            
            // Mostrar mensaje temporal
            showNotification('info', 'Función de cancelación temporalmente deshabilitada');
            event.target.innerHTML = originalText;
            event.target.disabled = false;
    }
}

/**
 * Actualizar tarjeta de reserva
 */
function updateReservationCard(card, newStatus) {
    const statusBadge = card.querySelector('.badge');
    const actionButton = card.querySelector('.card-footer .btn');
    
    if (statusBadge) {
        statusBadge.className = `badge bg-${newStatus === 'cancelada' ? 'secondary' : 'success'}`;
        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }
    
    if (actionButton) {
        if (newStatus === 'cancelada') {
            actionButton.innerHTML = '<span class="badge bg-secondary">Reserva Cancelada</span>';
            actionButton.disabled = true;
        }
    }
}

/**
 * Filtrar reservas
 */
function filterReservations() {
    const filter = this.value;
    const reservationCards = document.querySelectorAll('.reservation-card');
    
    reservationCards.forEach(card => {
        const status = card.dataset.status;
        
        if (filter === 'all' || status === filter) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
    
    // Actualizar contador
    updateVisibleReservationsCount();
}

/**
 * Inicializar actualización automática del dashboard
 */
function initDashboardRefresh() {
    // Actualizar estadísticas cada 30 segundos
    setInterval(updateDashboardStats, 30000);
    
    // Actualizar cuando la pestaña vuelve a estar activa
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateDashboardStats();
        }
    });
}

/**
 * Actualizar estadísticas del dashboard
 */
function updateDashboardStats() {
    // Solo hacer la llamada AJAX si estamos en la página del dashboard
    if (!document.getElementById('dashboard-stats')) {
        return;
    }
    
    // Función temporalmente deshabilitada - endpoint no implementado
    // fetch(`${APP_URL}/ajax_routes.php/user/getStats`, {
    //     method: 'GET',
    //     headers: {
    //         'X-Requested-With': 'XMLHttpRequest'
    //     }
    // })
    // .then(response => {
    //     if (!response.ok) {
    //         throw new Error('Network response was not ok');
    //     }
    //     return response.json();
    // })
    // .then(data => {
    //     if (data.success) {
    //         updateStatsDisplay(data.stats);
    //     }
    // })
    // .catch(error => {
    //     console.error('Error actualizando estadísticas:', error);
    // });
}

/**
 * Actualizar display de estadísticas
 */
function updateStatsDisplay(stats) {
    const elements = {
        'total-reservations': stats.total_reservations,
        'active-reservations': stats.active_reservations,
        'total-spent': `€${parseFloat(stats.total_spent).toFixed(2)}`
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
}

/**
 * Inicializar actualizaciones en tiempo real
 */
function initRealTimeUpdates() {
    // WebSocket o polling para actualizaciones en tiempo real
    // Por ahora usamos polling cada 60 segundos
    setInterval(checkForUpdates, 60000);
}

/**
 * Verificar actualizaciones
 */
function checkForUpdates() {
    // Función temporalmente deshabilitada - endpoint no implementado
    // fetch(`${APP_URL}/ajax_routes.php/user/checkUpdates`, {
    //     method: 'GET',
    //     headers: {
    //         'X-Requested-With': 'XMLHttpRequest'
    //     }
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (data.hasUpdates) {
    //         showNotification('info', 'Tienes nuevas actualizaciones');
    //         location.reload();
    //     }
    // })
    // .catch(error => {
    //     console.error('Error verificando actualizaciones:', error);
    // });
}

/**
 * Inicializar modales dinámicos
 */
function initDynamicModals() {
    // Configurar modales que se cargan dinámicamente
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-modal-url]')) {
            e.preventDefault();
            loadModalContent(e.target.dataset.modalUrl, e.target.dataset.modalTitle);
        }
    });
}

/**
 * Cargar contenido de modal dinámicamente
 */
function loadModalContent(url, title) {
    const modal = document.getElementById('dynamicModal');
    const modalTitle = modal.querySelector('.modal-title');
    const modalBody = modal.querySelector('.modal-body');
    
    modalTitle.textContent = title || 'Cargando...';
    modalBody.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    fetch(url)
    .then(response => response.text())
    .then(html => {
        modalBody.innerHTML = html;
    })
    .catch(error => {
        modalBody.innerHTML = '<div class="alert alert-danger">Error al cargar el contenido</div>';
    });
}

/**
 * Mostrar notificación
 */
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Mostrar error de campo
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(`${fieldId}-error`) || createErrorDiv(fieldId);
    
    field.classList.add('is-invalid');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

/**
 * Ocultar error de campo
 */
function hideFieldError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.getElementById(`${fieldId}-error`);
    
    field.classList.remove('is-invalid');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

/**
 * Crear div de error
 */
function createErrorDiv(fieldId) {
    const field = document.getElementById(fieldId);
    const errorDiv = document.createElement('div');
    errorDiv.id = `${fieldId}-error`;
    errorDiv.className = 'invalid-feedback';
    errorDiv.style.display = 'none';
    
    field.parentNode.appendChild(errorDiv);
    return errorDiv;
}

/**
 * Actualizar información del usuario en la navbar
 */
function updateNavbarUserInfo(user) {
    const userNameElement = document.querySelector('.navbar .dropdown-toggle span');
    if (userNameElement && user.name) {
        userNameElement.textContent = user.name;
    }
}

/**
 * Obtener ID del usuario actual
 */
function getCurrentUserId() {
    // Esto se puede obtener de una variable global o del DOM
    return window.currentUserId || null;
}

/**
 * Actualizar estadísticas de reservas
 */
function updateReservationStats() {
    const reservationCards = document.querySelectorAll('.reservation-card');
    const stats = {
        total: reservationCards.length,
        confirmadas: 0,
        pendientes: 0,
        canceladas: 0,
        totalGastado: 0
    };
    
    reservationCards.forEach(card => {
        const status = card.dataset.status;
        const cost = parseFloat(card.dataset.cost) || 0;
        
        stats[status]++;
        if (status !== 'cancelada') {
            stats.totalGastado += cost;
        }
    });
    
    // Actualizar display de estadísticas
    document.getElementById('total-reservations').textContent = stats.total;
    document.getElementById('confirmed-reservations').textContent = stats.confirmadas;
    document.getElementById('pending-reservations').textContent = stats.pendientes;
    document.getElementById('total-spent').textContent = `€${stats.totalGastado.toFixed(2)}`;
}

/**
 * Actualizar contador de reservas visibles
 */
function updateVisibleReservationsCount() {
    const visibleCards = document.querySelectorAll('.reservation-card[style*="block"], .reservation-card:not([style*="none"])');
    const counter = document.getElementById('visible-reservations-count');
    if (counter) {
        counter.textContent = visibleCards.length;
    }
}

// Exportar funciones para uso global
window.UserSystem = {
    showNotification,
    updateStatsDisplay,
    handleReservationCancel,
    validatePasswords,
    updateNavbarUserInfo
}; 