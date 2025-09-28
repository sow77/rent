<?php
// views/admin/reservations.php - Vista de gestión de reservas
if (!isset($reservations)) {
    $reservations = [];
}

$pageTitle = 'Gestión de Reservas';
$currentPage = 'reservations';

// Capturar el contenido en un buffer para el layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Gestión de Reservas</h1>
    <div>
        <button class="btn btn-success me-2" onclick="exportReservations()">
            <i class="fas fa-download me-2"></i>Exportar
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReservationModal">
            <i class="fas fa-plus me-2"></i>Nueva Reserva
        </button>
    </div>
</div>

<!-- Search and Filter -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Búsqueda y Filtros</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-lg-4 col-md-12">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar reservas..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" id="statusFilter">
                    <option value="">Todos los estados</option>
                    <option value="confirmada">Confirmada</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="cancelada">Cancelada</option>
                    <option value="completada">Completada</option>
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <select class="form-select" id="typeFilter">
                    <option value="">Todos los tipos</option>
                    <option value="vehicle">Vehículo</option>
                    <option value="boat">Barco</option>
                    <option value="transfer">Traslado</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-12">
                <button class="btn btn-outline-primary w-100" onclick="applyFilters()">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reservations Table -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Lista de Reservas</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($reservations)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-calendar-check fa-3x mb-3"></i><br>
            <h5>No hay reservas registradas</h5>
            <p class="text-muted">Comienza agregando tu primera reserva</p>
        </div>
        <?php else: ?>
        <!-- Desktop Table -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Servicio</th>
                            <th>Fechas</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td class="text-nowrap">
                                <small class="text-muted">#<?php echo substr(htmlspecialchars($reservation['id']), 0, 8); ?>...</small>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($reservation['user_name'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($reservation['user_email'] ?? ''); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($reservation['entity_name'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo ucfirst(htmlspecialchars($reservation['entity_type'] ?? 'N/A')); ?></small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($reservation['pickup_date'] ?? 'N/A'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($reservation['return_date'] ?? 'N/A'); ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong class="text-success">€<?php echo number_format($reservation['total_cost'] ?? 0, 2); ?></strong>
                            </td>
                            <td class="text-center">
                                <?php
                                $status = $reservation['status'] ?? 'pendiente';
                                $statusColors = [
                                    'confirmada' => 'success',
                                    'pendiente' => 'warning',
                                    'cancelada' => 'danger',
                                    'completada' => 'info'
                                ];
                                $statusIcons = [
                                    'confirmada' => 'check-circle',
                                    'pendiente' => 'clock',
                                    'cancelada' => 'times-circle',
                                    'completada' => 'check-double'
                                ];
                                ?>
                                <span class="badge bg-<?php echo $statusColors[$status]; ?>">
                                    <i class="fas fa-<?php echo $statusIcons[$status]; ?> me-1"></i>
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-info" onclick="showReservationDetails('<?php echo $reservation['id']; ?>', true)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editReservation('<?php echo $reservation['id']; ?>')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReservation('<?php echo $reservation['id']; ?>')" title="Eliminar">
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
            <?php foreach ($reservations as $reservation): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px; font-size: 20px; border-radius: 8px;">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">Reserva #<?php echo substr(htmlspecialchars($reservation['id']), 0, 8); ?>...</h6>
                            <small class="text-muted"><?php echo htmlspecialchars($reservation['user_name'] ?? 'N/A'); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="showReservationDetails('<?php echo $reservation['id']; ?>', true)">
                                    <i class="fas fa-eye me-2"></i>Ver Detalles
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="editReservation('<?php echo $reservation['id']; ?>')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteReservation('<?php echo $reservation['id']; ?>')">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Servicio:</small>
                            <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($reservation['entity_type'] ?? 'N/A')); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Estado:</small>
                            <?php
                            $status = $reservation['status'] ?? 'pendiente';
                            $statusColors = [
                                'confirmada' => 'success',
                                'pendiente' => 'warning',
                                'cancelada' => 'danger',
                                'completada' => 'info'
                            ];
                            $statusIcons = [
                                'confirmada' => 'check-circle',
                                'pendiente' => 'clock',
                                'cancelada' => 'times-circle',
                                'completada' => 'check-double'
                            ];
                            ?>
                            <span class="badge bg-<?php echo $statusColors[$status]; ?>">
                                <i class="fas fa-<?php echo $statusIcons[$status]; ?> me-1"></i>
                                <?php echo ucfirst($status); ?>
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Fecha Inicio:</small>
                            <span><?php echo htmlspecialchars($reservation['pickup_date'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Total:</small>
                            <span class="text-success fw-bold">€<?php echo number_format($reservation['total_cost'] ?? 0, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Reservation Modal -->
<div class="modal fade" id="addReservationModal" tabindex="-1" aria-labelledby="addReservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReservationModalLabel"><i class="fas fa-calendar-plus me-2"></i>Nueva Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addReservationForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-user me-1"></i>Usuario</label>
                            <select class="form-select" name="user_id" required>
                                <option value="">Seleccionar usuario</option>
                                <!-- Opciones de usuarios se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-list me-1"></i>Tipo de Entidad</label>
                            <select class="form-select" name="entity_type" id="entityTypeSelect" required onchange="loadEntities()">
                                <option value="">Seleccionar tipo</option>
                                <option value="vehicle">Vehículo</option>
                                <option value="boat">Barco</option>
                                <option value="transfer">Traslado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-car me-1"></i>Entidad</label>
                            <select class="form-select" name="entity_id" id="entitySelect" required>
                                <option value="">Seleccionar entidad</option>
                                <!-- Opciones se cargarán dinámicamente según el tipo -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-calendar me-1"></i>Fecha de Inicio</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-calendar-check me-1"></i>Fecha de Fin</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveReservation()">
                    <i class="fas fa-save me-1"></i>Guardar
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

function editReservation(id) {
    // Obtener datos de la reserva
    fetch(`${APP_URL}/admin/reservations/api/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al cargar los datos de la reserva');
                return;
            }
            const reservation = data.data;
            
            // Llenar el formulario con los datos de la reserva
            const form = document.getElementById('addReservationForm');
            form.user_id.value = reservation.user_id;
            form.entity_type.value = reservation.entity_type;
            form.entity_id.value = reservation.entity_id;
            form.start_date.value = reservation.pickup_date;
            form.end_date.value = reservation.return_date;
            form.notes.value = reservation.notes || '';
            
            // Cambiar el título del modal
            document.querySelector('#addReservationModal .modal-title').innerHTML = '<i class="fas fa-calendar-edit me-2"></i>Editar Reserva';
            // Cambiar el botón de guardar
            const saveButton = document.querySelector('#addReservationModal .btn-primary');
            saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            saveButton.onclick = () => saveReservation(id);
            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('addReservationModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos de la reserva');
        });
}

function deleteReservation(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta reserva?')) {
        fetch(`${APP_URL}/admin/reservations/api/${id}`, {
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
                alert(data.message || 'Error al eliminar la reserva');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la reserva');
        });
    }
}

function saveReservation(id = null) {
    const form = document.getElementById('addReservationForm');
    const formData = {
        user_id: form.user_id.value,
        entity_type: form.entity_type.value,
        entity_id: form.entity_id.value,
        pickup_date: form.start_date.value,
        return_date: form.end_date.value,
        notes: form.notes.value
    };
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${APP_URL}/admin/reservations/api/${id}` : `${APP_URL}/admin/reservations/api`;
    
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
            window.location.reload();
        } else {
            alert(data.message || 'Error al guardar la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al guardar la reserva');
    });
}

function exportReservations() {
    // Implementar exportación de reservas
    console.log('Exportar reservas');
}

function filterReservations() {
    // Implementar filtrado de reservas
    console.log('Filtrar reservas');
}

// Funciones de filtrado y búsqueda
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    applyFilters();
}

function applyFilters() {
    const q = document.getElementById('searchInput').value.trim();
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (type) params.set('type', type);
    if (status) params.set('status', status);
    const url = params.toString() ? `${APP_URL}/admin/reservations/api?${params}` : `${APP_URL}/admin/reservations/api`;
    fetch(url).then(r=>r.json()).then(data=>{
        if (!data.success) return;
        updateReservationsTable(data.data);
        updateReservationsCards(data.data);
    });
}

function updateReservationsTable(reservations) {
    const tbody = document.querySelector('tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    reservations.forEach(r => {
        const status = (r.status||'pendiente');
        const statusColors = { confirmada:'success', pendiente:'warning', cancelada:'danger', completada:'info' };
        const statusIcons = { confirmada:'check-circle', pendiente:'clock', cancelada:'times-circle', completada:'check-double' };
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-nowrap"><small class="text-muted">#${String(r.id).substring(0,8)}...</small></td>
            <td><div><strong>${r.user_name||'N/A'}</strong><br><small class="text-muted">${r.user_email||''}</small></div></td>
            <td><div><strong>${r.entity_name||'N/A'}</strong><br><small class="text-muted">${(r.entity_type||'').charAt(0).toUpperCase()+(r.entity_type||'').slice(1)}</small></div></td>
            <td><div><strong>${r.pickup_date||'N/A'}</strong><br><small class="text-muted">${r.return_date||'N/A'}</small></div></td>
            <td class="text-center"><strong class="text-success">€${Number(r.total_cost||0).toFixed(2)}</strong></td>
            <td class="text-center"><span class="badge bg-${statusColors[status]}" ><i class="fas fa-${statusIcons[status]} me-1"></i>${status.charAt(0).toUpperCase()+status.slice(1)}</span></td>
            <td class="text-center"><div class="btn-group" role="group">
                <button class="btn btn-sm btn-outline-info" onclick="showReservationDetails('${r.id}', true)" title="Ver Detalles"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-outline-primary" onclick="editReservation('${r.id}')" title="Editar"><i class="fas fa-edit"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteReservation('${r.id}')" title="Eliminar"><i class="fas fa-trash"></i></button>
            </div></td>`;
        tbody.appendChild(tr);
    });
}

function updateReservationsCards(reservations) {
    const container = document.querySelector('.d-lg-none');
    if (!container) return;
    container.innerHTML = '';
    reservations.forEach(r => {
        const status = (r.status||'pendiente');
        const statusColors = { confirmada:'success', pendiente:'warning', cancelada:'danger', completada:'info' };
        const statusIcons = { confirmada:'check-circle', pendiente:'clock', cancelada:'times-circle', completada:'check-double' };
        const card = document.createElement('div');
        card.className = 'card mb-3 border-0 shadow-sm';
        card.innerHTML = `
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3"><div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 20px; border-radius: 8px;"><i class="fas fa-calendar-check"></i></div></div>
                    <div class="flex-grow-1"><h6 class="mb-1 fw-bold">Reserva #${String(r.id).substring(0,8)}...</h6><small class="text-muted">${r.user_name||'N/A'}</small></div>
                </div>
                <div class="row g-2">
                    <div class="col-6"><small class="text-muted d-block">Servicio:</small><span class="badge bg-info">${(r.entity_type||'').charAt(0).toUpperCase()+(r.entity_type||'').slice(1)}</span></div>
                    <div class="col-6"><small class="text-muted d-block">Estado:</small><span class="badge bg-${statusColors[status]}"><i class="fas fa-${statusIcons[status]} me-1"></i>${status.charAt(0).toUpperCase()+status.slice(1)}</span></div>
                </div>
            </div>`;
        container.appendChild(card);
    });
}

// Cargar usuarios y entidades al abrir el modal
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (typeFilter) typeFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    
    // Cargar usuarios cuando se abre el modal
    const addReservationModal = document.getElementById('addReservationModal');
    if (addReservationModal) {
        addReservationModal.addEventListener('shown.bs.modal', function () {
            loadUsers();
        });
    }
});

// Cargar usuarios disponibles
async function loadUsers() {
    try {
        const response = await fetch(`${APP_URL}/admin/users/api`);
        const data = await response.json();
        if (data.success) {
            const select = document.querySelector('select[name="user_id"]');
            select.innerHTML = '<option value="">Seleccionar usuario</option>';
            data.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar usuarios:', error);
    }
}

// Cargar entidades según el tipo seleccionado
async function loadEntities() {
    const entityType = document.getElementById('entityTypeSelect').value;
    const entitySelect = document.getElementById('entitySelect');
    
    if (!entityType) {
        entitySelect.innerHTML = '<option value="">Seleccionar entidad</option>';
        return;
    }
    
    try {
        const response = await fetch(`${APP_URL}/admin/${entityType}s/api`);
        const data = await response.json();
        if (data.success) {
            entitySelect.innerHTML = '<option value="">Seleccionar entidad</option>';
            data.data.forEach(entity => {
                const option = document.createElement('option');
                option.value = entity.id;
                option.textContent = entity.name || `${entity.brand} ${entity.model}`;
                entitySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar entidades:', error);
    }
}
</script>

<!-- Script para detalles de reserva -->
<script src="<?php echo APP_URL; ?>/public/js/reservation-details.js"></script> 