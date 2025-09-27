<!DOCTYPE html>
<html lang="<?php echo I18n::getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo APP_URL; ?>/public/css/admin.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php require_once __DIR__ . '/../admin/partials/sidebar.php'; ?>

        <!-- Page Content -->
        <div class="content">
            <!-- Navbar -->
            <?php require_once __DIR__ . '/../admin/partials/navbar.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid">
                <?php echo $content ?? ''; ?>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        const currentLang = '<?php echo I18n::getCurrentLang(); ?>';
        const currentSection = '<?php echo $currentSection ?? ''; ?>';
    </script>
    <script src="<?php echo APP_URL; ?>/public/js/admin.js"></script>
</body>
</html> 