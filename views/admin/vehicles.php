<?php
// views/admin/vehicles.php - Vista de gestión de vehículos
if (!isset($vehicles)) {
    $vehicles = [];
}

$pageTitle = 'Gestión de Vehículos';
$currentPage = 'vehicles';

// Capturar el contenido en un buffer para el layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Gestión de Vehículos</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
        <i class="fas fa-plus me-2"></i>Agregar Vehículo
    </button>
</div>

<!-- Search and Filter -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Búsqueda y Filtros</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar vehículos..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" id="categoryFilter">
                    <option value="">Todas las categorías</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" id="statusFilter">
                    <option value="">Todos los estados</option>
                    <option value="disponible">Disponible</option>
                    <option value="mantenimiento">Mantenimiento</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <button class="btn btn-outline-primary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Vehicles Table -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-car me-2"></i>Lista de Vehículos</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($vehicles)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-car fa-3x mb-3"></i><br>
            <h5>No hay vehículos registrados</h5>
            <p class="text-muted">Comienza agregando tu primer vehículo</p>
        </div>
        <?php else: ?>
        <!-- Desktop Table -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Imagen</th>
                            <th>Vehículo</th>
                            <th style="width: 120px;">Categoría</th>
                            <th style="width: 100px;">Año</th>
                            <th style="width: 120px;">Precio/Día</th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($vehicle['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($vehicle['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 60px; height: 40px; font-size: 10px; border-radius: 4px;">
                                        <i class="fas fa-car"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></strong>
                                    <br>
                                    <small class="text-muted">ID: #<?php echo htmlspecialchars($vehicle['id']); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($vehicle['category'])); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted"><?php echo htmlspecialchars($vehicle['year']); ?></span>
                            </td>
                            <td class="text-center">
                                <strong class="text-success">€<?php echo number_format($vehicle['daily_rate'], 2); ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $vehicle['available'] ? 'success' : 'warning'; ?>">
                                    <i class="fas fa-<?php echo $vehicle['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                    <?php echo $vehicle['available'] ? 'Disponible' : 'Mantenimiento'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editVehicle('<?php echo $vehicle['id']; ?>')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteVehicle('<?php echo $vehicle['id']; ?>')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="d-lg-none">
            <?php foreach ($vehicles as $vehicle): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if (!empty($vehicle['image'])): ?>
                                <img src="<?php echo htmlspecialchars($vehicle['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?>" 
                                     class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 60px; font-size: 16px; border-radius: 8px;">
                                    <i class="fas fa-car"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></h6>
                            <small class="text-muted">ID: #<?php echo htmlspecialchars($vehicle['id']); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editVehicle('<?php echo $vehicle['id']; ?>')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteVehicle('<?php echo $vehicle['id']; ?>')">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Categoría:</small>
                            <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($vehicle['category'])); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Año:</small>
                            <span><?php echo htmlspecialchars($vehicle['year']); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Precio/Día:</small>
                            <span class="text-success fw-bold">€<?php echo number_format($vehicle['daily_rate'], 2); ?></span>
                    </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Estado:</small>
                            <span class="badge bg-<?php echo $vehicle['available'] ? 'success' : 'warning'; ?>">
                                <i class="fas fa-<?php echo $vehicle['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                <?php echo $vehicle['available'] ? 'Disponible' : 'Mantenimiento'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addVehicleModalLabel"><i class="fas fa-car me-2"></i>Agregar Vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addVehicleForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-tag me-1"></i>Marca</label>
                            <input type="text" class="form-control" name="brand" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-car me-1"></i>Modelo</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-list me-1"></i>Categoría</label>
                            <div class="input-group">
                                <select class="form-select" name="category" id="vehicleCategorySelect" required>
                                    <option value="">Seleccionar categoría</option>
                            </select>
                                <button class="btn btn-outline-secondary" type="button" onclick="showNewTypeModal('vehicle')">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-outline-danger" type="button" onclick="showManageTypesModalVehicle()" title="Gestionar tipos">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-calendar me-1"></i>Año</label>
                            <input type="number" class="form-control" name="year" min="1900" max="2030" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-euro-sign me-1"></i>Precio por Día</label>
                            <input type="number" class="form-control" name="daily_rate" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-users me-1"></i>Número de Plazas</label>
                            <input type="number" class="form-control" name="capacity" min="1" max="12" value="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                            <select class="form-select" name="available" required>
                                <option value="1">Disponible</option>
                                <option value="0">En Mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Ubicación</label>
                            <div class="input-group">
                                <select class="form-select" name="location_id" id="locationSelect" required>
                                    <option value="">Seleccionar ubicación</option>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" onclick="showNewLocationModal()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-list me-1"></i>Características
                                <span id="featureCounter" class="badge bg-secondary ms-2">0</span>
                            </label>
                            <div class="features-grid">
                                <div class="row g-2" id="featuresContainer">
                                    <!-- Las características se cargarán dinámicamente desde la base de datos -->
                                </div>
                            </div>
                            <input type="hidden" name="features" id="selectedFeatures" value="[]">
                            <small class="text-muted">Haz clic en las características que tenga el vehículo</small>
                            <div class="mt-2 d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showNewFeatureModal()">
                                    <i class="fas fa-plus me-1"></i>Agregar Nueva Característica
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="clearAllFeatures()">
                                    <i class="fas fa-times me-1"></i>Limpiar Todas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="selectCommonFeatures()">
                                    <i class="fas fa-star me-1"></i>Seleccionar Comunes
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showManageFeaturesModalVehicle()">
                                    <i class="fas fa-cog me-1"></i>Gestionar Características
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-images me-1"></i>Imágenes del Vehículo</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="image_files" accept="image/*" id="vehicleImageFiles" multiple>
                                <button class="btn btn-outline-secondary" type="button" onclick="previewVehicleImages()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <input type="hidden" name="images" id="vehicleImageUrls" value="[]">
                            <small class="text-muted">Puedes seleccionar múltiples imágenes (máximo 10)</small>
                            <div id="vehicleImagePreviews" class="mt-2 row g-2">
                                <!-- Las previsualizaciones se cargarán dinámicamente -->
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-align-left me-1"></i>Descripción</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Descripción del vehículo..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveVehicle()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva ubicación -->
<div class="modal fade" id="newLocationModal" tabindex="-1" aria-labelledby="newLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newLocationModalLabel"><i class="fas fa-map-marker-alt me-2"></i>Nueva Ubicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newLocationForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la ubicación</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <textarea class="form-control" name="address" rows="2" placeholder="Calle, número, ciudad..." required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Latitud</label>
                            <input type="number" class="form-control" name="latitude" step="0.000001" placeholder="40.416800">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitud</label>
                            <input type="number" class="form-control" name="longitude" step="0.000001" placeholder="-3.703800">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createNewLocation()">Crear Ubicación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para nueva característica -->
<div class="modal fade" id="newFeatureModal" tabindex="-1" aria-labelledby="newFeatureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newFeatureModalLabel">
                    <i class="fas fa-plus me-2"></i>Nueva Característica
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newFeatureForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la característica</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="category" id="featureCategorySelect" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icono (clase FontAwesome)</label>
                        <input type="text" class="form-control" name="icon" value="fas fa-check" placeholder="fas fa-check">
                        <small class="text-muted">Ejemplo: fas fa-car, fas fa-star, etc.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="createNewFeature()">Crear Característica</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gestionar Tipos (Vehículos) -->
<div class="modal fade" id="manageVehicleTypesModal" tabindex="-1" aria-labelledby="manageVehicleTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageVehicleTypesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Tipos (Vehículos)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="vehicleTypesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedVehicleTypes()">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
            </div>
        </div>
    </div>
    </div>

<!-- Modal: Gestionar Características (Vehículos) -->
<div class="modal fade" id="manageVehicleFeaturesModal" tabindex="-1" aria-labelledby="manageVehicleFeaturesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageVehicleFeaturesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Características (Vehículos)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="vehicleFeaturesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedVehicleFeatures()">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
            </div>
        </div>
    </div>
    </div>

<?php
// Obtener el contenido del buffer
$content = ob_get_clean();

// Incluir el layout de admin
include 'views/admin/layout.php';
?>

<script>
// Variable global para la URL de la aplicación
const APP_URL = '<?php echo APP_URL; ?>';

function editVehicle(id) {
    // Obtener datos del vehículo
    fetch(`${APP_URL}/admin/vehicles/api/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al cargar los datos del vehículo');
                return;
            }
            const vehicle = data.data;
            
            // Llenar el formulario con los datos del vehículo
            const form = document.getElementById('addVehicleForm');
            form.brand.value = vehicle.brand;
            form.model.value = vehicle.model;
            form.category.value = vehicle.category;
            form.year.value = vehicle.year;
            form.daily_rate.value = vehicle.daily_rate;
            form.available.value = vehicle.available;
            form.location_id.value = vehicle.location_id || '';
            form.description.value = vehicle.description || '';
            
            // Manejar imágenes múltiples
            let images = [];
            if (vehicle.images) {
                try {
                    images = JSON.parse(vehicle.images);
                } catch (e) {
                    console.error('Error al parsear imágenes:', e);
                    // Fallback a imagen única si existe
                    if (vehicle.image) {
                        images = [vehicle.image];
                    }
                }
            } else if (vehicle.image) {
                images = [vehicle.image];
            }
            
            // Actualizar el campo de imágenes
            document.getElementById('vehicleImageUrls').value = JSON.stringify(images);
            
            // Mostrar previsualizaciones de imágenes
            const previewsContainer = document.getElementById('vehicleImagePreviews');
            previewsContainer.innerHTML = '';
            images.forEach((imageUrl, index) => {
                addVehicleImageToPreview(imageUrl, index);
            });
            
            // Cargar características existentes
            let features = [];
            try {
                if (vehicle.features && vehicle.features !== '[]') {
                    features = JSON.parse(vehicle.features);
                }
            } catch (e) {
                console.error('Error al parsear características:', e);
            }
            
            document.getElementById('selectedFeatures').value = JSON.stringify(features);
            updateFeatureButtons(features);
            
            // Cambiar el título del modal
            document.querySelector('#addVehicleModal .modal-title').innerHTML = '<i class="fas fa-car me-2"></i>Editar Vehículo';
            // Cambiar el botón de guardar
            const saveButton = document.querySelector('#addVehicleModal .btn-primary');
            saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            saveButton.onclick = () => saveVehicle(id);
            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('addVehicleModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del vehículo');
        });
}

function deleteVehicle(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este vehículo?')) {
        fetch(`${APP_URL}/admin/vehicles/api/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error al eliminar el vehículo');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el vehículo');
        });
    }
}

function saveVehicle(id = null) {
    const form = document.getElementById('addVehicleForm');
    
    // Validar campos requeridos
    if (!form.brand.value || !form.model.value || !form.category.value || !form.year.value) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    // Preparar datos del formulario
    const formData = {
        brand: form.brand.value,
        model: form.model.value,
        category: form.category.value,
        year: form.year.value,
        daily_rate: form.daily_rate.value,
        available: form.available.value,
        location_id: form.location_id.value,
        images: document.getElementById('vehicleImageUrls').value, // Usar las URLs de las imágenes subidas
        description: form.description.value,
        features: document.getElementById('selectedFeatures').value // Usar las características seleccionadas
    };
    
    console.log('Enviando datos del vehículo:', formData);
    console.log('URLs de imágenes:', document.getElementById('vehicleImageUrls').value);
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${APP_URL}/admin/vehicles/api/${id}` : `${APP_URL}/admin/vehicles/api`;
    
    console.log('Enviando a URL:', url);
    console.log('Método:', method);
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status, response.statusText);
        return response.json();
    })
    .then(data => {
        console.log('Datos de respuesta:', data);
        if (data.success) {
            alert('Vehículo guardado exitosamente');
            // Cerrar modal
            const modal = document.getElementById('addVehicleModal');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            // Recargar solo si fue exitoso
            location.reload();
        } else {
            alert('Error al guardar: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error del servidor: ' + error.message);
    });
}

// Funciones para manejo de múltiples archivos
function handleVehicleImageChange(event) {
    const files = Array.from(event.target.files);
    if (files.length > 10) {
        alert('Máximo 10 imágenes permitidas');
        return;
    }
    
    // Limpiar previsualizaciones anteriores
    document.getElementById('vehicleImagePreviews').innerHTML = '';
    document.getElementById('vehicleImageUrls').value = '[]';
    
    // Subir cada archivo
    files.forEach((file, index) => {
        uploadVehicleImage(file, index);
    });
}

function uploadVehicleImage(file, index) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'vehicle');
    
    fetch(`${APP_URL}/api/file-upload.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addVehicleImageToPreview(data.url, index);
            addVehicleImageToUrls(data.url);
        } else {
            alert(`Error al subir imagen ${index + 1}: ${data.message}`);
        }
    })
    .catch(error => {
        alert(`Error al subir imagen ${index + 1}: ${error.message}`);
    });
}

function addVehicleImageToPreview(url, index) {
    const previewsContainer = document.getElementById('vehicleImagePreviews');
    const previewHtml = `
        <div class="col-md-3 col-sm-4 col-6" id="preview-${index}">
            <div class="position-relative">
                <img src="${url}" alt="Preview ${index + 1}" class="img-thumbnail w-100" style="height: 100px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                        onclick="removeVehicleImage(${index}, '${url}')" title="Eliminar imagen">
                    <i class="fas fa-times"></i>
                </button>
    </div>
</div>
    `;
    previewsContainer.insertAdjacentHTML('beforeend', previewHtml);
}

function addVehicleImageToUrls(url) {
    const urlsInput = document.getElementById('vehicleImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls.push(url);
    urlsInput.value = JSON.stringify(urls);
}

function removeVehicleImage(index, url) {
    // Remover de previsualizaciones
    const previewElement = document.getElementById(`preview-${index}`);
    if (previewElement) {
        previewElement.remove();
    }
    
    // Remover de URLs
    const urlsInput = document.getElementById('vehicleImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls = urls.filter(u => u !== url);
    urlsInput.value = JSON.stringify(urls);
}

function previewVehicleImages() {
    const urls = JSON.parse(document.getElementById('vehicleImageUrls').value);
    if (urls.length > 0) {
        urls.forEach((url, index) => {
            addVehicleImageToPreview(url, index);
        });
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = '';
    document.getElementById('statusFilter').value = '';
    applyFilters();
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2) strong').textContent.toLowerCase();
        const category = row.querySelector('td:nth-child(3) .badge').textContent.toLowerCase();
        const status = row.querySelector('td:nth-child(6) .badge').textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm);
        const normalizedCategory = category.replace(/\s+/g, '_');
        const matchesCategory = !categoryFilter || normalizedCategory.includes(categoryFilter.toLowerCase());
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
        
        row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
    });

    // Filtrar tarjetas móviles
    const mobileCards = document.querySelectorAll('.d-lg-none .card');
    mobileCards.forEach(card => {
        const nameEl = card.querySelector('h6, strong');
        const name = nameEl ? nameEl.textContent.toLowerCase() : '';
        const badges = card.querySelectorAll('.badge');
        const category = badges.length > 0 ? badges[0].textContent.toLowerCase() : '';
        const status = badges.length > 1 ? badges[badges.length - 1].textContent.toLowerCase() : '';

        const matchesSearch = name.includes(searchTerm);
        const normalizedCategory = category.replace(/\s+/g, '_');
        const matchesCategory = !categoryFilter || normalizedCategory.includes(categoryFilter.toLowerCase());
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());

        card.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
    });
}

// Aplicar filtros en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    
    // Event listener para cambio de archivo de imagen
    const vehicleImageFiles = document.getElementById('vehicleImageFiles');
    if (vehicleImageFiles) {
        vehicleImageFiles.addEventListener('change', handleVehicleImageChange);
    }
    
    // Cargar ubicaciones al iniciar
    loadLocations();
    
    // Cargar características desde la base de datos
    loadFeatures();
    
    // Inicializar características
    initializeFeatures();

    // Cargar categorías dinámicas (filtro y formulario)
    fetch(`${APP_URL}/admin/types/api?category=vehicle`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        // Filtro superior
        const cf = document.getElementById('categoryFilter');
        if (cf) {
            const saved = cf.value;
            cf.innerHTML = '<option value="">Todas las categorías</option>';
            (data.data||[]).forEach(t=>{
                const opt = document.createElement('option');
                opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
                opt.textContent = t.name;
                cf.appendChild(opt);
            });
            cf.value = saved;
        }
        // Select del modal
        const sel = document.getElementById('vehicleCategorySelect');
        if (sel) {
            const current = sel.value;
            sel.innerHTML = '<option value="">Seleccionar categoría</option>';
            (data.data||[]).forEach(t=>{
                const opt = document.createElement('option');
                opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
                opt.textContent = t.name;
                sel.appendChild(opt);
            });
            sel.value = current;
        }
    });

    // Cargar categorías de servicio para el modal de nueva característica
    fetch(`${APP_URL}/admin/service-categories/api`).then(r=>r.json()).then(data=>{
        const sel = document.getElementById('featureCategorySelect');
        if (!sel) return;
        sel.innerHTML = '';
        const list = (data && data.success && Array.isArray(data.data)) ? data.data : [
            { key: 'vehicle', label: 'Vehículo' },
            { key: 'boat', label: 'Barco' },
            { key: 'transfer', label: 'Traslado' },
            { key: 'general', label: 'General' },
        ];
        list.forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.key;
            opt.textContent = it.label;
            sel.appendChild(opt);
        });
    }).catch(()=>{
        const sel = document.getElementById('featureCategorySelect');
        if (!sel) return;
        sel.innerHTML = '';
        [
            { key: 'vehicle', label: 'Vehículo' },
            { key: 'boat', label: 'Barco' },
            { key: 'transfer', label: 'Traslado' },
            { key: 'general', label: 'General' },
        ].forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.key;
            opt.textContent = it.label;
            sel.appendChild(opt);
        });
    });
});

// Inicializar sistema de características
function initializeFeatures() {
    console.log('🔧 Inicializando sistema de características...');
    
    const selectedFeaturesInput = document.getElementById('selectedFeatures');
    let selectedFeatures = [];
    
    // Cargar características guardadas si existen
    try {
        const savedFeatures = selectedFeaturesInput.value;
        if (savedFeatures && savedFeatures !== '[]') {
            selectedFeatures = JSON.parse(savedFeatures);
            console.log('📋 Características guardadas:', selectedFeatures);
        }
    } catch (e) {
        console.error('❌ Error al cargar características guardadas:', e);
    }
    
    // Actualizar botones con características seleccionadas
    if (selectedFeatures.length > 0) {
        setTimeout(() => {
            updateFeatureButtons(selectedFeatures);
        }, 200);
    }
    
    console.log('✅ Sistema de características inicializado');
    
    // Event listeners para botones de características
    const featureBtns = document.querySelectorAll('.feature-btn');
    featureBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const feature = this.getAttribute('data-feature');
            
            if (selectedFeatures.includes(feature)) {
                // Remover característica
                selectedFeatures = selectedFeatures.filter(f => f !== feature);
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-primary');
                this.querySelector('i').classList.remove('fa-check');
                this.querySelector('i').classList.add('fa-plus');
            } else {
                // Agregar característica
                selectedFeatures.push(feature);
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                this.querySelector('i').classList.remove('fa-plus');
                this.querySelector('i').classList.add('fa-check');
            }
            
            // Actualizar input hidden
            selectedFeaturesInput.value = JSON.stringify(selectedFeatures);
            
            // Mostrar contador de características seleccionadas
            updateFeatureCounter(selectedFeatures.length);
        });
    });
    
    // Mostrar contador inicial
    updateFeatureCounter(selectedFeatures.length);
}

// Actualizar estado visual de botones de características
function updateFeatureButtons(selectedFeatures) {
    const featureBtns = document.querySelectorAll('.feature-btn');
    
    featureBtns.forEach(btn => {
        const feature = btn.getAttribute('data-feature');
        
        if (selectedFeatures.includes(feature)) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
            btn.querySelector('i').classList.remove('fa-plus');
            btn.querySelector('i').classList.add('fa-check');
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
            btn.querySelector('i').classList.remove('fa-check');
            btn.querySelector('i').classList.add('fa-plus');
        }
    });
    
    // Actualizar contador
    updateFeatureCounter(selectedFeatures.length);
}

// Actualizar contador de características seleccionadas
function updateFeatureCounter(count) {
    const counterElement = document.getElementById('featureCounter');
    if (counterElement) {
        counterElement.textContent = count;
        counterElement.className = count > 0 ? 'badge bg-success' : 'badge bg-secondary';
    }
}

// Cargar ubicaciones existentes
function loadLocations() {
    fetch(`${APP_URL}/admin/locations/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const locationSelect = document.getElementById('locationSelect');
                locationSelect.innerHTML = '<option value="">Seleccionar ubicación</option>';
                
                data.data.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = `${location.name} - ${location.address}`;
                    locationSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar ubicaciones:', error);
        });
}

// Mostrar modal de nueva ubicación
function showNewLocationModal() {
    new bootstrap.Modal(document.getElementById('newLocationModal')).show();
}

// Crear nueva ubicación
function createNewLocation() {
    const form = document.getElementById('newLocationForm');
    const formData = {
        name: form.name.value,
        address: form.address.value,
        latitude: form.latitude.value || null,
        longitude: form.longitude.value || null
    };
    
    fetch(`${APP_URL}/admin/locations/api`, {
        method: 'POST',
            headers: {
                'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
            alert('Ubicación creada exitosamente');
            // Cerrar modal
            const modal = document.getElementById('newLocationModal');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            // Recargar ubicaciones
            loadLocations();
            // Limpiar formulario
            form.reset();
            } else {
            alert('Error al crear ubicación: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        alert('Error del servidor');
        });
    }

// Mostrar modal de nueva característica
function showNewFeatureModal() {
    new bootstrap.Modal(document.getElementById('newFeatureModal')).show();
}

// Crear nueva característica
function createNewFeature() {
    const form = document.getElementById('newFeatureForm');
    const formData = {
        name: form.name.value,
        category: form.category.value,
        icon: form.icon.value
    };
    
    fetch(`${APP_URL}/admin/features/api`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Característica creada exitosamente');
            // Cerrar modal
            const modal = document.getElementById('newFeatureModal');
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
            // Recargar características
            loadFeatures();
            // Limpiar formulario
            form.reset();
        } else {
            alert('Error al crear característica: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error del servidor');
    });
}

// Cargar características desde la base de datos
function loadFeatures() {
    console.log('🔄 Cargando características desde la base de datos...');
    fetch(`${APP_URL}/admin/features/api?category=vehicle`)
        .then(response => response.json())
        .then(data => {
            console.log('📋 Respuesta de características:', data);
            if (data.success && data.data) {
                const container = document.getElementById('featuresContainer');
                container.innerHTML = '';
                
                data.data.forEach(feature => {
                    const col = document.createElement('div');
                    col.className = 'col-md-4';
                    col.innerHTML = `
                        <button type="button" class="btn btn-outline-primary feature-btn" data-feature="${feature.name}">
                            <i class="${feature.icon} me-1"></i>${feature.name}
                        </button>
                    `;
                    container.appendChild(col);
                });
                
                console.log(`✅ ${data.data.length} características cargadas`);
                
                // Reinicializar características después de cargar
                setTimeout(() => {
                    initializeFeatures();
                }, 100);
            } else {
                console.warn('⚠️ No se recibieron características válidas');
                // Mostrar características por defecto si no hay en la DB
                showDefaultFeatures();
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar características:', error);
            // Mostrar características por defecto en caso de error
            showDefaultFeatures();
        });
}

// Mostrar características por defecto si no hay en la DB
function showDefaultFeatures() {
    const container = document.getElementById('featuresContainer');
    const defaultFeatures = [
        { name: 'GPS Navigation', icon: 'fas fa-map-marker-alt' },
        { name: 'Leather Seats', icon: 'fas fa-chair' },
        { name: 'Bluetooth', icon: 'fas fa-bluetooth-b' },
        { name: '360 Camera', icon: 'fas fa-camera' },
        { name: 'Parking Sensors', icon: 'fas fa-parking' },
        { name: 'Panoramic Roof', icon: 'fas fa-sun' }
    ];
    
    container.innerHTML = '';
    defaultFeatures.forEach(feature => {
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <button type="button" class="btn btn-outline-primary feature-btn" data-feature="${feature.name}">
                <i class="${feature.icon} me-1"></i>${feature.name}
            </button>
        `;
        container.appendChild(col);
    });
    
    console.log('🔄 Características por defecto mostradas');
}

// Limpiar todas las características seleccionadas
function clearAllFeatures() {
    const selectedFeaturesInput = document.getElementById('selectedFeatures');
    selectedFeaturesInput.value = '[]';
    
    // Actualizar botones
    const featureBtns = document.querySelectorAll('.feature-btn');
    featureBtns.forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
        btn.querySelector('i').classList.remove('fa-check');
        btn.querySelector('i').classList.add('fa-plus');
    });
    
    // Actualizar contador
    updateFeatureCounter(0);
    
    console.log('🧹 Todas las características han sido limpiadas');
}

// Seleccionar características comunes para vehículos
function selectCommonFeatures() {
    const commonFeatures = ['GPS Navigation', 'Bluetooth', 'Air Conditioning', 'Power Windows'];
    const selectedFeaturesInput = document.getElementById('selectedFeatures');
    
    // Obtener características actuales
    let currentFeatures = [];
    try {
        const savedFeatures = selectedFeaturesInput.value;
        if (savedFeatures && savedFeatures !== '[]') {
            currentFeatures = JSON.parse(savedFeatures);
        }
    } catch (e) {
        console.error('Error al parsear características:', e);
    }
    
    // Agregar características comunes que no estén ya seleccionadas
    commonFeatures.forEach(feature => {
        if (!currentFeatures.includes(feature)) {
            currentFeatures.push(feature);
        }
    });
    
    // Actualizar input
    selectedFeaturesInput.value = JSON.stringify(currentFeatures);
    
    // Actualizar botones
    updateFeatureButtons(currentFeatures);
    
    console.log('⭐ Características comunes seleccionadas');
}

// Mostrar modal para agregar nuevo tipo
async function showNewTypeModal(category) {
    const typeName = prompt(`Ingresa el nombre del nuevo tipo de ${category}:`);
    if (typeName && typeName.trim()) {
        try {
            // Crear el nuevo tipo en la base de datos
            const response = await fetch(`${APP_URL}/admin/types/api`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: typeName.trim(),
                    category: category
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Agregar la nueva opción al select
                const select = document.getElementById(`${category}CategorySelect`);
                if (select) {
                    const newOption = document.createElement('option');
                    newOption.value = typeName.toLowerCase().replace(/\s+/g, '_');
                    newOption.textContent = typeName;
                    select.appendChild(newOption);
                    
                    // Seleccionar la nueva opción
                    select.value = newOption.value;
                }
                
                console.log('✅ Nuevo tipo agregado:', typeName);
            } else {
                alert('Error al crear el tipo: ' + data.message);
            }
        } catch (error) {
            console.error('Error al crear tipo:', error);
            alert('Error al crear el tipo');
        }
    }
}

// Gestionar tipos de vehículo (con selección múltiple)
function showManageTypesModalVehicle() {
    fetch(`${APP_URL}/admin/types/api?category=vehicle`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando tipos');
        const cont = document.getElementById('vehicleTypesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(t=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class="form-check-input me-2" type="checkbox" value="${t.id}"><span>${t.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageVehicleTypesModal')).show();
    });
}

function deleteSelectedVehicleTypes() {
    const boxes = document.querySelectorAll('#manageVehicleTypesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos un tipo');
    if (!confirm(`¿Eliminar ${boxes.length} tipo(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/types/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar un tipo');
        }
        reloadVehicleTypes();
        // Actualizar lista del modal
        showManageTypesModalVehicle();
    })();
}

function reloadVehicleTypes(selectValue) {
    fetch(`${APP_URL}/admin/types/api?category=vehicle`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        const sel = document.getElementById('vehicleCategorySelect');
        const current = sel.value;
        sel.innerHTML = '<option value="">Seleccionar categoría</option>';
        (data.data||[]).forEach(t=>{
            const opt = document.createElement('option');
            opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
        if (selectValue) sel.value = selectValue.toLowerCase().replace(/\s+/g,'_'); else sel.value = current;
    });
}

// Gestionar características de vehículo (con selección múltiple)
function showManageFeaturesModalVehicle() {
    fetch(`${APP_URL}/admin/features/api?category=vehicle`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando características');
        const cont = document.getElementById('vehicleFeaturesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(f=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class=\"form-check-input me-2\" type=\"checkbox\" value=\"${f.id}\"><i class=\"${f.icon||'fas fa-check'} me-1\"></i><span>${f.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageVehicleFeaturesModal')).show();
    });
}

function deleteSelectedVehicleFeatures() {
    const boxes = document.querySelectorAll('#manageVehicleFeaturesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos una característica');
    if (!confirm(`¿Eliminar ${boxes.length} característica(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/features/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar una característica');
        }
        // Recargar parrilla de características del formulario
        loadFeatures();
        // Actualizar lista del modal
        showManageFeaturesModalVehicle();
    })();
}
</script>
