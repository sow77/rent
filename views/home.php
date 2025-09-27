<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/i18n.php';
require_once __DIR__ . '/layouts/header.php';

$pageTitle = I18n::t('home.title');
$activePage = 'home';

// Extraer las variables de $data
$featuredVehicles = $data['featuredVehicles'] ?? [];
$featuredBoats = $data['featuredBoats'] ?? [];
$featuredTransfers = $data['featuredTransfers'] ?? [];
?>

<main>
    <!-- Hero Section -->
    <section class="hero-section vehicle-hero">
        <div class="overlay"></div>
        <div class="text-overlay"></div>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8 text-center text-white">
                    <h1 class="display-4 fw-bold mb-4"><?php echo I18n::t('hero.title'); ?></h1>
                    <p class="lead mb-5"><?php echo I18n::t('hero.subtitle'); ?></p>
                    
                    <div class="search-form">
                        <form id="searchForm" action="#" method="GET">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <select class="form-select" name="service_type" id="serviceTypeSelect" required>
                                        <option value="vehicles"><?php echo I18n::t('nav.vehicles'); ?></option>
                                        <option value="boats"><?php echo I18n::t('nav.boats'); ?></option>
                                        <option value="transfers"><?php echo I18n::t('nav.transfers'); ?></option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select" name="category" id="serviceCategorySelect">
                                        <option value="">Todas las categorías</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input type="date" class="form-control" name="date" required>
                                </div>
                                <div class="col-md-12 col-lg-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i><?php echo I18n::t('common.search'); ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Services -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Vehicles -->
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <img id="featuredVehiclesImg" src="https://images.unsplash.com/photo-1485291571150-772bcfc10da5?auto=format&fit=crop&q=80" 
                             class="card-img-top" alt="Luxury vehicles" data-service="vehicles">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo I18n::t('nav.vehicles'); ?></h3>
                            <p class="card-text"><?php echo I18n::t('services.vehicles.description'); ?></p>
                            <a href="<?php echo APP_URL; ?>/vehicles" class="btn btn-primary"><?php echo I18n::t('services.view_more'); ?></a>
                        </div>
                    </div>
                </div>
                
                <!-- Boats -->
                <div class="col-md-4">
                    <div class="service-card card h-100">
                        <img id="featuredBoatsImg" src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&q=80" 
                             class="card-img-top" alt="Luxury boats" data-service="boats">
                        <div class="card-body text-center">
                            <h3 class="card-title"><?php echo I18n::t('nav.boats'); ?></h3>
                            <p class="card-text"><?php echo I18n::t('services.boats.description'); ?></p>
                            <a href="<?php echo APP_URL; ?>/boats" class="btn btn-primary"><?php echo I18n::t('services.view_more'); ?></a>
                        </div>
                    </div>
                </div>

                <!-- Transfers -->
                    <div class="col-md-4">
                        <div class="service-card card h-100">
                        <img id="featuredTransfersImg" src="https://images.unsplash.com/photo-1562618817-92b2a2c78399?auto=format&fit=crop&q=80" 
                             class="card-img-top" alt="Premium transfers" data-service="transfers">
                                <div class="card-body text-center">
                            <h3 class="card-title"><?php echo I18n::t('nav.transfers'); ?></h3>
                            <p class="card-text"><?php echo I18n::t('services.transfers.description'); ?></p>
                            <a href="<?php echo APP_URL; ?>/transfers" class="btn btn-primary"><?php echo I18n::t('services.view_more'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Vehicles -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title">Vehículos Destacados</h2>
            <?php $chunks = array_chunk($featuredVehicles, 3); ?>
            <?php foreach ($chunks as $chunk): ?>
                <?php $len = count($chunk); $rowClass = $len < 3 ? ('row-few ' . ($len === 1 ? 'row-one' : 'row-two')) : ''; ?>
                <div class="row g-4 <?php echo $rowClass; ?>">
                    <?php
                    if ($len === 1) { $colClass = 'col-12'; }
                    elseif ($len === 2) { $colClass = 'col-12 col-md-6'; }
                    else { $colClass = 'col-12 col-md-6 col-lg-4'; }
                    ?>
                    <?php foreach ($chunk as $vehicle): ?>
                        <?php
                        $images = [];
                        if (!empty($vehicle['images'])) {
                            $decoded = json_decode($vehicle['images'], true);
                            if (is_array($decoded) && !empty($decoded)) {
                                $images = $decoded;
                            }
                        }
                        if (empty($images)) { $images = [ $vehicle['image'] ?? '' ]; }
                        $altText = htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']);
                        $carouselId = 'feat-veh-' . $vehicle['id'];
                        ?>
                        <div class="<?php echo $colClass; ?>">
                            <div class="card h-100">
                                <div id="<?php echo $carouselId; ?>" class="publication-carousel carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                                    <div class="carousel-inner">
                                        <?php foreach ($images as $idx => $imgUrl): ?>
                                            <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                                                <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo $altText; ?>" loading="lazy">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (count($images) > 1): ?>
                                        <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h5>
                                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($vehicle['description'], 0, 100)) . '...'; ?></p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="price">€<?php echo number_format($vehicle['daily_rate'], 2); ?>/día</span>
                                        </div>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary view-details" 
                                                    data-type="vehicles" 
                                                    data-id="<?php echo $vehicle['id']; ?>">
                                                <?php echo I18n::t('vehicles.view_details'); ?>
                                            </button>
                                            <button class="btn btn-success" onclick="showReservationForm('<?php echo $vehicle['id']; ?>','vehicle', <?php echo htmlspecialchars(json_encode($vehicle)); ?>)">
                                                <i class="fas fa-calendar-plus me-1"></i>Reservar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Boats -->
     <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4"><?php echo I18n::t('boats.title'); ?></h2>
            <?php $chunks = array_chunk($featuredBoats, 3); ?>
            <?php foreach ($chunks as $chunk): ?>
                <?php $len = count($chunk); $rowClass = $len < 3 ? ('row-few ' . ($len === 1 ? 'row-one' : 'row-two')) : ''; ?>
                <div class="row g-4 <?php echo $rowClass; ?>">
                    <?php
                    if ($len === 1) { $colClass = 'col-12'; }
                    elseif ($len === 2) { $colClass = 'col-12 col-md-6'; }
                    else { $colClass = 'col-12 col-md-6 col-lg-4'; }
                    ?>
                    <?php foreach ($chunk as $boat): ?>
                        <div class="<?php echo $colClass; ?>">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($boat->getImage()); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($boat->getName()); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($boat->getName()); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($boat->getDescription()); ?></p>
                                    <p class="card-text">
                                        <strong><?php echo I18n::t('boats.daily_rate'); ?>:</strong> 
                                        €<?php echo number_format($boat->getDailyRate(), 2); ?>
                                    </p>
                                    <button class="btn btn-primary view-details" 
                                            data-type="boats" 
                                            data-id="<?php echo $boat->getId(); ?>">
                                        <?php echo I18n::t('boats.view_details'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Transfers -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-4"><?php echo I18n::t('transfers.title'); ?></h2>
            <?php $chunks = array_chunk($featuredTransfers, 3); ?>
            <?php foreach ($chunks as $chunk): ?>
                <?php $len = count($chunk); $rowClass = $len < 3 ? ('row-few ' . ($len === 1 ? 'row-one' : 'row-two')) : ''; ?>
                <div class="row g-4 <?php echo $rowClass; ?>">
                    <?php
                    if ($len === 1) { $colClass = 'col-12'; }
                    elseif ($len === 2) { $colClass = 'col-12 col-md-6'; }
                    else { $colClass = 'col-12 col-md-6 col-lg-4'; }
                    ?>
                    <?php foreach ($chunk as $transfer): ?>
                        <div class="<?php echo $colClass; ?>">
                            <div class="card h-100">
                                <img src="<?php echo htmlspecialchars($transfer->getImage()); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($transfer->getName()); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($transfer->getName()); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($transfer->getDescription()); ?></p>
                                    <p class="card-text">
                                        <strong><?php echo I18n::t('transfers.daily_rate'); ?>:</strong> 
                                        €<?php echo number_format($transfer->getPrice(), 2); ?>
                                    </p>
                                    <button class="btn btn-primary view-details" 
                                            data-type="transfers" 
                                            data-id="<?php echo $transfer->getId(); ?>">
                                        <?php echo I18n::t('transfers.view_details'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>

<script>
// Conectar el buscador de portada con las secciones reales
document.addEventListener('DOMContentLoaded', function() {
    const APP_URL = '<?php echo APP_URL; ?>';
    const form = document.getElementById('searchForm');
    const serviceSelect = document.getElementById('serviceTypeSelect');
    const categorySelect = document.getElementById('serviceCategorySelect');

    // Mapeo de servicio a categoría del API
    const SERVICE_TO_API = { vehicles: 'vehicle', boats: 'boat', transfers: 'transfer' };

    async function loadCategories(service) {
        const apiCategory = SERVICE_TO_API[service] || 'vehicle';
        const firstLabel = service === 'vehicles' ? 'Todas las categorías' : 'Todos los tipos';
        const url = `${APP_URL}/admin/types/api?category=${apiCategory}`;

        const setFallback = () => {
            const fallback = service === 'vehicles'
                ? ['económico', 'familiar', 'lujo', 'deportivo']
                : (service === 'boats'
                    ? ['yate', 'velero', 'lancha', 'catamaran']
                    : ['limusina', 'minivan', 'suv', 'autobús', 'taxi', 'van']);
            categorySelect.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = firstLabel;
            categorySelect.appendChild(first);
            fallback.forEach(name => {
                const o = document.createElement('option');
                o.value = name;
                o.textContent = name.charAt(0).toUpperCase() + name.slice(1);
                categorySelect.appendChild(o);
            });
        };

        try {
            const resp = await fetch(url);
            const data = await resp.json();
            const list = (data && data.success && Array.isArray(data.data)) ? data.data : [];
            categorySelect.innerHTML = '';
            const first = document.createElement('option');
            first.value = '';
            first.textContent = firstLabel;
            categorySelect.appendChild(first);
            if (list.length) {
                list.forEach(t => {
                    const name = (t.name || '').toString();
                    const o = document.createElement('option');
                    o.value = name.toLowerCase();
                    o.textContent = name;
                    categorySelect.appendChild(o);
                });
            } else {
                setFallback();
            }
        } catch (e) {
            setFallback();
        }
    }

    // Inicializar categorías dinámicamente
    loadCategories(serviceSelect.value);
    serviceSelect.addEventListener('change', () => loadCategories(serviceSelect.value));

    // Rotador de imágenes para Featured Services
    (function initFeaturedRotators(){
        const targets = [
            { id: 'featuredVehiclesImg', service: 'vehicles', api: `${APP_URL}/vehicles/search` },
            { id: 'featuredBoatsImg', service: 'boats', api: `${APP_URL}/boats/search` },
            { id: 'featuredTransfersImg', service: 'transfers', api: `${APP_URL}/transfers/search` },
        ];

        const intervalMs = 7000;

        targets.forEach(cfg => {
            const imgEl = document.getElementById(cfg.id);
            if (!imgEl) return;

            const storageKey = `featured_imgs_${cfg.service}`;
            const ttlMs = 10 * 60 * 1000;
            const now = Date.now();

        const setSrcSafe = (url) => {
            if (!url || typeof url !== 'string') return;
            imgEl.style.willChange = 'opacity, transform';
            imgEl.style.transition = 'opacity 0.6s ease, transform 6s ease-out';
            imgEl.style.opacity = '0';
            imgEl.style.transform = 'scale(1.025)';
                const preload = new Image();
                preload.onload = () => {
                    imgEl.src = url;
                requestAnimationFrame(() => {
                    imgEl.style.opacity = '1';
                    imgEl.style.transform = 'scale(1)';
                });
                };
                preload.onerror = () => {/* mantener actual */};
                preload.src = url;
            };

            const rotate = (list) => {
                if (!Array.isArray(list) || list.length === 0) return;
                let idx = 0;
                setInterval(() => {
                    idx = (idx + 1) % list.length;
                    setSrcSafe(list[idx]);
                }, intervalMs);
            };

            const fetchAndCache = () => {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 2500);
                fetch(cfg.api, { signal: controller.signal })
                    .then(r => r.json())
                    .then(data => {
                        clearTimeout(timeout);
                        let items = [];
                        if (data && Array.isArray(data.data)) items = data.data;
                        else if (Array.isArray(data)) items = data;
                        const images = items.map(item => {
                            // admitir item.image o primer elemento de item.images
                            if (item.images) {
                                try {
                                    const parsed = Array.isArray(item.images) ? item.images : JSON.parse(item.images);
                                    if (Array.isArray(parsed) && parsed.length) return parsed[0];
                                } catch(e) {}
                            }
                            return item.image || '';
                        }).filter(Boolean);
                        if (images.length) {
                            try { localStorage.setItem(storageKey, JSON.stringify({ ts: now, data: images })); } catch(e) {}
                            rotate(images);
                        }
                    })
                    .catch(() => {
                        clearTimeout(timeout);
                    });
            };

            try {
                const raw = localStorage.getItem(storageKey);
                if (raw) {
                    const cached = JSON.parse(raw);
                    if (cached && Array.isArray(cached.data) && cached.data.length && (now - cached.ts) < ttlMs) {
                        rotate(cached.data);
                    }
                }
            } catch(e) {}

            fetchAndCache();
        });
    })();
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const service = form.service_type.value; // 'vehicles' | 'boats' | 'transfers'
        const date = form.date.value;
        let base = APP_URL;
        if (service === 'vehicles') base += '/vehicles/search';
        else if (service === 'boats') base += '/boats/search';
        else base += '/transfers/search';
        const params = new URLSearchParams();
        if (date) params.set('date', date);
        const category = (categorySelect.value || '').trim();
        if (category) params.set('category', category);
        const url = params.toString() ? `${base}?${params.toString()}` : base;
        window.location.href = url;
    });
});
</script>