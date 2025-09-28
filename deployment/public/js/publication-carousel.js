/**
 * Sistema de carrusel para publicaciones
 */
class PublicationCarousel {
    constructor(element) {
        this.carousel = element;
        this.items = element.querySelectorAll('.carousel-item');
        this.indicators = element.querySelectorAll('.carousel-indicators button');
        this.currentIndex = 0;
        this.isTransitioning = false;
        
        this.init();
    }
    
    init() {
        // Solo inicializar si hay más de una imagen
        if (this.items.length <= 1) {
            return;
        }
        
        // Configurar controles
        this.setupControls();
        
        // Auto-play activado para mejorar la percepción de carrusel
        this.startAutoPlay(4000);
        
        // Soporte para gestos táctiles en móvil
        this.setupTouchSupport();
    }
    
    setupControls() {
        const prevBtn = this.carousel.querySelector('.carousel-control-prev');
        const nextBtn = this.carousel.querySelector('.carousel-control-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.prev();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.next();
            });
        }
        
        // Configurar indicadores
        this.indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', (e) => {
                e.preventDefault();
                this.goTo(index);
            });
        });
        
        // Pausar en hover
        this.carousel.addEventListener('mouseenter', () => {
            this.stopAutoPlay();
        });
        
        this.carousel.addEventListener('mouseleave', () => {
            this.startAutoPlay();
        });
    }
    
    setupTouchSupport() {
        let startX = 0;
        let endX = 0;
        
        this.carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
        }, { passive: true });
        
        this.carousel.addEventListener('touchmove', (e) => {
            endX = e.touches[0].clientX;
        }, { passive: true });
        
        this.carousel.addEventListener('touchend', () => {
            const diff = startX - endX;
            const threshold = 50;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
        }, { passive: true });
    }
    
    goTo(index) {
        if (this.isTransitioning || index === this.currentIndex) {
            return;
        }
        
        this.isTransitioning = true;
        
        // Actualizar elementos activos
        this.items[this.currentIndex].classList.remove('active');
        this.indicators[this.currentIndex].classList.remove('active');
        
        this.currentIndex = index;
        
        this.items[this.currentIndex].classList.add('active');
        this.indicators[this.currentIndex].classList.add('active');
        
        // Permitir nueva transición después del delay
        setTimeout(() => {
            this.isTransitioning = false;
        }, 600);
    }
    
    next() {
        const nextIndex = (this.currentIndex + 1) % this.items.length;
        this.goTo(nextIndex);
    }
    
    prev() {
        const prevIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
        this.goTo(prevIndex);
    }
    
    startAutoPlay(interval = 5000) {
        this.stopAutoPlay();
        if (this.items.length > 1) {
            this.autoPlayInterval = setInterval(() => {
                this.next();
            }, interval);
        }
    }
    
    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }
}

/**
 * Crear HTML del carrusel (función interna)
 */
function createCarouselHTML(images, altText = '') {
    if (!Array.isArray(images) || images.length === 0) {
        return createSingleImageHTML('', altText);
    }
    
    if (images.length === 1) {
        return createSingleImageHTML(images[0], altText);
    }
    
    const carouselId = `carousel-${Math.random().toString(36).substr(2, 9)}`;
    
    let carouselHTML = `<div class="carousel-inner">`;
    
    // Crear items del carrusel
    images.forEach((image, index) => {
        const isActive = index === 0 ? 'active' : '';
        carouselHTML += `
            <div class="carousel-item ${isActive}">
                <img src="${escapeHtml(image)}" alt="${escapeHtml(altText)}" loading="lazy">
            </div>`;
    });
    
    carouselHTML += `
        </div>
        
        <!-- Controles -->
        <button class="carousel-control-prev" type="button">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
        
        <!-- Indicadores -->
        <div class="carousel-indicators">`;
    
    images.forEach((_, index) => {
        const isActive = index === 0 ? 'active' : '';
        carouselHTML += `
            <button type="button" class="${isActive}" aria-label="Imagen ${index + 1}"></button>`;
    });
    
    carouselHTML += `
        </div>
        
        <!-- Contador -->
        <div class="image-counter">
            <span class="current">1</span>/<span class="total">${images.length}</span>
        </div>`;
    
    return carouselHTML;
}

