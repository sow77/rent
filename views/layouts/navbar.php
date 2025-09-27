<?php
// views/layouts/navbar.php
// Asegurar que I18n esté inicializado
I18n::init();
$currentLang = I18n::getCurrentLang();
$baseUrl = APP_URL;

// Inicializar $activePage si no está definida
if (!isset($activePage)) {
    $activePage = '';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $baseUrl; ?>">
            <i class="fas fa-car me-2"></i><?php echo APP_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'home' ? 'active' : ''; ?>" 
                       href="<?php echo $baseUrl; ?>">
                        <i class="fas fa-home me-1"></i><?php echo I18n::t('nav.home'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'vehicles' ? 'active' : ''; ?>" 
                       href="<?php echo $baseUrl; ?>/vehicles">
                        <i class="fas fa-car me-1"></i><?php echo I18n::t('nav.vehicles'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'boats' ? 'active' : ''; ?>" 
                       href="<?php echo $baseUrl; ?>/boats">
                        <i class="fas fa-ship me-1"></i><?php echo I18n::t('nav.boats'); ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activePage === 'transfers' ? 'active' : ''; ?>" 
                       href="<?php echo $baseUrl; ?>/transfers">
                        <i class="fas fa-shuttle-van me-1"></i><?php echo I18n::t('nav.transfers'); ?>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav align-items-center">
                <!-- Dropdown de idiomas simplificado -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fi fi-<?php echo $currentLang === 'en' ? 'gb' : $currentLang; ?>" style="font-size: 1.2em;"></i>
                        <span class="d-none d-sm-inline"><?php echo I18n::t('languages.' . $currentLang); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'es' ? ' active fw-bold' : ''; ?>" href="?lang=es">
                                <i class="fi fi-es"></i><span>Español</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'en' ? ' active fw-bold' : ''; ?>" href="?lang=en">
                                <i class="fi fi-gb"></i><span>English</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'fr' ? ' active fw-bold' : ''; ?>" href="?lang=fr">
                                <i class="fi fi-fr"></i><span>Français</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2<?php echo $currentLang === 'de' ? ' active fw-bold' : ''; ?>" href="?lang=de">
                                <i class="fi fi-de"></i><span>Deutsche</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Usuario/Login simplificado -->
                <?php if (!Auth::isAuthenticated()): ?>
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt me-1"></i>
                        <span class="d-none d-sm-inline"><?php echo I18n::t('auth.login'); ?></span>
                    </button>
                </li>
                <?php else: ?>
                <?php $currentUser = Auth::getCurrentUser(); ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <span class="d-none d-sm-inline"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/profile">
                                <i class="fas fa-user-cog me-2"></i><?php echo I18n::t('auth.profile'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $baseUrl; ?>/user/reservations">
                                <i class="fas fa-list me-2"></i>Mis Reservas
                            </a>
                        </li>
                        <?php if (Auth::isAdmin()): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo $baseUrl; ?>/admin">
                                <i class="fas fa-cog me-2"></i>Administración
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo $baseUrl; ?>/logout">
                                <i class="fas fa-sign-out-alt me-2"></i><?php echo I18n::t('auth.logout'); ?>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> 