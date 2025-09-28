<?php
require_once '../../config/config.php';
require_once '../../views/layouts/header.php';

$page_title = 'Edit Location';
echo getHeader($page_title, 'locations');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Edit Location</h2>

                    <form action="/locations/edit/<?php echo $location['id']; ?>" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($location['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" 
                                   value="<?php echo htmlspecialchars($location['address']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" name="latitude" 
                                       value="<?php echo htmlspecialchars($location['latitude']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" name="longitude" 
                                       value="<?php echo htmlspecialchars($location['longitude']); ?>" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/locations" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../views/layouts/footer.php'; ?>