/**
 * Crear carrusel a partir de un array de imágenes (función pública)
 */
function createCarousel(images, altText = '') {
    if (!Array.isArray(images) || images.length === 0) {
        return createSingleImage('', altText);
    }
    
    if (images.length === 1) {
        return createSingleImage(images[0], altText);
    }
    
    const carouselId = `carousel-${Math.random().toString(36).substr(2, 9)}`;
    
    let carouselHTML = `
        <div id="${carouselId}" class="publication-carousel">
            <div class="carousel-inner">`;
    
    // Crear items del carrusel
    images.forEach((image, index) => {
        const isActive = index === 0 ? 'active' : '';
        carouselHTML += `
            <div class="carousel-item ${isActive}">
                <img src="${escapeHtml(image)}" alt="${escapeHtml(altText)}" loading="lazy">
            </div>`;
    });
    
    carouselHTML += `
            </div>
            
            <!-- Controles -->
            <button class="carousel-control-prev" type="button">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Anterior</span>
            </button>
            <button class="carousel-control-next" type="button">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Siguiente</span>
            </button>
            
            <!-- Indicadores -->
            <div class="carousel-indicators">`;
    
    images.forEach((_, index) => {
        const isActive = index === 0 ? 'active' : '';
        carouselHTML += `
            <button type="button" class="${isActive}" aria-label="Imagen ${index + 1}"></button>`;
    });
    
    carouselHTML += `
            </div>
            
            <!-- Contador -->
            <div class="image-counter">
                <span class="current">1</span>/<span class="total">${images.length}</span>
            </div>
        </div>`;
    
    return carouselHTML;
}

/**
 * Crear HTML de imagen única (función interna)
 */
function createSingleImageHTML(imageSrc, altText = '') {
    const defaultImage = `${APP_URL}/public/images/no-image.png`;
    const src = imageSrc || defaultImage;
    
    return `
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="${escapeHtml(src)}" alt="${escapeHtml(altText)}" loading="lazy">
            </div>
        </div>`;
}

/**
 * Crear imagen única (función pública)
 */
function createSingleImage(imageSrc, altText = '') {
    const defaultImage = `${APP_URL}/public/images/no-image.png`;
    const src = imageSrc || defaultImage;
    
    return `
        <div class="publication-carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="${escapeHtml(src)}" alt="${escapeHtml(altText)}" loading="lazy">
                </div>
            </div>
        </div>`;
}

/**
 * Procesar imágenes desde string o array
 */
function processImages(imageData) {
    if (!imageData) {
        return [];
    }
    
    if (typeof imageData === 'string') {
        // Si es una cadena que parece JSON, intentar parsear
        if (imageData.startsWith('[') || imageData.startsWith('{')) {
            try {
                const parsed = JSON.parse(imageData);
                return Array.isArray(parsed) ? parsed : [imageData];
            } catch (e) {
                return [imageData];
            }
        }
        return [imageData];
    }
    
    if (Array.isArray(imageData)) {
        return imageData;
    }
    
    return [];
}

/**
 * Escapar HTML para prevenir XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

/**
 * Inicializar todos los carruseles en la página
 */
