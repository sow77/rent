const APP = {
    init() {
        this.bindEvents();
        this.setupAjax();
        this.initializeComponents();
    },

    bindEvents() {
        // Navigation
        $(document).on('click', '[data-action]', this.handleAction.bind(this));
        
        // Forms
        $('form[data-ajax]').on('submit', this.handleFormSubmit.bind(this));
        
        // Language switching
        $('.language-select').on('change', this.handleLanguageChange.bind(this));
    },

    setupAjax() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: this.handleAjaxError.bind(this)
        });
    },

    initializeComponents() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Initialize date pickers
        $('.datepicker').flatpickr({
            minDate: 'today',
            dateFormat: 'Y-m-d'
        });
    },

    handleAction(e) {
        e.preventDefault();
        const action = $(e.currentTarget).data('action');
        const id = $(e.currentTarget).data('id');
        
        switch(action) {
            case 'reserve':
                this.handleReservation(id);
                break;
            case 'filter':
                this.handleFilter(e.currentTarget);
                break;
            case 'search':
                this.handleSearch(e.currentTarget);
                break;
        }
    },

    async handleFormSubmit(e) {
        e.preventDefault();
        const form = $(e.currentTarget);
        const url = form.attr('action');
        const method = form.attr('method') || 'POST';
        
        try {
            this.showLoading();
            const response = await $.ajax({
                url,
                method,
                data: form.serialize()
            });
            
            if (response.success) {
                this.showSuccess(response.message);
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
            } else {
                this.showError(response.message);
            }
        } catch (error) {
            this.showError('An error occurred. Please try again.');
        } finally {
            this.hideLoading();
        }
    },

    handleLanguageChange(e) {
        const lang = $(e.currentTarget).val();
        window.location.href = `?lang=${lang}`;
    },

    showLoading() {
        $('.loading-overlay').fadeIn();
    },

    hideLoading() {
        $('.loading-overlay').fadeOut();
    },

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    },

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }
};

$(document).ready(() => APP.init());

$(document).ready(function() {
    // Inicializar la aplicación
    initApp();
    
    // Configurar AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Manejar el formulario de búsqueda
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const serviceType = $('select[name="service_type"]').val();
        
        // Redirigir a la página correspondiente
        window.location.href = `/${serviceType}?${formData}`;
    });
    
    // Inicializar los tooltips de Bootstrap
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Manejar el cambio de idioma
    $('.language-selector .dropdown-item').on('click', function(e) {
        // No prevenir comportamiento por defecto para permitir que Bootstrap maneje el dropdown
        const lang = $(this).data('lang');
        window.location.href = `?lang=${lang}`;
    });
    
    // Animación de las tarjetas de servicio
    $('.service-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
    
    // Cargar más vehículos al hacer clic en "Ver más"
    $('.load-more').on('click', function() {
        const category = $(this).data('category');
        const offset = $(this).data('offset');
        
        $.get(`/api/vehicles/${category}`, { offset: offset })
            .done(function(response) {
                if (response.vehicles.length > 0) {
                    // Agregar los nuevos vehículos
                    response.vehicles.forEach(function(vehicle) {
                        const card = createVehicleCard(vehicle);
                        $(`#${category}-vehicles`).append(card);
                    });
                    
                    // Actualizar el offset
                    $('.load-more').data('offset', offset + response.vehicles.length);
                    
                    // Ocultar el botón si no hay más vehículos
                    if (response.vehicles.length < 3) {
                        $('.load-more').hide();
                    }
                } else {
                    $('.load-more').hide();
                }
            })
            .fail(function(error) {
                console.error('Error al cargar más vehículos:', error);
            });
    });
});

// Función para crear una tarjeta de vehículo
function createVehicleCard(vehicle) {
    return `
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="${vehicle.image}" class="card-img-top" alt="${vehicle.brand} ${vehicle.model}">
                <div class="card-body">
                    <h5 class="card-title">${vehicle.brand} ${vehicle.model}</h5>
                    <p class="card-text">${vehicle.description}</p>
                    <p class="card-text"><small class="text-muted">${vehicle.daily_rate}€/día</small></p>
                    <a href="/vehicles/${vehicle.id}" class="btn btn-primary">Ver detalles</a>
                </div>
            </div>
        </div>
    `;
}

// Función para inicializar la aplicación
function initApp() {
    // Verificar si hay mensajes de sesión
    if (typeof sessionMessages !== 'undefined') {
        showSessionMessages(sessionMessages);
    }
    
    // Inicializar el selector de fecha
    $('input[type="date"]').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true,
        startDate: 'today'
    });
}

// Función para mostrar mensajes de sesión
function showSessionMessages(messages) {
    if (messages.success) {
        showToast('success', messages.success);
    }
    if (messages.error) {
        showToast('error', messages.error);
    }
}

// Función para mostrar notificaciones toast
function showToast(type, message) {
    const toast = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    $('#toastContainer').append(toast);
    $('.toast').toast('show');
}