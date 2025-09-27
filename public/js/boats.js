const BoatManager = {
    init() {
        this.bindEvents();
        this.buildDynamicCategories().then(() => this.loadBoats());
    },

    bindEvents() {
        $(document).on('click', '.category-filter', (e) => this.handleFilter(e));
        $('#boatSearch').on('input', (e) => this.handleSearch(e));
    },

    getTypesCached(serviceKey, apiUrl, fallbackNames) {
        const storageKey = `types_cache_${serviceKey}`;
        const ttlMs = 10 * 60 * 1000;
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
            const names = await this.getTypesCached('boat', `${APP_URL}/admin/types/api?category=boat`, ['yate','velero','lancha','catamarán']);
            const group = document.getElementById('boatCategoryGroup');
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
            console.warn('No se pudieron cargar categorías dinámicas de boat');
        }
    },

    showLoading() {
        $('#boatsContainer').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    },

    hideLoading() {
        // El contenido se actualizará con los datos
    },

    showError(message) {
        $('#boatsContainer').html(`<div class="alert alert-danger">${message}</div>`);
    },

    async loadBoats(filters = {}) {
        try {
            this.showLoading();
            console.log('Cargando barcos con filtros:', filters);
            
            const response = await $.ajax({
                url: `${APP_URL}/boats/search`,
                method: 'GET',
                data: filters,
                dataType: 'json'
            });
            
            console.log('Respuesta del servidor:', response);
            
            if (response && response.data) {
            this.renderBoats(response.data);
            } else {
                this.showError('No hay barcos disponibles');
            }
        } catch (error) {
            console.error('Error al cargar barcos:', error);
            this.showError('Error al cargar los barcos');
        } finally {
            this.hideLoading();
        }
    },

    handleFilter(e) {
        e.preventDefault();
        const category = $(e.currentTarget).data('category');
        console.log('Filtrando por categoría:', category);
        
        $('.category-filter').removeClass('active');
        $(e.currentTarget).addClass('active');
        
        this.loadBoats({ category });
    },

    handleSearch(e) {
        e.preventDefault();
        const searchTerm = $(e.currentTarget).val().trim();
        console.log('Buscando con término:', searchTerm);
        
        this.loadBoats({ term: searchTerm });
    },

    renderBoats(boats) {
        console.log('Renderizando barcos:', boats);
        const container = $('#boatsContainer');
        
        if (!boats || !boats.length) {
            container.html('<div class="col-12 text-center"><h3>No hay barcos disponibles</h3></div>');
            return;
        }

        // Agrupar barcos de 3 en 3
        const chunks = [];
        for (let i = 0; i < boats.length; i += 3) {
            chunks.push(boats.slice(i, i + 3));
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

            const chunkHtml = chunk.map(boat => {
            // Procesar imágenes del barco
            let images = [];
            if (boat.images) {
                try {
                    const parsedImages = JSON.parse(boat.images);
                    if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                        images = parsedImages;
                    }
                } catch (e) {
                    console.warn('Error parsing boat images:', e);
                }
            }
            if (images.length === 0 && boat.image && boat.image !== '[' && boat.image.trim() !== '') {
                images = [boat.image];
            }
            
            // Usar la primera imagen disponible
            const firstImage = images.length > 0 ? images[0] : '';
            
            // Generar HTML del carrusel con todas las imágenes
            const carouselItems = images.map((image, index) => `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}"
                         alt="${boat.name}"
                         loading="lazy"
                         style="height: 200px; object-fit: cover; width: 100%;">
                </div>
            `).join('');
            
            // Generar indicadores si hay múltiples imágenes
            const indicators = images.length > 1 ? `
                <div class="carousel-indicators">
                    ${images.map((_, index) => `
                        <button type="button" 
                                data-bs-target="#carousel-${boat.id}" 
                                data-bs-slide-to="${index}" 
                                class="${index === 0 ? 'active' : ''}" 
                                aria-current="${index === 0 ? 'true' : 'false'}" 
                                aria-label="Slide ${index + 1}"></button>
                    `).join('')}
                </div>
            ` : '';
            
            // Generar controles si hay múltiples imágenes
            const controls = images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${boat.id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-${boat.id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            ` : '';
            
                return `
                <div class="${colClass} d-flex">
                    <div class="card h-100 w-100 shadow-sm">
                    <div id="carousel-${boat.id}" class="carousel slide publication-carousel" 
                         data-images='${JSON.stringify(images)}'
                         data-alt="${boat.name}">
                        ${indicators}
                        <div class="carousel-inner">
                            ${carouselItems}
                        </div>
                        ${controls}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">${boat.name}</h5>
                        <p class="card-text text-muted flex-grow-1">${boat.description || ''}</p>
                        <div class="mt-auto">
                            <p class="card-text mb-3">
                                <strong>Precio diario:</strong> 
                                <span class="text-primary fw-bold">€${parseFloat(boat.daily_rate).toFixed(2)}</span>
                            </p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary view-details" 
                                        data-type="boats" 
                                        data-id="${boat.id}">
                                    <i class="fas fa-eye me-1"></i>Ver detalles
                                </button>
                                <button class="btn btn-success" 
                                        onclick="showReservationForm('${boat.id}', 'boat', ${JSON.stringify(boat).replace(/"/g, '&quot;')})">
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

$(document).ready(() => BoatManager.init());