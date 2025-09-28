<?php
require_once '../../config/config.php';
require_once '../../views/layouts/header.php';

$page_title = 'Locations';
echo getHeader($page_title, 'locations');
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Locations</h1>
        <a href="/locations/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Location
        </a>
    </div>

    <?php if ($flash = getFlashMessage()): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Coordinates</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($location = $locations->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($location['name']); ?></td>
                        <td><?php echo htmlspecialchars($location['address']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($location['latitude']); ?>,
                            <?php echo htmlspecialchars($location['longitude']); ?>
                        </td>
                        <td>
                            <a href="/locations/edit/<?php echo $location['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete-location" 
                                    data-id="<?php echo $location['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../../views/layouts/footer.php'; ?>