function initializeCarousels() {
    const carousels = document.querySelectorAll('.publication-carousel');
    console.log('Inicializando carruseles:', carousels.length);
    
    carousels.forEach((carousel, index) => {
        // Si tiene data-images, construir dinámicamente el carrusel si hace falta
        try {
            const dataImagesAttr = carousel.getAttribute('data-images');
            if (dataImagesAttr) {
                const imgs = Array.isArray(dataImagesAttr) ? dataImagesAttr : JSON.parse(dataImagesAttr);
                if (Array.isArray(imgs) && imgs.length > 1) {
                    const inner = carousel.querySelector('.carousel-inner') || (function(){
                        const div = document.createElement('div');
                        div.className = 'carousel-inner';
                        carousel.appendChild(div);
                        return div;
                    })();
                    // Reconstruir items si solo hay uno
                    const existingItems = inner.querySelectorAll('.carousel-item');
                    if (existingItems.length < imgs.length) {
                        inner.innerHTML = imgs.map((src, i) => `
                            <div class="carousel-item ${i === 0 ? 'active' : ''}">
                                <img src="${src}" alt="${carousel.getAttribute('data-alt') || ''}" loading="lazy">
                            </div>
                        `).join('');
                        // Controles si no existen
                        if (!carousel.querySelector('.carousel-control-prev')) {
                            carousel.insertAdjacentHTML('beforeend', `
                                <button class="carousel-control-prev" type="button">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            `);
                        }
                        // Indicadores
                        if (!carousel.querySelector('.carousel-indicators')) {
                            const indicators = document.createElement('div');
                            indicators.className = 'carousel-indicators';
                            indicators.innerHTML = imgs.map((_, i) => `<button type="button" class="${i === 0 ? 'active' : ''}" aria-label="Imagen ${i+1}"></button>`).join('');
                            carousel.appendChild(indicators);
                        }
                    }
                }
            }
        } catch (e) { /* noop */ }

        const items = carousel.querySelectorAll('.carousel-item');
        const hasControls = carousel.querySelector('.carousel-control-prev') && carousel.querySelector('.carousel-control-next');
        const dataImages = carousel.getAttribute('data-images');
        
        console.log(`Carrusel ${index}:`, {
            id: carousel.id,
            items: items.length,
            hasControls: hasControls,
            dataImages: dataImages,
            firstItem: items[0] ? items[0].innerHTML.substring(0, 100) + '...' : 'No items'
        });
        
        // Marcar imágenes pequeñas para no escalarlas por encima de su tamaño natural
        const skipNoUpscale = !!carousel.closest('#vehiclesContainer');
        items.forEach(item => {
            const img = item.querySelector('img');
            if (!img) return;
            if (skipNoUpscale) {
                // no marcar no-upscale en vehículos para evitar imagen pequeña
                return;
            }
            if (img.naturalWidth && img.naturalHeight) {
                const container = carousel.getBoundingClientRect();
                // si la imagen es claramente más pequeña que el contenedor, evitar upscale
                if (img.naturalWidth < container.width * 0.7 || img.naturalHeight < container.height * 0.7) {
                    img.classList.add('no-upscale');
                }
            } else {
                img.addEventListener('load', () => {
                    const container = carousel.getBoundingClientRect();
                    if (img.naturalWidth < container.width * 0.7 || img.naturalHeight < container.height * 0.7) {
                        img.classList.add('no-upscale');
                    }
                }, { once: true });
            }
        });

        // Inicializar funcionalidad del carrusel directamente
        new PublicationCarousel(carousel);
        
        // Actualizar contador si existe
        updateImageCounter(carousel);
    });
}

/**
 * Actualizar contador de imágenes
 */
function updateImageCounter(carousel) {
    const counter = carousel.querySelector('.image-counter .current');
    const indicators = carousel.querySelectorAll('.carousel-indicators button');
    
    if (counter && indicators.length > 0) {
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                counter.textContent = index + 1;
            });
        });
        
        // Actualizar en navegación con controles
        const prevBtn = carousel.querySelector('.carousel-control-prev');
        const nextBtn = carousel.querySelector('.carousel-control-next');
        
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', () => {
                setTimeout(() => {
                    const activeIndex = Array.from(indicators).findIndex(ind => ind.classList.contains('active'));
                    counter.textContent = activeIndex + 1;
                }, 100);
            });
            
            nextBtn.addEventListener('click', () => {
                setTimeout(() => {
                    const activeIndex = Array.from(indicators).findIndex(ind => ind.classList.contains('active'));
                    counter.textContent = activeIndex + 1;
                }, 100);
            });
        }
    }
}

/**
 * Configurar grid responsivo
 */
function setupResponsiveGrid() {
    // row-cols maneja automáticamente la distribución
    console.log('Grid configurado con row-cols de Bootstrap');
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    initializeCarousels();
    setupResponsiveGrid();
});

// Reinicializar después de cargar contenido dinámico
window.reinitializeCarousels = initializeCarousels;
window.setupResponsiveGrid = setupResponsiveGrid;
