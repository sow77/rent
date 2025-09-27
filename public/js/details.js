// Asegurarnos de que DetailsManager no esté ya definidoao 
if (typeof DetailsManager === 'undefined') {
    window.DetailsManager = {
        init() {
            console.log('Inicializando DetailsManager');
            this.bindEvents();
        },

        bindEvents() {
            console.log('Vinculando eventos');
            document.addEventListener('click', (e) => {
                const button = e.target.closest('.view-details');
                if (!button) return;

                // Solo prevenir comportamiento por defecto si es un botón de detalles
                e.preventDefault();
                e.stopPropagation();
                
                const type = button.getAttribute('data-type');
                const id = button.getAttribute('data-id');
                
                console.log('Botón clickeado:', { type, id });
                
                if (!type || !id) {
                    console.error('Faltan datos requeridos:', { type, id });
                    return;
                }
                
                this.loadDetails(type, id);
            });

            // Agregar manejador para el cierre del modal (solo modales de detalles)
            document.addEventListener('hidden.bs.modal', (e) => {
                const modal = e.target;
                // Limpiar el contenido si es modal de detalles
                if (modal.id === 'vehiclesDetailsModal' || modal.id === 'boatsDetailsModal' || modal.id === 'transfersDetailsModal' || modal.id === 'vehiclesDetailsModalPage') {
                    const modalBody = modal.querySelector('.modal-body');
                    if (modalBody) modalBody.innerHTML = '';
                }
                // Asegurar limpieza de backdrop y estado del body
                setTimeout(() => {
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    if (!document.querySelector('.modal.show')) {
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        document.body.style.paddingLeft = '';
                    }
                }, 50);
            });
            
            // Agregar manejador para el cierre con escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    const openModal = document.querySelector('.modal.show');
                    if (openModal) {
                        const bsModal = bootstrap.Modal.getInstance(openModal);
                        if (bsModal) {
                            bsModal.hide();
                        }
                    }
                }
            });
        },

        async loadDetails(type, id) {
            console.log('Cargando detalles:', { type, id });
            try {
                const response = await fetch(`${APP_URL}/${type}/details?id=${id}`);
                console.log('Respuesta del servidor:', response);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Datos recibidos:', data);
                
                if (data.success) {
                    this.showDetails(type, data.data);
                } else {
                    console.error('Error en la respuesta:', data.error);
                    alert('Error al cargar los detalles: ' + (data.error || 'Error desconocido'));
                }
            } catch (error) {
                console.error('Error al cargar detalles:', error);
                alert('Error al cargar los detalles: ' + error.message);
            }
        },

        showDetails(type, data) {
            console.log('Mostrando detalles:', { type, data });
            
            // Cerrar modales existentes primero
            this.closeAllModals();
            
            // Buscar el modal correcto según la página actual
            let modalId = `${type}DetailsModal`;
            let modal = document.getElementById(modalId);
            
            // Si no existe, buscar el modal específico de la página
            if (!modal) {
                if (type === 'vehicles') {
                    modalId = 'vehiclesDetailsModalPage';
                    modal = document.getElementById(modalId);
                }
            }
            
            // Si aún no existe, usar el modal genérico
            if (!modal) {
                modalId = 'vehiclesDetailsModal';
                modal = document.getElementById(modalId);
            }
            
            if (!modal) {
                console.error('Modal no encontrado para tipo:', type);
                console.error('Modales disponibles:', document.querySelectorAll('.modal').length);
                return;
            }

            const modalBody = modal.querySelector('.modal-body');
            if (!modalBody) {
                console.error('Modal body no encontrado');
                return;
            }

            this.renderDetails(type, data, modalBody);
            
            // Esperar un poco para que se cierren los modales anteriores
            setTimeout(() => {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
            }, 100);
        },

        closeAllModals() {
            // Cerrar todos los modales abiertos
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            });
            
            // Limpiar backdrops
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => backdrop.remove());
            
            // Restaurar estilos del body
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            document.body.style.paddingLeft = '';
        },

        renderDetails(type, data, container) {
            // Procesar imágenes del vehículo
            let images = [];
            if (data.images) {
                if (Array.isArray(data.images)) {
                    images = data.images;
                } else if (typeof data.images === 'string') {
                    try {
                        const parsedImages = JSON.parse(data.images);
                        if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                            images = parsedImages;
                        }
                    } catch (e) {
                        console.warn('Error parsing images in modal:', e);
                    }
                }
            }
            if (images.length === 0 && data.image && data.image !== '[' && data.image.trim() !== '') {
                images = [data.image];
            }

            // Generar HTML del carrusel para el modal
            const carouselItems = images.map((image, index) => `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}" 
                         alt="${data.brand || data.name} ${data.model || ''}" 
                         loading="lazy" 
                         style="height: 400px; width: 100%;">
                </div>
            `).join('');

            const indicators = images.length > 1 ? `
                <div class="carousel-indicators">
                    ${images.map((_, index) => `
                        <button type="button"
                                data-bs-target="#modal-carousel-${data.id}"
                                data-bs-slide-to="${index}"
                                class="${index === 0 ? 'active' : ''}"
                                aria-current="${index === 0 ? 'true' : 'false'}"
                                aria-label="Slide ${index + 1}"></button>
                    `).join('')}
                </div>
            ` : '';

            const controls = images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#modal-carousel-${data.id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#modal-carousel-${data.id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            ` : '';

            // Compactar características
            let featuresHtml = '';
            if (data.features && Array.isArray(data.features)) {
                featuresHtml = `
                    <div class="features-section mb-3">
                        <h5 class="mb-2"><i class="fas fa-list me-2"></i>Características</h5>
                        <div class="d-flex flex-wrap gap-2">
                            ${data.features.map(feature => `
                                <span class="badge bg-primary">${feature}</span>
                            `).join('')}
                        </div>
                    </div>`;
            }

            // Datos clave en una sola fila horizontal
            let keyDataHtml = '';
            if (type === 'vehicles') {
                keyDataHtml = `
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-tag me-2"></i>Categoría:</strong> 
                            <span class="badge bg-info">${data.category || 'N/A'}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-calendar me-2"></i>Año:</strong> 
                            <span class="badge bg-secondary">${data.year || 'N/A'}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-users me-2"></i>Número de Plazas:</strong> 
                            <span class="badge bg-info">${data.capacity || 5} personas</span>
                        </div>
                    </div>`;
            } else if (type === 'boats') {
                keyDataHtml = `
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-tag me-2"></i>Tipo:</strong> 
                            <span class="badge bg-info">${data.type || 'N/A'}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-users me-2"></i>Capacidad:</strong> 
                            <span class="badge bg-info">${data.capacity || '-'} personas</span>
                        </div>
                    </div>`;
            } else if (type === 'transfers') {
                keyDataHtml = `
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-tag me-2"></i>Tipo:</strong> 
                            <span class="badge bg-info">${data.type || 'N/A'}</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-users me-2"></i>Capacidad:</strong> 
                            <span class="badge bg-info">${data.capacity || '0'} personas</span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong><i class="fas fa-clock me-2"></i>Duración:</strong> 
                            <span class="badge bg-secondary">${data.duration || 'No especificada'}</span>
                        </div>
                    </div>`;
            }

            // Título y subtítulo
            let title = '';
            let subtitle = '';
            let price = '';
            if (type === 'vehicles') {
                title = `${data.brand} ${data.model}`;
                subtitle = data.description || '';
                price = `<strong class="fw-bold fs-4" style="color: white;"><i class="fas fa-euro-sign me-2"></i>${parseFloat(data.daily_rate).toFixed(2)}</strong> <small style="color: white;">/día</small>`;
            } else if (type === 'boats') {
                title = data.name;
                subtitle = data.description || '';
                price = `<strong class="fw-bold fs-4" style="color: white;"><i class="fas fa-euro-sign me-2"></i>${parseFloat(data.daily_rate).toFixed(2)}</strong> <small style="color: white;">/día</small>`;
            } else if (type === 'transfers') {
                title = data.name;
                subtitle = data.description || '';
                price = `<strong class="fw-bold fs-4" style="color: white;"><i class="fas fa-euro-sign me-2"></i>${parseFloat(data.price).toFixed(2)}</strong>`;
            }

            // Renderizado moderno con carrusel
            container.innerHTML = `
                <div class="details-content">
                    <div class="row">
                        <div class="col-md-6">
                            <div id="modal-carousel-${data.id}" class="carousel slide" data-bs-ride="carousel">
                                ${indicators}
                                <div class="carousel-inner">
                                    ${carouselItems}
                                </div>
                                ${controls}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="details-info">
                                <h3 class="mb-2">${title}</h3>
                                <div class="mb-2 text-muted">${subtitle}</div>
                                ${featuresHtml}
                                ${keyDataHtml}
                                <div class="details-price">${price}</div>
                                <button class="btn btn-primary mt-2" onclick="showReservationForm('${data.id}', '${type.slice(0, -1)}', ${JSON.stringify(data).replace(/"/g, '&quot;')})">RESERVAR AHORA</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    };

    // Inicializar cuando el DOM esté listo
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM cargado, inicializando DetailsManager');
        window.DetailsManager.init();
    });
    
    // También inicializar inmediatamente si el DOM ya está listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM cargado (readyState), inicializando DetailsManager');
            window.DetailsManager.init();
        });
    } else {
        console.log('DOM ya está listo, inicializando DetailsManager inmediatamente');
        window.DetailsManager.init();
    }
} 