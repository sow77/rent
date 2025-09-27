$(document).ready(function() {
    const vehiclesContainer = $('#vehiclesContainer');
    const searchInput = $('#vehicleSearch');
    const categoryGroup = $('#vehicleCategoryGroup');
    let currentCategory = 'all';

    // Cache ligero de tipos con TTL y timeout de red
    function getTypesCached(serviceKey, apiUrl, fallbackNames) {
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
        } catch (e) { /* noop */ }

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
    }

    // Cargar categorías dinámicas desde API y construir botones
    getTypesCached('vehicle', `${APP_URL}/admin/types/api?category=vehicle`, ['económico','familiar','lujo','deportivo'])
        .then(list => {
            // Para vehículos, limitar a categorías soportadas por backend
            const allowed = new Set(['económico','familiar','lujo','deportivo']);
            const names = list.filter(n => allowed.has(n.toLowerCase ? n.toLowerCase() : n));
            const frag = document.createDocumentFragment();
            names.forEach(name => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-outline-primary category-filter mb-2';
                btn.dataset.category = name;
                btn.textContent = name;
                frag.appendChild(btn);
            });
            categoryGroup[0].appendChild(frag);
            bindCategoryButtons();
        });

    // Función para cargar vehículos
    function loadVehicles(category = 'all', searchTerm = '') {
        console.log('Cargando vehículos:', { category, searchTerm });
        $.ajax({
            url: `${APP_URL}/vehicles/search`,
            method: 'GET',
            data: {
                category: category,
                term: searchTerm
            },
            success: function(response) {
                console.log('Respuesta de búsqueda:', response);
                if (response.data && Array.isArray(response.data)) {
                    renderVehicles(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar vehículos:', error);
            }
        });
    }

    // Función para renderizar vehículos
    function renderVehicles(vehicles) {
        console.log('Renderizando vehículos:', vehicles);
        if (!vehicles || vehicles.length === 0) {
            vehiclesContainer.html('<div class="alert alert-info text-center"><p>No se encontraron vehículos</p></div>');
            return;
        }

        // Agrupar vehículos de 3 en 3
        const chunks = [];
        for (let i = 0; i < vehicles.length; i += 3) {
            chunks.push(vehicles.slice(i, i + 3));
        }

        const chunksHtml = chunks.map(chunk => {
            const len = chunk.length; // cuántos items tiene esta fila (1,2 o 3)
            let colClass;
            
            // Elegir la clase de columna según el número de items en esta fila
            if (len === 1) {
                // 1 item -> ocupa toda la fila
                colClass = 'col-12';
            } else if (len === 2) {
                // 2 items -> 50/50 en desktop
                colClass = 'col-12 col-md-6 col-lg-6';
            } else {
                // 3 items -> tercios en large
                colClass = 'col-12 col-md-6 col-lg-4';
            }

            const chunkHtml = chunk.map(vehicle => {
            // Procesar imágenes del vehículo
            let images = [];
            if (vehicle.images) {
                console.log('Imágenes para', vehicle.brand, vehicle.model, ':', vehicle.images);
                if (Array.isArray(vehicle.images)) {
                    images = vehicle.images;
                } else if (typeof vehicle.images === 'string') {
                    try {
                        const parsedImages = JSON.parse(vehicle.images);
                        if (Array.isArray(parsedImages) && parsedImages.length > 0) {
                            images = parsedImages;
                        }
                    } catch (e) {
                        console.warn('Error parsing vehicle images:', e);
                    }
                }
            }
            if (images.length === 0 && vehicle.image && vehicle.image !== '[' && vehicle.image.trim() !== '') {
                images = [vehicle.image];
            }
            
            // Usar la primera imagen disponible
            const firstImage = images.length > 0 ? images[0] : '';
            
            console.log('Imágenes finales para', vehicle.brand, vehicle.model, ':', images);
            
            // Generar HTML del carrusel con todas las imágenes
            const carouselItems = images.map((image, index) => `
                <div class="carousel-item ${index === 0 ? 'active' : ''}">
                    <img src="${image}"
                         alt="${vehicle.brand} ${vehicle.model}"
                         loading="lazy">
                </div>
            `).join('');
            
            
            // Generar indicadores si hay múltiples imágenes
            const indicators = images.length > 1 ? `
                <div class="carousel-indicators">
                    ${images.map((_, index) => `
                        <button type="button" 
                                data-bs-target="#carousel-${vehicle.id}" 
                                data-bs-slide-to="${index}" 
                                class="${index === 0 ? 'active' : ''}" 
                                aria-current="${index === 0 ? 'true' : 'false'}" 
                                aria-label="Slide ${index + 1}"></button>
                    `).join('')}
                </div>
            ` : '';
            
            // Generar controles si hay múltiples imágenes
            const controls = images.length > 1 ? `
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-${vehicle.id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-${vehicle.id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            ` : '';
            
                return `
                <div class="${colClass} d-flex">
                    <div class="card h-100 w-100 shadow-sm">
                    <div id="carousel-${vehicle.id}" class="carousel slide publication-carousel" data-bs-ride="carousel" data-bs-interval="4000" 
                         data-images='${JSON.stringify(images)}'
                         data-alt="${vehicle.brand} ${vehicle.model}">
                        ${indicators}
                        <div class="carousel-inner">
                            ${carouselItems}
                        </div>
                        ${controls}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold">${vehicle.brand} ${vehicle.model}</h5>
                        <p class="card-text text-muted flex-grow-1">${vehicle.description || ''}</p>
                        <div class="mt-auto">
                            <p class="card-text mb-3">
                                <strong>Precio diario:</strong> 
                                <span class="text-primary fw-bold">€${parseFloat(vehicle.daily_rate).toFixed(2)}</span>
                            </p>
                            <div class="d-grid gap-2">
                        <button class="btn btn-primary view-details" 
                                data-type="vehicles" 
                                data-id="${vehicle.id}">
                                    <i class="fas fa-eye me-1"></i>Ver detalles
                                </button>
                                <button class="btn btn-success" 
                                        onclick="showReservationForm('${vehicle.id}', 'vehicle', ${JSON.stringify(vehicle).replace(/"/g, '&quot;')})">
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
            return `<div class="row g-4 ${rowClass}">${chunkHtml}</div>`;
        }).join('');

        vehiclesContainer.html(chunksHtml);
        
        // Reinicializar carruseles después de cargar contenido dinámico
        if (window.reinitializeCarousels) {
            console.log('Reinicializando carruseles...');
            window.reinitializeCarousels();
        } else {
            console.log('reinitializeCarousels no está disponible');
        }
    }

    // Manejar búsqueda
    searchInput.on('input', function() {
        const searchTerm = $(this).val().trim();
        loadVehicles(currentCategory, searchTerm);
    });

    // Manejar botón de búsqueda
    $('#vehicleSearchBtn').on('click', function() {
        const searchTerm = searchInput.val().trim();
        loadVehicles(currentCategory, searchTerm);
    });

    function bindCategoryButtons() {
        const categoryButtons = $('.category-filter');
        categoryButtons.off('click').on('click', function() {
            const category = $(this).data('category');
            console.log('Filtrando por categoría:', category);
            categoryButtons.removeClass('active');
            $(this).addClass('active');
            currentCategory = category;
            loadVehicles(category, searchInput.val().trim());
        });
    }

    // El CSS se encarga de la responsividad automáticamente

    // Cargar vehículos iniciales
    loadVehicles();
}); 