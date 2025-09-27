/**
 * Sistema de actualización rápida de autenticación
 * Actualiza la UI sin recargar la página completa
 */

class QuickAuthUpdate {
    constructor() {
        this.isUpdating = false;
    }

    /**
     * Actualizar la UI después del login exitoso
     */
    async updateAfterLogin(userData) {
        if (this.isUpdating) return;
        this.isUpdating = true;

        try {
            // Mostrar indicador de carga
            this.showLoadingIndicator();

            // Actualizar variables globales
            this.updateGlobalVariables(userData);

            // Actualizar navbar
            await this.updateNavbar(userData);

            // Actualizar otros elementos de la página
            this.updatePageElements(userData);

            // Ocultar indicador de carga
            this.hideLoadingIndicator();

            // Mostrar mensaje de éxito
            this.showSuccessMessage('¡Bienvenido! Has iniciado sesión correctamente.');

        } catch (error) {
            console.error('Error actualizando UI:', error);
            this.hideLoadingIndicator();
        } finally {
            this.isUpdating = false;
        }
    }

    /**
     * Actualizar variables globales de JavaScript
     */
    updateGlobalVariables(userData) {
        // Actualizar variables globales
        window.currentUserId = userData.id;
        window.currentUserName = userData.name;
        window.currentUserRole = userData.role;
        window.currentUserEmail = userData.email;
        
        // También actualizar las variables en el scope global
        if (typeof currentUserId !== 'undefined') {
            currentUserId = userData.id;
        }
        if (typeof currentUserName !== 'undefined') {
            currentUserName = userData.name;
        }
        if (typeof currentUserRole !== 'undefined') {
            currentUserRole = userData.role;
        }
    }

    /**
     * Actualizar el navbar dinámicamente
     */
    async updateNavbar(userData) {
        const navbarNav = document.querySelector('.navbar-nav:last-child');
        if (!navbarNav) return;

        // Crear nuevo HTML para el navbar del usuario autenticado
        const userNavHTML = `
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle fa-lg me-2"></i>
                    <span class="d-none d-sm-inline">${userData.name}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <a class="dropdown-item" href="${APP_URL}/user/dashboard">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="${APP_URL}/user/profile">
                            <i class="fas fa-user-cog me-2"></i>Perfil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="${APP_URL}/user/reservations">
                            <i class="fas fa-list me-2"></i>Mis Reservas
                        </a>
                    </li>
                    ${userData.role === 'admin' ? `
                    <li>
                        <a class="dropdown-item" href="${APP_URL}/admin">
                            <i class="fas fa-cog me-2"></i>Administración
                        </a>
                    </li>
                    ` : ''}
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="${APP_URL}/logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </li>
        `;

        // Reemplazar el contenido del navbar
        navbarNav.innerHTML = userNavHTML;

        // Reinicializar tooltips y dropdowns de Bootstrap
        this.reinitializeBootstrapComponents();
    }

    /**
     * Actualizar otros elementos de la página
     */
    updatePageElements(userData) {
        // Actualizar botones de reserva si existen
        const reserveButtons = document.querySelectorAll('[data-action="reserve"]');
        reserveButtons.forEach(button => {
            // Los botones de reserva ahora funcionarán para usuarios autenticados
            button.style.display = 'inline-block';
        });

        // Actualizar mensajes de autenticación
        const authMessages = document.querySelectorAll('.auth-message');
        authMessages.forEach(message => {
            message.style.display = 'none';
        });
    }

    /**
     * Mostrar indicador de carga
     */
    showLoadingIndicator() {
        // Crear overlay de carga
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'authLoadingOverlay';
        loadingOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        loadingOverlay.style.cssText = `
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            backdrop-filter: blur(2px);
        `;
        
        loadingOverlay.innerHTML = `
            <div class="text-center text-white">
                <div class="spinner-border text-light mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <div>Actualizando interfaz...</div>
            </div>
        `;

        document.body.appendChild(loadingOverlay);
    }

    /**
     * Ocultar indicador de carga
     */
    hideLoadingIndicator() {
        const loadingOverlay = document.getElementById('authLoadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    /**
     * Mostrar mensaje de éxito
     */
    showSuccessMessage(message) {
        // Crear toast de éxito
        const toastHTML = `
            <div class="toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3" 
                 role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', toastHTML);

        // Mostrar el toast
        const toastElement = document.querySelector('.toast:last-child');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        // Limpiar el toast después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    /**
     * Reinicializar componentes de Bootstrap
     */
    reinitializeBootstrapComponents() {
        // Reinicializar tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Reinicializar dropdowns
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(dropdownToggleEl => {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    }

    /**
     * Actualizar después del logout
     */
    updateAfterLogout() {
        const navbarNav = document.querySelector('.navbar-nav:last-child');
        if (!navbarNav) return;

        // Restaurar navbar para usuario no autenticado
        const guestNavHTML = `
            <li class="nav-item">
                <button type="button" class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                    <i class="fas fa-sign-in-alt me-1"></i>
                    <span class="d-none d-sm-inline">Iniciar Sesión</span>
                </button>
            </li>
        `;

        navbarNav.innerHTML = guestNavHTML;
        this.reinitializeBootstrapComponents();
    }
}

// Crear instancia global
window.quickAuthUpdate = new QuickAuthUpdate();
