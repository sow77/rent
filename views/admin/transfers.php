<?php
// views/admin/transfers.php - Vista de gestión de traslados
if (!isset($transfers)) {
    $transfers = [];
}

$pageTitle = 'Gestión de Traslados';
$currentPage = 'transfers';

// Capturar el contenido en un buffer para el layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Gestión de Traslados</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTransferModal">
        <i class="fas fa-plus me-2"></i>Agregar Traslado
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
                    <input type="text" class="form-control" placeholder="Buscar traslados..." id="searchInput">
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

<!-- Transfers Table -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-shuttle-van me-2"></i>Lista de Traslados</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($transfers)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-shuttle-van fa-3x mb-3"></i><br>
            <h5>No hay traslados registrados</h5>
            <p class="text-muted">Comienza agregando tu primer traslado</p>
        </div>
        <?php else: ?>
        <!-- Desktop Table -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Imagen</th>
                            <th>Traslado</th>
                            <th style="width: 120px;">Tipo</th>
                            <th style="width: 100px;">Capacidad</th>
                            <th style="width: 120px;">Precio</th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transfers as $transfer): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($transfer['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($transfer['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($transfer['name']); ?>" 
                                         class="img-thumbnail" style="width: 60px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 60px; height: 40px; font-size: 10px; border-radius: 4px;">
                                        <i class="fas fa-shuttle-van"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($transfer['name']); ?></strong>
                                    <br>
                                    <small class="text-muted">ID: #<?php echo htmlspecialchars($transfer['id']); ?></small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($transfer['type'])); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted"><?php echo htmlspecialchars($transfer['capacity']); ?> personas</span>
                            </td>
                            <td class="text-center">
                                <strong class="text-success">€<?php echo number_format($transfer['price'], 2); ?></strong>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $transfer['available'] ? 'success' : 'warning'; ?>">
                                    <i class="fas fa-<?php echo $transfer['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                    <?php echo $transfer['available'] ? 'Disponible' : 'Mantenimiento'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editTransfer('<?php echo $transfer['id']; ?>')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTransfer('<?php echo $transfer['id']; ?>')" title="Eliminar">
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
            <?php foreach ($transfers as $transfer): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if (!empty($transfer['image'])): ?>
                                <img src="<?php echo htmlspecialchars($transfer['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($transfer['name']); ?>" 
                                     class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 60px; font-size: 16px; border-radius: 8px;">
                                    <i class="fas fa-shuttle-van"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($transfer['name']); ?></h6>
                            <small class="text-muted">ID: #<?php echo htmlspecialchars($transfer['id']); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editTransfer('<?php echo $transfer['id']; ?>')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteTransfer('<?php echo $transfer['id']; ?>')">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Tipo:</small>
                            <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($transfer['type'])); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Capacidad:</small>
                            <span><?php echo htmlspecialchars($transfer['capacity']); ?> personas</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Precio:</small>
                            <span class="text-success fw-bold">€<?php echo number_format($transfer['price'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Estado:</small>
                            <span class="badge bg-<?php echo $transfer['available'] ? 'success' : 'warning'; ?>">
                                <i class="fas fa-<?php echo $transfer['available'] ? 'check-circle' : 'tools'; ?> me-1"></i>
                                <?php echo $transfer['available'] ? 'Disponible' : 'Mantenimiento'; ?>
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

<!-- Add Transfer Modal -->
<div class="modal fade" id="addTransferModal" tabindex="-1" aria-labelledby="addTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTransferModalLabel"><i class="fas fa-shuttle-van me-2"></i>Agregar Traslado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addTransferForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-tag me-1"></i>Nombre</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-shuttle-van me-1"></i>Tipo</label>
                            <div class="input-group">
                                <select class="form-select" name="type" id="transferTypeSelect" required>
                                    <option value="">Seleccionar tipo</option>
                            </select>
                                <button class="btn btn-outline-secondary" type="button" onclick="showNewTypeModal()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-outline-danger" type="button" onclick="showManageTypesModalTransfer()" title="Gestionar tipos">
                                    <i class="fas fa-cog"></i>
                                </button>
                        </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-users me-1"></i>Capacidad</label>
                            <input type="number" class="form-control" name="capacity" min="1" max="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-euro-sign me-1"></i>Precio</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
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
                                <select class="form-select" name="location_id" id="locationSelect">
                                    <option value="">Seleccionar ubicación</option>
                                    <option value="47ce4d50-1993-11f0-9b0b-907841162cc6">Madrid Centro</option>
                                    <option value="47ce61e1-1993-11f0-9b0b-907841162cc6">Barcelona Aeropuerto</option>
                                    <option value="47ce6380-1993-11f0-9b0b-907841162cc6">Valencia Ciudad</option>
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
                            <small class="text-muted">Haz clic en las características que tenga el traslado</small>
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showManageFeaturesModalTransfer()">
                                    <i class="fas fa-cog me-1"></i>Gestionar Características
                                </button>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-images me-1"></i>Imágenes del Traslado</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="image_files" accept="image/*" id="transferImageFiles" multiple>
                                <button class="btn btn-outline-secondary" type="button" onclick="previewTransferImages()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <input type="hidden" name="images" id="transferImageUrls" value="[]">
                            <small class="text-muted">Puedes seleccionar múltiples imágenes (máximo 10)</small>
                            <div id="transferImagePreviews" class="mt-2 row g-2">
                                <!-- Las previsualizaciones se cargarán dinámicamente -->
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-align-left me-1"></i>Descripción</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Descripción del traslado..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveTransfer()">
                    <i class="fas fa-save me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos para el modal de características -->
<style>
.features-grid .feature-btn {
    width: 100%;
    margin-bottom: 0.5rem;
    text-align: left;
    transition: all 0.2s ease;
}

.features-grid .feature-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.features-grid .feature-btn.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.features-grid .feature-btn.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.features-grid .feature-btn i {
    transition: all 0.2s ease;
}

.features-grid .feature-btn.btn-primary i {
    color: white;
}

.features-grid .feature-btn.btn-outline-primary i {
    color: #0d6efd;
}

.features-grid .feature-btn.btn-primary i.fa-plus {
    display: none;
}

.features-grid .feature-btn.btn-primary i.fa-check {
    display: inline;
}

.features-grid .feature-btn.btn-outline-primary i.fa-check {
    display: none;
}

.features-grid .feature-btn.btn-outline-primary i.fa-plus {
    display: inline;
}

#featureCounter {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.modal-body img {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.modal-body .form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.modal-body .form-control,
.modal-body .form-select {
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.modal-body .form-control:focus,
.modal-body .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.modal-footer .btn {
    border-radius: 6px;
    font-weight: 500;
    padding: 0.5rem 1rem;
}

.modal-footer .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.modal-footer .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}
</style>

<!-- Modal: Gestionar Tipos (Transfers) -->
<div class="modal fade" id="manageTransferTypesModal" tabindex="-1" aria-labelledby="manageTransferTypesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTransferTypesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Tipos (Transfers)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="transferTypesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedTransferTypes()">
                    <i class="fas fa-trash me-1"></i>Eliminar seleccionados
                </button>
            </div>
        </div>
    </div>
    </div>

<!-- Modal: Gestionar Características (Transfers) -->
<div class="modal fade" id="manageTransferFeaturesModal" tabindex="-1" aria-labelledby="manageTransferFeaturesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTransferFeaturesModalLabel"><i class="fas fa-cog me-2"></i>Gestionar Características (Transfers)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="transferFeaturesList" class="list-group small"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger" onclick="deleteSelectedTransferFeatures()">
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

function editTransfer(id) {
    // Obtener datos del traslado
    fetch(`${APP_URL}/admin/transfers/api/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al cargar los datos del traslado');
                return;
            }
            const transfer = data.data;
            
            // Llenar el formulario con los datos del traslado
            const form = document.getElementById('addTransferForm');
            form.name.value = transfer.name;
            form.type.value = transfer.type;
            form.capacity.value = transfer.capacity;
            form.price.value = transfer.price;
            form.available.value = transfer.available ? '1' : '0';
            form.location_id.value = transfer.location_id || '';
            form.description.value = transfer.description || '';
            
            // Manejar imágenes múltiples
            let images = [];
            if (transfer.images) {
                try {
                    images = JSON.parse(transfer.images);
                } catch (e) {
                    console.error('Error al parsear imágenes:', e);
                    // Fallback a imagen única si existe
                    if (transfer.image) {
                        images = [transfer.image];
                    }
                }
            } else if (transfer.image) {
                images = [transfer.image];
            }
            
            // Actualizar el campo de imágenes
            document.getElementById('transferImageUrls').value = JSON.stringify(images);
            
            // Mostrar previsualizaciones de imágenes
            const previewsContainer = document.getElementById('transferImagePreviews');
            previewsContainer.innerHTML = '';
            images.forEach((imageUrl, index) => {
                addTransferImageToPreview(imageUrl, index);
            });
            
            // Manejar características
            if (transfer.features) {
                let features = [];
                try {
                    if (typeof transfer.features === 'string') {
                        features = JSON.parse(transfer.features);
                    } else if (Array.isArray(transfer.features)) {
                        features = transfer.features;
                    }
                } catch (e) {
                    console.error('Error al parsear características:', e);
                }
                
                // Actualizar input hidden
                const selectedFeaturesInput = document.getElementById('selectedFeatures');
                if (selectedFeaturesInput) {
                    selectedFeaturesInput.value = JSON.stringify(features);
                }
                
                // Actualizar botones
                updateFeatureButtons(features);
            }
            
            // Cambiar el título del modal
            document.querySelector('#addTransferModal .modal-title').innerHTML = '<i class="fas fa-shuttle-van me-2"></i>Editar Traslado';
            // Cambiar el botón de guardar
            const saveButton = document.querySelector('#addTransferModal .btn-primary');
            saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            saveButton.onclick = () => saveTransfer(id);
            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('addTransferModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del traslado');
        });
}

function deleteTransfer(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este traslado?')) {
        fetch(`${APP_URL}/admin/transfers/api/${id}`, {
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
                alert(data.message || 'Error al eliminar el traslado');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el traslado');
        });
    }
}

function saveTransfer(id = null) {
    const form = document.getElementById('addTransferForm');
    
    // Validar campos requeridos
    if (!form.name.value || !form.type.value || !form.capacity.value || !form.price.value) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    // Preparar datos del formulario
    const formData = {
        name: form.name.value,
        type: form.type.value,
        capacity: form.capacity.value,
        price: form.price.value,
        available: form.available.value,
        location_id: form.location_id.value,
        images: document.getElementById('transferImageUrls').value, // Usar las URLs de las imágenes subidas
        description: form.description.value,
        features: document.getElementById('selectedFeatures').value // Usar características del input hidden
    };
    
    console.log('Enviando datos del traslado:', formData);
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${APP_URL}/admin/transfers/api/${id}` : `${APP_URL}/admin/transfers/api`;
    
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
            alert('Traslado guardado exitosamente');
            // Cerrar modal
            const modal = document.getElementById('addTransferModal');
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
function handleTransferImageChange(event) {
    const files = Array.from(event.target.files);
    if (files.length > 10) {
        alert('Máximo 10 imágenes permitidas');
        return;
    }
    
    // Limpiar previsualizaciones anteriores
    document.getElementById('transferImagePreviews').innerHTML = '';
    document.getElementById('transferImageUrls').value = '[]';
    
    // Subir cada archivo
    files.forEach((file, index) => {
        uploadTransferImage(file, index);
    });
}

function uploadTransferImage(file, index) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'transfer');
    
    console.log('🔄 Subiendo imagen de transfer...', file.name);
    
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
            addTransferImageToPreview(data.url, index);
            addTransferImageToUrls(data.url);
            console.log('✅ Imagen de transfer subida exitosamente:', data.url);
            } else {
            throw new Error(data.message || 'Error desconocido al subir imagen');
            }
        })
        .catch(error => {
        console.error('❌ Error al subir imagen de transfer:', error);
        alert(`Error al subir imagen ${index + 1}: ${error.message}`);
    });
}

function addTransferImageToPreview(url, index) {
    const previewsContainer = document.getElementById('transferImagePreviews');
    const previewHtml = `
        <div class="col-md-3 col-sm-4 col-6" id="preview-${index}">
            <div class="position-relative">
                <img src="${url}" alt="Preview ${index + 1}" class="img-thumbnail w-100" style="height: 100px; object-fit: cover;">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                        onclick="removeTransferImage(${index}, '${url}')" title="Eliminar imagen">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    previewsContainer.insertAdjacentHTML('beforeend', previewHtml);
}

function addTransferImageToUrls(url) {
    const urlsInput = document.getElementById('transferImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls.push(url);
    urlsInput.value = JSON.stringify(urls);
}

function removeTransferImage(index, url) {
    // Remover de previsualizaciones
    const previewElement = document.getElementById(`preview-${index}`);
    if (previewElement) {
        previewElement.remove();
    }
    
    // Remover de URLs
    const urlsInput = document.getElementById('transferImageUrls');
    let urls = JSON.parse(urlsInput.value);
    urls = urls.filter(u => u !== url);
    urlsInput.value = JSON.stringify(urls);
}

function previewTransferImages() {
    const urls = JSON.parse(document.getElementById('transferImageUrls').value);
    if (urls.length > 0) {
        urls.forEach((url, index) => {
            addTransferImageToPreview(url, index);
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

// Aplicar filtros en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (typeFilter) typeFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    
    // Event listener para cambio de archivo de imagen
    const transferImageFiles = document.getElementById('transferImageFiles');
    if (transferImageFiles) {
        transferImageFiles.addEventListener('change', handleTransferImageChange);
    }
    
    // Event listener para cuando se abre el modal
    const addTransferModal = document.getElementById('addTransferModal');
    if (addTransferModal) {
        addTransferModal.addEventListener('shown.bs.modal', function () {
            console.log('🚀 Modal de transfer abierto, cargando datos...');
            loadAvailableFeatures();
            loadAvailableTypes();
        });
    }

    // Cargar tipos dinámicos en filtro y select del formulario al iniciar
    fetch(`${APP_URL}/admin/types/api?category=transfer`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        // Filtro superior
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

        // Select del modal
        const sel = document.getElementById('transferTypeSelect');
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

// Cargar características disponibles desde la base de datos
async function loadAvailableFeatures() {
    console.log('🔄 Cargando características disponibles...');
    try {
        // Obtener características de transfer específicamente
        const response = await fetch(`${APP_URL}/admin/features/api?category=transfer`);
        console.log('📡 Respuesta de features API:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 Datos de características:', data);
        
        if (data.success && data.data && data.data.length > 0) {
            console.log('🎯 Características de transfer encontradas:', data.data);
            displayFeatures(data.data);
        } else {
            console.log('⚠️ No hay características de transfer en la DB, usando por defecto');
            displayDefaultFeatures();
        }
    } catch (error) {
        console.error('❌ Error al cargar características:', error);
        console.log('🔄 Usando características por defecto debido al error');
        displayDefaultFeatures();
    }
}

// Cargar tipos disponibles desde la base de datos
async function loadAvailableTypes() {
    console.log('🔄 Cargando tipos disponibles...');
    try {
        const response = await fetch(`${APP_URL}/admin/types/api?category=transfer`);
        console.log('📡 Respuesta de types API:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📊 Datos de tipos:', data);
        
        if (data.success && data.data && data.data.length > 0) {
            console.log('🎯 Tipos de transfer encontrados:', data.data);
            displayTypes(data.data);
        } else {
            console.log('⚠️ No hay tipos de transfer en la DB, usando por defecto');
        }
    } catch (error) {
        console.error('❌ Error al cargar tipos:', error);
    }
}

// Mostrar tipos disponibles
function displayTypes(types) {
    console.log('🎨 Mostrando tipos:', types);
    const select = document.getElementById('transferTypeSelect');
    console.log('📦 Select encontrado:', select);
    
    if (!select) {
        console.error('❌ No se encontró el select transferTypeSelect');
        return;
    }
    
    // Limpiar opciones existentes (excepto la primera)
    select.innerHTML = '<option value="">Seleccionar tipo</option>';
    console.log('🧹 Select limpiado');
    
    types.forEach((type, index) => {
        console.log(`🔧 Procesando tipo ${index + 1}:`, type);
        const option = document.createElement('option');
        option.value = type.name.toLowerCase().replace(/\s+/g, '_');
        option.textContent = type.name;
        select.appendChild(option);
        console.log(`✅ Tipo ${type.name} agregado al select`);
    });
    
    console.log('✅ Tipos cargados desde la base de datos');
}

// Mostrar características disponibles
function displayFeatures(features) {
    console.log('🎨 Mostrando características:', features);
    const container = document.getElementById('featuresContainer');
    console.log('📦 Container encontrado:', container);
    
    if (!container) {
        console.error('❌ No se encontró el container featuresContainer');
        return;
    }
    
    container.innerHTML = '';
    console.log('🧹 Container limpiado');
    
    features.forEach((feature, index) => {
        console.log(`🔧 Procesando característica ${index + 1}:`, feature);
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <button type="button" class="btn btn-outline-primary feature-btn" data-feature="${feature.name}">
                <i class="${feature.icon || 'fas fa-plus'} me-1"></i>${feature.name}
            </button>
        `;
        container.appendChild(col);
        console.log(`✅ Característica ${feature.name} agregada al DOM`);
    });
    
    // Agregar event listeners a los botones
    addFeatureButtonListeners();
    
    console.log('✅ Características cargadas desde la base de datos');
}

// Mostrar características por defecto si no hay en la DB
function displayDefaultFeatures() {
    console.log('🔄 Mostrando características por defecto...');
    const container = document.getElementById('featuresContainer');
    console.log('📦 Container encontrado:', container);
    
    if (!container) {
        console.error('❌ No se encontró el container featuresContainer');
        return;
    }
    
    const defaultFeatures = [
        { name: 'WiFi Gratuito', icon: 'fas fa-wifi' },
        { name: 'Bebidas Incluidas', icon: 'fas fa-coffee' },
        { name: 'Conductor Profesional', icon: 'fas fa-user-tie' },
        { name: 'Aire Acondicionado', icon: 'fas fa-snowflake' },
        { name: 'Asientos Cómodos', icon: 'fas fa-chair' },
        { name: 'Equipaje Incluido', icon: 'fas fa-suitcase' }
    ];
    
    console.log('🎯 Características por defecto:', defaultFeatures);
    
    container.innerHTML = '';
    console.log('🧹 Container limpiado');
    
    defaultFeatures.forEach((feature, index) => {
        console.log(`🔧 Procesando característica por defecto ${index + 1}:`, feature);
        const col = document.createElement('div');
        col.className = 'col-md-4';
        col.innerHTML = `
            <button type="button" class="btn btn-outline-primary feature-btn" data-feature="${feature.name}">
                <i class="${feature.icon} me-1"></i>${feature.name}
            </button>
        `;
        container.appendChild(col);
        console.log(`✅ Característica por defecto ${feature.name} agregada al DOM`);
    });
    
    // Agregar event listeners a los botones
    addFeatureButtonListeners();
    
    console.log('🔄 Características por defecto mostradas');
}

// Agregar event listeners a los botones de características
function addFeatureButtonListeners() {
    const featureBtns = document.querySelectorAll('.feature-btn');
    featureBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const feature = this.getAttribute('data-feature');
            
            if (this.classList.contains('btn-primary')) {
                // Remover característica
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-primary');
                this.querySelector('i').classList.remove('fa-check');
                this.querySelector('i').classList.add('fa-plus');
            } else {
                // Agregar característica
                this.classList.remove('btn-outline-primary');
                this.classList.add('btn-primary');
                this.querySelector('i').classList.remove('fa-plus');
                this.querySelector('i').classList.add('fa-check');
            }
            
            // Actualizar input hidden
            updateSelectedFeatures();
        });
    });
}

// Actualizar el input hidden con las características seleccionadas
function updateSelectedFeatures() {
    const selectedFeatures = [];
    const featureBtns = document.querySelectorAll('.feature-btn.btn-primary');
    
    featureBtns.forEach(btn => {
        selectedFeatures.push(btn.getAttribute('data-feature'));
    });
    
    const selectedFeaturesInput = document.getElementById('selectedFeatures');
    if (selectedFeaturesInput) {
        selectedFeaturesInput.value = JSON.stringify(selectedFeatures);
    }
    
    // Actualizar contador
    updateFeatureCounter(selectedFeatures.length);
    
    console.log('📝 Características seleccionadas:', selectedFeatures);
}

// Actualizar contador de características
function updateFeatureCounter(count) {
    const counter = document.getElementById('featureCounter');
    if (counter) {
        counter.textContent = count;
        counter.className = count > 0 ? 'badge bg-primary ms-2' : 'badge bg-secondary ms-2';
    }
}

// Limpiar todas las características seleccionadas
function clearAllFeatures() {
    const selectedFeaturesInput = document.getElementById('selectedFeatures');
    if (selectedFeaturesInput) {
        selectedFeaturesInput.value = '[]';
    }
    
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

// Seleccionar características comunes para transfers
function selectCommonFeatures() {
    const commonFeatures = ['WiFi Gratuito', 'Conductor Profesional', 'Aire Acondicionado'];
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

// Actualizar botones de características basado en la selección
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

// Mostrar modal para agregar nueva característica
function showNewFeatureModal() {
    // Crear modal si no existe
    let modal = document.getElementById('newTransferFeatureModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'newTransferFeatureModal';
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
                            <input type="text" class="form-control" id="newTransferFeatureName" placeholder="Ej: Wi-Fi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" id="transferFeatureCategorySelect">
                                <option value="">Cargando...</option>
                            </select>
                            <small class="text-muted">Por defecto: Traslado</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="saveNewTransferFeatureBtn">
                            <i class="fas fa-save me-1"></i>Guardar
                        </button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modal);
    }

    // Cargar categorías dinámicas
    fetch(`${APP_URL}/admin/service-categories/api`).then(r=>r.json()).then(data=>{
        const sel = document.getElementById('transferFeatureCategorySelect');
        if (!sel) return;
        sel.innerHTML = '';
        const list = (data && data.success && Array.isArray(data.data)) ? data.data : [
            { key: 'transfer', label: 'Traslado' }
        ];
        list.forEach(it => {
            const opt = document.createElement('option');
            opt.value = it.key;
            opt.textContent = it.label;
            sel.appendChild(opt);
        });
        // Seleccionar por defecto 'transfer' si existe
        sel.value = 'transfer';
    }).catch(()=>{
        const sel = document.getElementById('transferFeatureCategorySelect');
        if (sel) { sel.innerHTML = '<option value="transfer">Traslado</option>'; }
    });

    // Guardar
    const saveBtn = document.getElementById('saveNewTransferFeatureBtn');
    if (saveBtn) {
        saveBtn.onclick = () => {
            const name = (document.getElementById('newTransferFeatureName').value || '').trim();
            const category = document.getElementById('transferFeatureCategorySelect').value || 'transfer';
            if (!name) { alert('Ingresa un nombre'); return; }
            fetch(`${APP_URL}/admin/features/api`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, category })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const m = bootstrap.Modal.getOrCreateInstance(document.getElementById('newTransferFeatureModal'));
                    m.hide();
                    loadAvailableFeatures();
                } else {
                    alert(data.message || 'Error al crear característica');
                }
            })
            .catch(err => alert('Error del servidor: ' + err.message));
        };
    }

    bootstrap.Modal.getOrCreateInstance(modal).show();
}

// Mostrar modal para agregar nuevo tipo de transfer
async function showNewTypeModal() {
    const typeName = prompt('Ingresa el nombre del nuevo tipo de transfer:');
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
                    category: 'transfer'
                })
            });
            
            const data = await response.json();
            
        if (data.success) {
                // Agregar la nueva opción al select
                const select = document.getElementById('transferTypeSelect');
                const newOption = document.createElement('option');
                newOption.value = typeName.toLowerCase().replace(/\s+/g, '_');
                newOption.textContent = typeName;
                select.appendChild(newOption);
                
                // Seleccionar la nueva opción
                select.value = newOption.value;
                
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

function showManageFeaturesModalTransfer() {
    fetch(`${APP_URL}/admin/features/api?category=transfer`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando características');
        const cont = document.getElementById('transferFeaturesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(f=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class=\"form-check-input me-2\" type=\"checkbox\" value=\"${f.id}\"><i class=\"${f.icon||'fas fa-check'} me-1\"></i><span>${f.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageTransferFeaturesModal')).show();
    });
}

function showManageTypesModalTransfer() {
    fetch(`${APP_URL}/admin/types/api?category=transfer`).then(r=>r.json()).then(data=>{
        if (!data.success) return alert('Error cargando tipos');
        const cont = document.getElementById('transferTypesList');
        cont.innerHTML = '';
        (data.data||[]).forEach(t=>{
            const item = document.createElement('label');
            item.className = 'list-group-item d-flex align-items-center gap-2';
            item.innerHTML = `<input class=\"form-check-input me-2\" type=\"checkbox\" value=\"${t.id}\"><span>${t.name}</span>`;
            cont.appendChild(item);
        });
        new bootstrap.Modal(document.getElementById('manageTransferTypesModal')).show();
    });
}

function deleteSelectedTransferTypes() {
    const boxes = document.querySelectorAll('#manageTransferTypesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos un tipo');
    if (!confirm(`¿Eliminar ${boxes.length} tipo(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/types/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar un tipo');
        }
        reloadTransferTypes();
        showManageTypesModalTransfer();
    })();
}

function deleteSelectedTransferFeatures() {
    const boxes = document.querySelectorAll('#manageTransferFeaturesModal input[type="checkbox"]:checked');
    if (!boxes.length) return alert('Selecciona al menos una característica');
    if (!confirm(`¿Eliminar ${boxes.length} característica(s)? Esta acción no se puede deshacer.`)) return;
    (async () => {
        for (const cb of boxes) {
            const res = await fetch(`${APP_URL}/admin/features/api/${cb.value}`, { method: 'DELETE' });
            const j = await res.json();
            if (!j.success) alert(j.message || 'No se pudo eliminar una característica');
        }
        loadAvailableFeatures();
        showManageFeaturesModalTransfer();
    })();
}

function reloadTransferTypes(selectValue) {
    fetch(`${APP_URL}/admin/types/api?category=transfer`).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        const sel = document.getElementById('transferTypeSelect');
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

// Mostrar modal para agregar nueva ubicación
function showNewLocationModal() {
    const locationName = prompt('Ingresa el nombre de la nueva ubicación:');
    if (locationName && locationName.trim()) {
        // Aquí podrías hacer una llamada a la API para crear la ubicación
        console.log('Nueva ubicación:', locationName.trim());
        alert('Función de agregar nueva ubicación en desarrollo');
    }
}
</script>