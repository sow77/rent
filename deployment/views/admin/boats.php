<?php
// views/admin/boats.php - Vista de gestión de barcos
if (!isset($boats)) {
    $boats = [];
}

$pageTitle = 'Gestión de Barcos';
$currentPage = 'boats';

// Capturar el contenido en un buffer para el layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Gestión de Barcos</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBoatModal">
        <i class="fas fa-plus me-2"></i>Agregar Barco
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
                    <input type="text" class="form-control" placeholder="Buscar barcos..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" id="typeFilter">
                    <option value="">Todos los tipos</option>
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

<!-- Boats Table -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-ship me-2"></i>Lista de Barcos</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($boats)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-ship fa-3x mb-3"></i><br>
            <h5>No hay barcos registrados</h5>
            <p class="text-muted">Comienza agregando tu primer barco</p>
        </div>
        <?php else: ?>
        <!-- Desktop Table -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Imagen</th>
                            <th>Barco</th>
                            <th style="width: 120px;">Tipo</th>
                            <th style="width: 100px;">Capacidad</th>
                            <th style="width: 120px;">Precio/Día</th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($boats as $boat): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($boat['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($boat['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($boat['name']); ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 60px; height: 40px; font-size: 10px; border-radius: 4px;">
                                        <i class="fas fa-ship"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($boat['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">ID: #<?php echo htmlspecialchars($boat['id']); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($boat['type'])); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted"><?php echo htmlspecialchars($boat['capacity']); ?> personas</span>
                            </td>
                            <td class="text-center">
                                <strong class="text-success">€<?php echo number_format($boat['daily_rate'], 2); ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $boat['available'] ? 'success' : 'warning'; ?>">
                                    <i class="fas fa-<?php echo $boat['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                    <?php echo $boat['available'] ? 'Disponible' : 'Mantenimiento'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editBoat('<?php echo $boat['id']; ?>')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBoat('<?php echo $boat['id']; ?>')" title="Eliminar">
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
            <?php foreach ($boats as $boat): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if (!empty($boat['image'])): ?>
                                <img src="<?php echo htmlspecialchars($boat['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($boat['name']); ?>" 
                                     class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 60px; font-size: 16px; border-radius: 8px;">
                                    <i class="fas fa-ship"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($boat['name']); ?></h6>
                            <small class="text-muted">ID: #<?php echo htmlspecialchars($boat['id']); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editBoat('<?php echo $boat['id']; ?>')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteBoat('<?php echo $boat['id']; ?>')">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Tipo:</small>
                            <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($boat['type'])); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Capacidad:</small>
                            <span><?php echo htmlspecialchars($boat['capacity']); ?> personas</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Precio/Día:</small>
                            <span class="text-success fw-bold">€<?php echo number_format($boat['daily_rate'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Estado:</small>
                            <span class="badge bg-<?php echo $boat['available'] ? 'success' : 'warning'; ?>">
                                <i class="fas fa-<?php echo $boat['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                <?php echo $boat['available'] ? 'Disponible' : 'Mantenimiento'; ?>
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

<!-- Add Boat Modal -->
<div class="modal fade" id="addBoatModal" tabindex="-1" aria-labelledby="addBoatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBoatModalLabel"><i class="fas fa-ship me-2"></i>Agregar Barco</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBoatForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-tag me-1"></i>Nombre</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-ship me-1"></i>Modelo</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-list me-1"></i>Tipo</label>
                            <div class="input-group">
                                <select class="form-select" name="type" id="boatTypeSelect" required>
                                    <option value="">Seleccionar tipo</option>
                            </select>
                                <button class="btn btn-outline-secondary" type="button" onclick="showNewTypeModal('boat')">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-outline-danger" type="button" onclick="showManageTypesModalBoat()" title="Gestionar tipos">
                                    <i class="fas fa-cog"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-users me-1"></i>Capacidad</label>
                            <input type="number" class="form-control" name="capacity" min="1" max="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-euro-sign me-1"></i>Precio por Día</label>
                            <input type="number" class="form-control" name="daily_rate" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                            <select class="form-select" name="available" required>
                                <option value="1">Disponible</option>
                                <option value="0">En Mantenimiento</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-ruler-horizontal me-1"></i>Longitud (m)</label>
                            <input type="number" class="form-control" name="length" step="0.1" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Ubicación</label>
                            <div class="input-group">
                                <select class="form-select" name="location_id" id="locationSelect">
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
                            <small class="text-muted">Haz clic en las características que tenga el barco</small>
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showManageFeaturesModalBoat()">
                                    <i class="fas fa-cog me-1"></i>Gestionar Características
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-images me-1"></i>Imágenes del Barco</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="image_files" accept="image/*" id="boatImageFiles" multiple>
                                <button class="btn btn-outline-secondary" type="button" onclick="previewBoatImages()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <input type="hidden" name="images" id="boatImageUrls" value="[]">
                            <small class="text-muted">Puedes seleccionar múltiples imágenes (máximo 10)</small>
                            <div id="boatImagePreviews" class="mt-2 row g-2">
                                <!-- Las previsualizaciones se cargarán dinámicamente -->
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-align-left me-1"></i>Descripción</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Descripción del barco..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveBoat()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- New Location Modal -->
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
                        <label class="form-label"><i class="fas fa-tag me-1"></i>Nombre</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Dirección</label>
                        <input type="text" class="form-control" name="address" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label"><i class="fas fa-globe me-1"></i>Latitud</label>
                            <input type="number" class="form-control" name="latitude" step="any">
                        </div>
                        <div class="col-6">
                            <label class="form-label"><i class="fas fa-globe me-1"></i>Longitud</label>
                            <input type="number" class="form-control" name="longitude" step="any">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="createNewLocation()">
                    <i class="fas fa-save me-1"></i>Crear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gestionar Tipos (Barcos) -->
<div class="modal fade" id="manageBoatTypesModal" tabindex="-1" aria-labelledby="manageBoatTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageBoatTypesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Tipos (Barcos)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="boatTypesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedBoatTypes()">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
            </div>
        </div>
    </div>
    </div>

<!-- Modal: Gestionar Características (Barcos) -->
<div class="modal fade" id="manageBoatFeaturesModal" tabindex="-1" aria-labelledby="manageBoatFeaturesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageBoatFeaturesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Características (Barcos)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="boatFeaturesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedBoatFeatures()">
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

function editBoat(id) {
    // Obtener datos del barco
    fetch(`${APP_URL}/admin/boats/api/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al cargar los datos del barco');
                return;
            }
            const boat = data.data;
            
            // Llenar el formulario con los datos del barco
            const form = document.getElementById('addBoatForm');
            form.name.value = boat.name;
            form.model.value = boat.model;
            form.type.value = boat.type;
            form.capacity.value = boat.capacity;
            form.daily_rate.value = boat.daily_rate;
            form.available.value = boat.available ? '1' : '0';
            form.length.value = boat.length || '';
            form.location_id.value = boat.location_id || '';
            form.description.value = boat.description || '';
            
            // Manejar imágenes múltiples
            let images = [];
            if (boat.images) {
                try {
                    images = JSON.parse(boat.images);
                } catch (e) {
                    console.error('Error al parsear imágenes:', e);
                    // Fallback a imagen única si existe
                    if (boat.image) {
                        images = [boat.image];
                    }
                }
            } else if (boat.image) {
                images = [boat.image];
            }
            
            // Actualizar el campo de imágenes
            document.getElementById('boatImageUrls').value = JSON.stringify(images);
            
            // Mostrar previsualizaciones de imágenes
            const previewsContainer = document.getElementById('boatImagePreviews');
            previewsContainer.innerHTML = '';
            images.forEach((imageUrl, index) => {
                addBoatImageToPreview(imageUrl, index);
            });
            
            // Cambiar el título del modal
            document.querySelector('#addBoatModal .modal-title').innerHTML = '<i class="fas fa-ship me-2"></i>Editar Barco';
            // Cambiar el botón de guardar
            const saveButton = document.querySelector('#addBoatModal .btn-primary');
            saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            saveButton.onclick = () => saveBoat(id);
            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('addBoatModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del barco');
        });
}

function deleteBoat(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este barco?')) {
        fetch(`${APP_URL}/admin/boats/api/${id}`, {
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
                alert(data.message || 'Error al eliminar el barco');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el barco');
        });
    }
}

function saveBoat(id = null) {
    const form = document.getElementById('addBoatForm');
    
    // Validar campos requeridos
    if (!form.name.value || !form.model.value || !form.type.value || !form.capacity.value || !form.daily_rate.value) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    // Preparar datos del formulario
    const formData = {
        name: form.name.value,
        model: form.model.value,
        type: form.type.value,
        capacity: form.capacity.value,
        daily_rate: form.daily_rate.value,
        available: form.available.value,
        length: form.length.value,
        location_id: form.location_id.value,
        images: document.getElementById('boatImageUrls').value, // Usar las URLs de las imágenes subidas
        description: form.description.value,
        features: form.features.value
    };
    
    console.log('Enviando datos del barco:', formData);
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${APP_URL}/admin/boats/api/${id}` : `${APP_URL}/admin/boats/api`;
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Barco guardado exitosamente');
            // Cerrar modal
            const modal = document.getElementById('addBoatModal');
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
function handleBoatImageChange(event) {
    const files = Array.from(event.target.files);
    if (files.length > 10) {
        alert('Máximo 10 imágenes permitidas');
        return;
    }
    
    // Limpiar previsualizaciones anteriores
    document.getElementById('boatImagePreviews').innerHTML = '';
    document.getElementById('boatImageUrls').value = '[]';
    
    // Subir cada archivo
    files.forEach((file, index) => {
        uploadBoatImage(file, index);
    });
}

function uploadBoatImage(file, index) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'boat');
    
    console.log('🔄 Subiendo imagen de barco...', file.name);
    
    fetch(`${APP_URL}/api/file-upload.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Respuesta del servidor:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text();
    })
    .then(text => {
        console.log('📤 Respuesta completa:', text);
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('❌ Error al parsear JSON:', e);
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (data.success) {
            addBoatImageToPreview(data.url, index);
            addBoatImageToUrls(data.url);
            console.log('✅ Imagen de barco subida exitosamente:', data.url);
        } else {
            throw new Error(data.message || 'Error desconocido al subir imagen');
        }
    })
    .catch(error => {
        console.error('❌ Error al subir imagen de barco:', error);
        alert(`Error al subir imagen ${index + 1}: ${error.message}`);
    });
}

function addBoatImageToPreview(url, index) {
    const previewsContainer = document.getElementById('boatImagePreviews');
    const previewHtml = `
        <div class="col-md-3 col-sm-4 col-6" id="preview-${index}">
            <div class="position-relative">
                <img src="${url}" alt="Preview ${index + 1}" class="img-thumbnail w-100" style="height: 100px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                        onclick="removeBoatImage(${index}, '${url}')" title="Eliminar imagen">
                    <i class="fas fa-times"></i>
                </button>
        </div>
        </div>
    `;
    previewsContainer.insertAdjacentHTML('beforeend', previewHtml);
}

function addBoatImageToUrls(url) {
    const urlsInput = document.getElementById('boatImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls.push(url);
    urlsInput.value = JSON.stringify(urls);
}

function removeBoatImage(index, url) {
    // Remover de previsualizaciones
    const previewElement = document.getElementById(`preview-${index}`);
    if (previewElement) {
        previewElement.remove();
    }
    
    // Remover de URLs
    const urlsInput = document.getElementById('boatImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls = urls.filter(u => u !== url);
    urlsInput.value = JSON.stringify(urls);
}

function previewBoatImages() {
    const urls = JSON.parse(document.getElementById('boatImageUrls').value);
    if (urls.length > 0) {
        urls.forEach((url, index) => {
            addBoatImageToPreview(url, index);
        });
    }
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    applyFilters();
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const rows = document.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2) strong').textContent.toLowerCase();
        const type = row.querySelector('td:nth-child(3) .badge').textContent.toLowerCase();
        const status = row.querySelector('td:nth-child(6) .badge').textContent.toLowerCase();
        
        const matchesSearch = name.includes(searchTerm);
        const matchesType = !typeFilter || type.toLowerCase().includes(typeFilter.toLowerCase());
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());
        
        row.style.display = matchesSearch && matchesType && matchesStatus ? '' : 'none';
    });

    // Filtrar tarjetas móviles
    const mobileCards = document.querySelectorAll('.d-lg-none .card');
    mobileCards.forEach(card => {
        const nameEl = card.querySelector('h6, strong');
        const name = nameEl ? nameEl.textContent.toLowerCase() : '';
        const badges = card.querySelectorAll('.badge');
        const type = badges.length > 0 ? badges[0].textContent.toLowerCase() : '';
        const status = badges.length > 1 ? badges[badges.length - 1].textContent.toLowerCase() : '';

        const matchesSearch = name.includes(searchTerm);
        const matchesType = !typeFilter || type.includes(typeFilter.toLowerCase());
        const matchesStatus = !statusFilter || status.includes(statusFilter.toLowerCase());

        card.style.display = (matchesSearch && matchesType && matchesStatus) ? '' : 'none';
    });
}

// Funciones para manejo de ubicaciones
function loadLocations() {
    fetch(`${APP_URL}/admin/locations/api`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('locationSelect');
                select.innerHTML = '<option value="">Seleccionar ubicación</option>';
                data.data.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.id;
                    option.textContent = `${location.name} - ${location.address}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar ubicaciones:', error);
        });
    }

function showNewLocationModal() {
    new bootstrap.Modal(document.getElementById('newLocationModal')).show();
}

function createNewLocation() {
    const form = document.getElementById('newLocationForm');
    const formData = {
        name: form.name.value,
        address: form.address.value,
        latitude: form.latitude.value,
        longitude: form.longitude.value
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
            // Limpiar formulario
            form.reset();
            // Recargar ubicaciones
            loadLocations();
        } else {
            alert('Error al crear ubicación: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear ubicación: ' + error.message);
    });
}

// Aplicar filtros en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (typeFilter) typeFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    
    // Event listener para cambio de archivo de imagen
    const boatImageFiles = document.getElementById('boatImageFiles');
    if (boatImageFiles) {
        boatImageFiles.addEventListener('change', handleBoatImageChange);
    }
    
    // Cargar ubicaciones al inicializar
    loadLocations();
    
    // Cargar características al inicializar
    initializeFeatures();

    // Cargar tipos dinámicos (filtro y formulario)
    fetch(`${APP_URL}/admin/types/api?category=boat`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        // Filtro
        const tf = document.getElementById('typeFilter');
        const saved = tf.value;
        tf.innerHTML = '<option value="">Todos los tipos</option>';
        (data.data||[]).forEach(t=>{
            const opt = document.createElement('option');
            opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
            opt.textContent = t.name;
            tf.appendChild(opt);
        });
        tf.value = saved;

        // Formulario
        const sel = document.getElementById('boatTypeSelect');
        const current = sel.value;
        sel.innerHTML = '<option value="">Seleccionar tipo</option>';
        (data.data||[]).forEach(t=>{
            const opt = document.createElement('option');
            opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
        sel.value = current;
    });
});

// Funciones para manejo de características
function initializeFeatures() {
    console.log('🔄 Inicializando características de barcos...');
    
    fetch(`${APP_URL}/admin/features/api?category=boat`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('✅ Características cargadas:', data.data);
                renderFeatures(data.data);
            } else {
                console.error('❌ Error al cargar características:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar características:', error);
        });
}

function renderFeatures(features) {
    const container = document.getElementById('featuresContainer');
    container.innerHTML = '';
    
    features.forEach(feature => {
        const col = document.createElement('div');
        col.className = 'col-md-3 col-sm-4 col-6';
        col.innerHTML = `
            <button type="button" class="btn btn-outline-primary w-100 feature-btn" 
                    data-feature="${feature.name}" data-id="${feature.id}">
                <i class="${feature.icon || 'fas fa-check'} me-1"></i>
                ${feature.name}
            </button>
        `;
        container.appendChild(col);
    });
    
    // Agregar event listeners
    const featureBtns = document.querySelectorAll('.feature-btn');
    featureBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.toggle('btn-primary');
            this.classList.toggle('btn-outline-primary');
            updateFeatureButtons();
        });
    });
}

function updateFeatureButtons() {
    const selectedBtns = document.querySelectorAll('.feature-btn.btn-primary');
    const selectedFeatures = Array.from(selectedBtns).map(btn => btn.dataset.feature);
    
    document.getElementById('selectedFeatures').value = JSON.stringify(selectedFeatures);
    document.getElementById('featureCounter').textContent = selectedFeatures.length;
}

function clearAllFeatures() {
    const featureBtns = document.querySelectorAll('.feature-btn');
    featureBtns.forEach(btn => {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    updateFeatureButtons();
}

function selectCommonFeatures() {
    clearAllFeatures();
    
    const commonFeatures = ['Sistema de navegación GPS', 'Radio marina VHF', 'Chalecos salvavidas', 'Ancla de seguridad', 'Motor fuera de borda'];
    const featureBtns = document.querySelectorAll('.feature-btn');
    
    featureBtns.forEach(btn => {
        if (commonFeatures.includes(btn.dataset.feature)) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
        }
    });
    updateFeatureButtons();
}

function showNewFeatureModal() {
    // Crear modal si no existe
    let modal = document.getElementById('newBoatFeatureModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'newBoatFeatureModal';
        modal.tabIndex = -1;
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nueva Característica</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="newBoatFeatureName" placeholder="Ej: Wi-Fi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" id="boatFeatureCategorySelect">
                                <option value="">Cargando...</option>
                            </select>
                            <small class="text-muted">Por defecto: Barco</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveNewBoatFeatureBtn">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }

    // Cargar categorías dinámicas
    fetch(`${APP_URL}/admin/service-categories/api`).then(r=>r.json()).then(data=>{
        const sel = document.getElementById('boatFeatureCategorySelect');
        if (!sel) return;
        sel.innerHTML = '';
        const list = (data && data.success && Array.isArray(data.data)) ? data.data : [
            { key: 'boat', label: 'Barco' }
        ];
        list.forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.key;
            opt.textContent = it.label;
            sel.appendChild(opt);
        });
        // Seleccionar por defecto 'boat' si existe
        sel.value = 'boat';
    }).catch(()=>{
        const sel = document.getElementById('boatFeatureCategorySelect');
        if (sel) { sel.innerHTML = '<option value="boat">Barco</option>'; }
    });

    // Guardar
    const saveBtn = document.getElementById('saveNewBoatFeatureBtn');
    if (saveBtn) {
        saveBtn.onclick = () => {
            const name = (document.getElementById('newBoatFeatureName').value || '').trim();
            const category = document.getElementById('boatFeatureCategorySelect').value || 'boat';
            if (!name) { alert('Ingresa un nombre'); return; }
            fetch(`${APP_URL}/admin/features/api`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, category })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal y recargar características
                    const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('newBoatFeatureModal'));
                    m.hide();
                    initializeFeatures();
                } else {
                    alert(data.message || 'Error al crear característica');
                }
            })
            .catch(err => alert('Error del servidor: ' + err.message));
        };
    }

    // Mostrar
    bootstrap.Modal.getOrCreateInstance(modal).show();
}

function showManageFeaturesModalBoat() {
    fetch(`${APP_URL}/admin/features/api?category=boat`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando características');
        const cont = document.getElementById('boatFeaturesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(f=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class=\"form-check-input me-2\" type=\"checkbox\" value=\"${f.id}\"><i class=\"${f.icon||'fas fa-check'} me-1\"></i><span>${f.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageBoatFeaturesModal')).show();
    });
}

function deleteSelectedBoatFeatures() {
    const boxes = document.querySelectorAll('#manageBoatFeaturesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos una característica');
    if (!confirm(`¿Eliminar ${boxes.length} característica(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/features/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar una característica');
        }
        initializeFeatures();
        showManageFeaturesModalBoat();
    })();
}

function showManageTypesModalBoat() {
    fetch(`${APP_URL}/admin/types/api?category=boat`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando tipos');
        const cont = document.getElementById('boatTypesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(t=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class=\"form-check-input me-2\" type=\"checkbox\" value=\"${t.id}\"><span>${t.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageBoatTypesModal')).show();
    });
}

function deleteSelectedBoatTypes() {
    const boxes = document.querySelectorAll('#manageBoatTypesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos un tipo');
    if (!confirm(`¿Eliminar ${boxes.length} tipo(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/types/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar un tipo');
        }
        reloadBoatTypes();
        showManageTypesModalBoat();
    })();
}

function reloadBoatTypes(selectValue) {
    fetch(`${APP_URL}/admin/types/api?category=boat`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        const sel = document.getElementById('boatTypeSelect');
        const current = sel.value;
        sel.innerHTML = '<option value="">Seleccionar tipo</option>';
        (data.data||[]).forEach(t=>{
            const opt = document.createElement('option');
            opt.value = t.name.toLowerCase().replace(/\s+/g,'_');
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
        if (selectValue) sel.value = selectValue.toLowerCase().replace(/\s+/g,'_'); else sel.value = current;
    });
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
                const select = document.getElementById(`${category}TypeSelect`);
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
</script>
