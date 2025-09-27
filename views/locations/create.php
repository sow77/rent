<?php
require_once '../../config/config.php';
require_once '../../views/layouts/header.php';

$page_title = 'Add Location';
echo getHeader($page_title, 'locations');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Add New Location</h2>

                    <form action="/locations/create" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" name="latitude" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" name="longitude" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="/locations" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../views/layouts/footer.php'; ?>