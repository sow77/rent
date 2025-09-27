// public/js/navbar.js - Funcionalidad específica de la navbar

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Inicializando funcionalidad de navbar');
    
    initNavbarDropdowns();
    initNavbarResponsive();
    initNavbarAnimations();
});

/**
 * Inicializar dropdowns de la navbar
 */
function initNavbarDropdowns() {
    // Configurar dropdown del usuario
    const userDropdown = document.getElementById('userDropdown');
    const userDropdownMenu = userDropdown?.nextElementSibling;
    
    if (userDropdown && userDropdownMenu) {
        // Asegurar que Bootstrap dropdown esté inicializado
        if (typeof bootstrap !== 'undefined') {
            const dropdown = new bootstrap.Dropdown(userDropdown, {
                autoClose: true,
                boundary: 'viewport'
            });
        }
        
        // Agregar eventos personalizados
        userDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Dropdown del usuario clickeado');
        });
        
        // Agregar hover para mejor UX
        userDropdown.addEventListener('mouseenter', function() {
            if (window.innerWidth >= 992) { // Solo en desktop
                const dropdown = bootstrap.Dropdown.getInstance(userDropdown);
                if (dropdown) {
                    dropdown.show();
                }
            }
        });
        
        userDropdownMenu.addEventListener('mouseleave', function() {
            if (window.innerWidth >= 992) { // Solo en desktop
                const dropdown = bootstrap.Dropdown.getInstance(userDropdown);
                if (dropdown) {
                    dropdown.hide();
                }
            }
        });
    }
    
    // Configurar dropdown de idiomas
    const languageDropdown = document.getElementById('languageDropdown');
    const languageDropdownMenu = languageDropdown?.nextElementSibling;
    
    if (languageDropdown && languageDropdownMenu) {
        // Asegurar que Bootstrap dropdown esté inicializado
        if (typeof bootstrap !== 'undefined') {
            const dropdown = new bootstrap.Dropdown(languageDropdown, {
                autoClose: true,
                boundary: 'viewport'
            });
        }
    }
}

/**
 * Inicializar funcionalidad responsive de la navbar
 */
function initNavbarResponsive() {
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navbarCollapse = document.querySelector('.navbar-collapse');
    
    if (navbarToggler && navbarCollapse) {
        // Cerrar navbar al hacer click en un enlace (en móvil) - EXCLUYENDO dropdowns
        const navLinks = navbarCollapse.querySelectorAll('.nav-link:not([data-bs-toggle="dropdown"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
        
        // Cerrar dropdowns al cambiar tamaño de ventana
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                // En desktop, cerrar navbar colapsado
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse && bsCollapse._isShown()) {
                    bsCollapse.hide();
                }
            }
        });
    }
}

/**
 * Inicializar animaciones de la navbar
 */
function initNavbarAnimations() {
    const navbar = document.querySelector('.navbar');
    
    if (navbar) {
        // Efecto de scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
        
        // Eliminada la animación de entrada que causaba parpadeo
    }
}

/**
 * Función para cerrar todos los dropdowns
 */
function closeAllDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown-menu.show');
    dropdowns.forEach(dropdown => {
        const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
        if (dropdownInstance) {
            dropdownInstance.hide();
        }
    });
}

/**
 * Función para mostrar notificación en la navbar
 */
function showNavbarNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `navbar-notification navbar-notification-${type}`;
    notification.innerHTML = `
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        </div>
    `;
    
    // Insertar después de la navbar
    const navbar = document.querySelector('.navbar');
    navbar.parentNode.insertBefore(notification, navbar.nextSibling);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Exportar funciones para uso global
window.NavbarUtils = {
    closeAllDropdowns,
    showNavbarNotification
}; 