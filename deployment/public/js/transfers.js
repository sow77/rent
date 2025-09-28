const TransferManager = {
    init() {
        this.bindEvents();
        this.buildDynamicCategories().then(() => this.loadTransfers());
    },

    bindEvents() {
        $(document).on('click', '.category-filter', (e) => this.handleFilter(e));
        $(document).on('click', '.book-transfer', (e) => this.handleBooking(e));
        $('#transferSearch').on('input', (e) => this.handleSearch(e));
        $('#transferSearchBtn').on('click', (e) => this.handleSearch(e));
        $(document).on('click', '.view-details', (e) => this.handleViewDetails(e));
    },

    getTypesCached(serviceKey, apiUrl, fallbackNames) {
        const storageKey = `types_cache_${serviceKey}`;
        const ttlMs = 10 * 60 * 1000; // 10 minutos
        const now = Date.now();
        try {
            const raw = localStorage.getItem(storageKey);
            if (raw) {
                const cached = JSON.parse(raw);
                if (cached && Array.isArray(cached.data) && cached.data.length && (now - cached.ts) < ttlMs) {
                    return Promise.resolve(cached.data);
                }
            }
        } catch (e) {}
        return new Promise(resolve => {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 2500);
            fetch(apiUrl, { signal: controller.signal })
                .then(r => r.json())
                .then(data => {
                    clearTimeout(timeout);
                    const list = (data && data.success && Array.isArray(data.data)) ? data.data.map(t => t.name) : [];
                    if (list.length) {
                        try { localStorage.setItem(storageKey, JSON.stringify({ ts: now, data: list })); } catch (e) {}
                        resolve(list);
                    } else {
                        try {
                            const raw2 = localStorage.getItem(storageKey);
                            if (raw2) {
                                const cached2 = JSON.parse(raw2);
                                if (cached2 && Array.isArray(cached2.data) && cached2.data.length) return resolve(cached2.data);
                            }
                        } catch (e) {}
                        resolve(fallbackNames);
                    }
                })
                .catch(() => {
                    clearTimeout(timeout);
                    try {
                        const raw2 = localStorage.getItem(storageKey);
                        if (raw2) {
                            const cached2 = JSON.parse(raw2);
                            if (cached2 && Array.isArray(cached2.data) && cached2.data.length) return resolve(cached2.data);
                        }
                    } catch (e) {}
                    resolve(fallbackNames);
                });
        });
    },

    async buildDynamicCategories() {
        try {
            const names = await this.getTypesCached('transfer', `${APP_URL}/admin/types/api?category=transfer`, ['limusina','minivan','suv','bus']);
            const group = document.getElementById('transferCategoryGroup');
            const frag = document.createDocumentFragment();
            names.forEach(name => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary category-filter mb-2';
                btn.dataset.category = name;
                btn.textContent = name;
                frag.appendChild(btn);
            });
            group.appendChild(frag);
        } catch (e) {
            console.warn('No se pudieron cargar categorías dinámicas de transfer');
        }
    },

    showLoading() {
        // Modificado para evitar cambios de layout - similar a boats
        $('#transfersContainer').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    },

    hideLoading() {
        // El contenido se actualizará con los datos
    },

    showError(message) {
        if (!$('#errorAlert').length) {
            $('body').append(`
                <div id="errorAlert" class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                     role="alert" style="z-index: 9999; display: none;">
                    <span id="errorMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
        }
        $('#errorMessage').text(message);
        $('#errorAlert').fadeIn().delay(3000).fadeOut();
    },

    async loadTransfers(filters = {}) {
        try {
            this.showLoading();
            console.log('Cargando traslados con filtros:', filters);
            
            const response = await $.get(`${APP_URL}/transfers/search`, filters);
            console.log('Respuesta del servidor:', response);
            
            // Validar y procesar la respuesta
            let transfers = [];
            if (response && typeof response === 'object') {
                // Si la respuesta tiene una propiedad data que es un array
                if (response.data && Array.isArray(response.data)) {
                    transfers = response.data;
                }
                // Si la respuesta es directamente un array
                else if (Array.isArray(response)) {
                    transfers = response;
                }
                // Si la respuesta es un objeto con propiedades que son transfers
                else if (typeof response === 'object') {
                    transfers = Object.values(response);
                }
            }
            
            console.log('Traslados procesados:', transfers);
            this.renderTransfers(transfers);
        } catch (error) {
            console.error('Error al cargar los traslados:', error);
            this.showError('Error al cargar los traslados');
        } finally {
            this.hideLoading();
        }
    },

    handleFilter(e) {
        e.preventDefault();
        const category = $(e.currentTarget).data('category');
        
        // Actualizar botones
        $('.category-filter').removeClass('active');
        $(e.currentTarget).addClass('active');
        
        // Si es 'all', no enviar categoría
        const filters = category === 'all' ? {} : { category };
        
        // Cargar traslados con el filtro
        this.loadTransfers(filters);
    },

    handleSearch(e) {
        e.preventDefault();
        const searchTerm = $(e.currentTarget).val();
        this.loadTransfers({ term: searchTerm });
    },

    handleViewDetails(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = e.currentTarget;
        const type = button.getAttribute('data-type');
        const id = button.getAttribute('data-id');
        
        console.log('TransferManager - Botón clickeado:', { type, id });
        
        if (!type || !id) {
            console.error('Faltan datos requeridos:', { type, id });
            return;
        }
        
        // Usar SIEMPRE DetailsManager global
        if (typeof DetailsManager !== 'undefined' && DetailsManager.loadDetails) {
            console.log('Usando DetailsManager global');
            DetailsManager.loadDetails(type, id);
        } else {
            console.error('DetailsManager no está disponible');
            alert('No se pudo cargar el sistema de detalles. DetailsManager no está disponible.');
        }
    },

    renderTransfers(transfers) {
        console.log('Renderizando traslados:', transfers);
        const container = $('#transfersContainer');
        
        if (!transfers || !transfers.length) {
            container.html('<div class="col-12 text-center"><h3>No hay traslados disponibles</h3></div>');
            return;
        }

        // Agrupar transfers de 3 en 3
        const chunks = [];
        for (let i = 0; i < transfers.length; i += 3) {
            chunks.push(transfers.slice(i, i + 3));
        }

        const chunksHtml = chunks.map(chunk => {
            const len = chunk.length;
            let colClass;
            
            if (len === 1) {
                colClass = 'col-12';
            } else if (len === 2) {
                colClass = 'col-12 col-md-6 col-lg-6';
            } else {
                colClass = 'col-12 col-md-6 col-lg-4';
            }

            const chunkHtml = chunk.map(transfer => {
            // Procesar imágenes del transfer
            let images = [];
            if (transfer.images) {
                try {
                    const parsedImages = JSON.parse(transfer.images);
                    if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                        images = parsedImages;
                    }
                } catch (e) {
                    console.warn('Error parsing transfer images:', e);
                }
            }
            if (images.length === 0 && transfer.image && transfer.image !== '[' && transfer.image.trim() !== '') {
                images = [transfer.image];
            }
            
            // Usar la primera imagen disponible
            const firstImage = images.length > 0 ? images[0] : '';
            
            // Generar HTML del carrusel con todas las imágenes
            const carouselItems = images.map((image, index) => `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}"
                         alt="${transfer.name || 'Transfer'}"
                         loading="lazy"
                         style="height: 200px; object-fit: cover; width: 100%;">
                </div>
            `).join('');
            
            // Generar indicadores si hay múltiples imágenes
            const indicators = images.length > 1 ? `
                <div class="carousel-indicators">
                    ${images.map((_, index) => `
                        <button type="button" 
                                data-bs-target="#carousel-${transfer.id}" 
                                data-bs-slide-to="${index}" 
                                class="${index === 0 ? 'active' : ''}" 
                                aria-current="${index === 0 ? 'true' : 'false'}" 
                                aria-label="Slide ${index + 1}"></button>
                    `).join('')}
                </div>
            ` : '';
            
            // Generar controles si hay múltiples imágenes
            const controls = images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${transfer.id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-${transfer.id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            ` : '';
            
                return `
                <div class="${colClass} d-flex">
                    <div class="card h-100 w-100 shadow-sm">
                    <div id="carousel-${transfer.id}" class="carousel slide publication-carousel" 
                         data-images='${JSON.stringify(images)}'
                         data-alt="${transfer.name || 'Transfer'}">
                        ${indicators}
                        <div class="carousel-inner">
                            ${carouselItems}
                        </div>
                        ${controls}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">${transfer.name || 'Sin nombre'}</h5>
                        <p class="card-text text-muted flex-grow-1">${transfer.description || 'Sin descripción'}</p>
                        <div class="mt-auto">
                            <p class="card-text mb-3">
                                <strong>Precio diario:</strong> 
                                <span class="text-primary fw-bold">€${transfer.price || '0.00'}</span>
                            </p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary view-details" 
                                        data-type="transfers" 
                                        data-id="${transfer.id}">
                                    <i class="fas fa-eye me-1"></i>Ver detalles
                                </button>
                                <button class="btn btn-success" 
                                        onclick="showReservationForm('${transfer.id}', 'transfer', ${JSON.stringify(transfer).replace(/"/g, '&quot;')})">
                                    <i class="fas fa-calendar-plus me-1"></i>Reservar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `;
        }).join('');

            const rowClass = len < 3 ? `row-few ${len === 1 ? 'row-one' : 'row-two'}` : '';
            return `<div class=\"row g-4 ${rowClass}\">${chunkHtml}</div>`;
        }).join('');

        container.html(chunksHtml);
        
        // Reinicializar carruseles después de cargar contenido dinámico
        if (window.reinitializeCarousels) {
            window.reinitializeCarousels();
        }
    }
};

$(document).ready(() => TransferManager.init());