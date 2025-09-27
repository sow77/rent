/**
 * Gestor de sesiones - Renovación automática y verificación de expiración
 */

class SessionManager {
    constructor() {
        this.sessionTimeout = 30 * 60 * 1000; // 30 minutos en milisegundos
        this.warningTime = 5 * 60 * 1000; // 5 minutos antes de expirar
        this.checkInterval = 60 * 1000; // Verificar cada minuto
        this.lastActivity = Date.now();
        this.warningShown = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.startSessionCheck();
        this.updateLastActivity();
    }

    bindEvents() {
        // Actualizar actividad en eventos del usuario
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateLastActivity();
            }, true);
        });

        // Renovar sesión cuando la ventana vuelve a estar activa
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateLastActivity();
                this.renewSession();
            }
        });

        // Renovar sesión cuando la ventana recibe foco
        window.addEventListener('focus', () => {
            this.updateLastActivity();
            this.renewSession();
        });

        // No hacer logout automático en beforeunload para evitar 
        // cerrar sesión durante navegación interna del sitio

        // Detectar cuando la página se va a cerrar
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                // La página se oculta, pero no cerrar sesión inmediatamente
                // Solo marcar como inactivo
                this.lastActivity = Date.now() - (this.sessionTimeout - 1000); // Casi expirado
            }
        });
    }

    updateLastActivity() {
        this.lastActivity = Date.now();
        this.warningShown = false;
    }

    startSessionCheck() {
        setInterval(() => {
            this.checkSession();
        }, this.checkInterval);
    }

    checkSession() {
        const timeSinceLastActivity = Date.now() - this.lastActivity;
        const timeUntilExpiry = this.sessionTimeout - timeSinceLastActivity;

        // Si la sesión está a punto de expirar, mostrar advertencia
        if (timeUntilExpiry <= this.warningTime && !this.warningShown) {
            this.showSessionWarning(timeUntilExpiry);
        }

        // Si la sesión ha expirado, cerrar sesión
        if (timeSinceLastActivity >= this.sessionTimeout) {
            this.logout();
        }
    }

    showSessionWarning(timeUntilExpiry) {
        this.warningShown = true;
        const minutes = Math.ceil(timeUntilExpiry / (60 * 1000));
        
        // Crear modal de advertencia
        const warningModal = document.createElement('div');
        warningModal.className = 'modal fade';
        warningModal.id = 'sessionWarningModal';
        warningModal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle me-2"></i>Sesión a punto de expirar
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <p class="mb-3">Tu sesión expirará en <strong>${minutes} minutos</strong> por inactividad.</p>
                        <p class="text-muted">¿Deseas continuar con tu sesión?</p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-primary" onclick="sessionManager.renewSession()">
                            <i class="fas fa-sync me-1"></i>Continuar sesión
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="sessionManager.logout()">
                            <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(warningModal);
        
        // Mostrar el modal
        const modal = new bootstrap.Modal(warningModal);
        modal.show();

        // Auto-renovar si el usuario hace clic en cualquier lugar
        document.addEventListener('click', () => {
            this.renewSession();
            modal.hide();
        }, { once: true });
    }

    async renewSession() {
        try {
            const response = await fetch(`${APP_URL}/auth/renew-session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                this.updateLastActivity();
                this.showSuccessMessage('Sesión renovada correctamente');
            } else {
                console.warn('No se pudo renovar la sesión');
            }
        } catch (error) {
            console.error('Error al renovar sesión:', error);
        }
    }

    async logout() {
        try {
            // Usar sendBeacon para asegurar que la petición se envíe
            // incluso si la página se está cerrando
            if (navigator.sendBeacon) {
                const data = new FormData();
                data.append('logout', 'true');
                navigator.sendBeacon(`${APP_URL}/auth/logout`, data);
            } else {
                // Fallback para navegadores que no soportan sendBeacon
                const response = await fetch(`${APP_URL}/auth/logout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    keepalive: true // Mantener la conexión abierta
                });
            }
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
        }
        
        // Limpiar variables globales
        if (window.quickAuthUpdate) {
            window.quickAuthUpdate.updateAfterLogout();
        }
        
        // Redirigir al login
        window.location.href = `${APP_URL}/login?message=session_expired`;
    }

    showSuccessMessage(message) {
        // Crear toast de éxito
        const toastHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Agregar al contenedor de toasts
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Mostrar el toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remover el toast después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    // Método público para renovar sesión manualmente
    static renew() {
        if (window.sessionManager) {
            window.sessionManager.renewSession();
        }
    }

    // Método público para cerrar sesión manualmente
    static logout() {
        if (window.sessionManager) {
            window.sessionManager.logout();
        }
    }
}

// Crear instancia global solo si el usuario está autenticado
if (typeof currentUserId !== 'undefined' && currentUserId) {
    window.sessionManager = new SessionManager();
}

// Funciones globales para uso desde otros scripts
window.renewSession = () => SessionManager.renew();
window.logoutSession = () => SessionManager.logout();
