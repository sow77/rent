<?php
// views/admin/users.php - Vista de gestión de usuarios
if (!isset($users)) {
    $users = [];
}

$pageTitle = 'Gestión de Usuarios';
$currentPage = 'users';

// Capturar el contenido en un buffer para el layout
ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h1 class="page-title">Gestión de Usuarios</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus me-2"></i>Agregar Usuario
    </button>
</div>

<!-- Search and Filter -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Búsqueda y Filtros</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-lg-6 col-md-12 mb-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar usuarios..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-lg-3 col-md-6 mb-2">
                <select class="form-select" id="roleFilter">
                    <option value="">Todos los roles</option>
                    <option value="admin">Administrador</option>
                    <option value="user">Usuario</option>
                </select>
            </div>
            <div class="col-12 col-lg-3 col-md-6 mb-2">
                <select class="form-select" id="statusFilter">
                    <option value="">Todos los estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 col-md-6 mb-2">
                <button class="btn btn-outline-primary w-100" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="content-card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Lista de Usuarios</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($users)): ?>
        <div class="text-center text-muted py-5">
            <i class="fas fa-users fa-3x mb-3"></i><br>
            <h5>No hay usuarios registrados</h5>
            <p class="text-muted">Comienza agregando tu primer usuario</p>
        </div>
        <?php else: ?>
        <!-- Desktop Table -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 120px;">ID</th>
                            <th style="width: 60px;">Avatar</th>
                            <th>Usuario</th>
                            <th>Contacto</th>
                            <th style="width: 120px;">Estado</th>
                            <th style="width: 100px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="text-nowrap">
                                <small class="text-muted">#<?php echo substr(htmlspecialchars($user['id']), 0, 8); ?>...</small>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                         alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                         class="img-thumbnail" style="width: 35px; height: 35px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 35px; height: 35px; font-size: 10px; border-radius: 4px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo ucfirst($user['role']); ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    <div class="text-break"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <div class="text-muted"><?php echo htmlspecialchars($user['phone']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-<?php echo $user['active'] ? 'success' : 'secondary'; ?>">
                                    <i class="fas fa-<?php echo $user['active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                    <?php echo $user['active'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser('<?php echo $user['id']; ?>')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('<?php echo $user['id']; ?>')" title="Eliminar">
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
            <?php foreach ($users as $user): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if (!empty($user['avatar'])): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                     alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                     class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center" 
                                     style="width: 60px; height: 60px; font-size: 16px; border-radius: 8px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($user['name']); ?></h6>
                            <small class="text-muted">ID: #<?php echo htmlspecialchars($user['id']); ?></small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="editUser('<?php echo $user['id']; ?>')">
                                    <i class="fas fa-edit me-2"></i>Editar
                                </a></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteUser('<?php echo $user['id']; ?>')">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Email:</small>
                            <span class="text-break"><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Teléfono:</small>
                            <span><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'info'; ?>">
                            <i class="fas fa-<?php echo $user['role'] === 'admin' ? 'crown' : 'user'; ?> me-1"></i>
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <span class="badge bg-<?php echo $user['active'] ? 'success' : 'secondary'; ?>">
                            <i class="fas fa-<?php echo $user['active'] ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                            <?php echo $user['active'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i>Agregar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addUserForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-user me-1"></i>Nombre</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-lock me-1"></i>Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-phone me-1"></i>Teléfono</label>
                            <input type="tel" class="form-control" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-user-tag me-1"></i>Rol</label>
                            <select class="form-select" name="role" required>
                                <option value="user">Usuario</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="fas fa-toggle-on me-1"></i>Estado</label>
                            <select class="form-select" name="active" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Dirección</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="fas fa-image me-1"></i>Avatar</label>
                            <div class="input-group">
                                <input type="file" class="form-control" name="avatar_file" accept="image/*" id="avatarFile">
                                <button class="btn btn-outline-secondary" type="button" onclick="previewAvatar()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <input type="hidden" name="avatar" id="avatarUrl">
                            <div id="avatarPreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 100px; max-height: 100px;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">
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

function editUser(id) {
    // Obtener datos del usuario
    fetch(`${APP_URL}/admin/users/api/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Error al cargar los datos del usuario');
                return;
            }
            const user = data.data;
            
            // Llenar el formulario con los datos del usuario
            const form = document.getElementById('addUserForm');
            form.name.value = user.name;
            form.email.value = user.email;
            form.password.value = ''; // No mostrar contraseña
            form.phone.value = user.phone || '';
            form.role.value = user.role;
            form.active.value = user.active;
            form.address.value = user.address || '';
            form.avatar.value = user.avatar || '';
            
            // Cambiar el título del modal
            document.querySelector('#addUserModal .modal-title').innerHTML = '<i class="fas fa-user-edit me-2"></i>Editar Usuario';
            // Cambiar el botón de guardar
            const saveButton = document.querySelector('#addUserModal .btn-primary');
            saveButton.innerHTML = '<i class="fas fa-save me-1"></i>Guardar';
            saveButton.onclick = () => saveUser(id);
            // Mostrar el modal
            new bootstrap.Modal(document.getElementById('addUserModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los datos del usuario');
        });
}

function deleteUser(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        fetch(`${APP_URL}/admin/users/api/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario eliminado exitosamente');
                // Recargar lista de usuarios en lugar de toda la página
                loadUsers();
                console.log('✅ Usuario eliminado y lista recargada');
            } else {
                alert(data.message || 'Error al eliminar el usuario');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el usuario');
        });
    }
}

function saveUser(id = null) {
    const form = document.getElementById('addUserForm');
    
    // Validar campos requeridos
    if (!form.name.value || !form.email.value || (!id && !form.password.value)) {
        alert('Por favor completa todos los campos requeridos');
        return;
    }
    
    // Preparar datos del formulario
    const formData = {
        name: form.name.value,
        email: form.email.value,
        password: form.password.value,
        phone: form.phone.value,
        role: form.role.value,
        active: form.active.value,
        address: form.address.value,
        avatar: document.getElementById('avatarUrl').value // Usar la URL del archivo subido
    };
    
    console.log('Enviando datos:', formData);
    
    const method = id ? 'PUT' : 'POST';
    const url = id ? `${APP_URL}/admin/users/api/${id}` : `${APP_URL}/admin/users/api`;
    
    console.log('🌐 APP_URL:', APP_URL);
    console.log('🚀 Enviando petición a:', url);
    console.log('📤 Método:', method);
    console.log('📊 Datos:', formData);
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        console.log('📡 Respuesta del servidor:', response.status, response.statusText);
        console.log('🔗 Headers de respuesta:', response.headers);
        return response.json();
    })
    .then(data => {
            console.log('📊 Respuesta del servidor:', data);
            if (data.success) {
                console.log('✅ Usuario guardado exitosamente, llamando a loadUsers()...');
                alert('Usuario guardado exitosamente');
                // Cerrar modal
                const modal = document.getElementById('addUserModal');
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
                // Limpiar formulario
                form.reset();
                // Recargar lista de usuarios en lugar de toda la página
                console.log('🚀 Ejecutando loadUsers()...');
                loadUsers();
                console.log('✅ Usuario guardado y lista recargada');
            } else {
                console.error('❌ Error del servidor:', data.message);
                alert('Error al guardar: ' + data.message);
            }
        })
    .catch(error => {
        console.error('Error:', error);
        alert('Error del servidor: ' + error.message);
    });
}

// Función para recargar la lista de usuarios
function loadUsers() {
    console.log('🔄 Recargando lista de usuarios...');
    console.log('📍 URL de la API:', `${APP_URL}/admin/users/api`);
    
    // Hacer petición AJAX para obtener usuarios actualizados
    fetch(`${APP_URL}/admin/users/api`)
        .then(response => {
            console.log('📡 Respuesta del servidor:', response.status, response.statusText);
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos recibidos:', data);
            if (data.success) {
                console.log('👥 Usuarios en la respuesta:', data.data);
                updateUsersTable(data.data);
                console.log('✅ Lista de usuarios actualizada');
            } else {
                console.error('❌ Error al cargar usuarios:', data.message);
            }
        })
        .catch(error => {
            console.error('❌ Error al recargar usuarios:', error);
        });
}

// Función para actualizar la tabla de usuarios
function updateUsersTable(users) {
    console.log('🔄 Actualizando tabla con usuarios:', users);
    
    const tbody = document.querySelector('tbody');
    console.log('📍 Elemento tbody encontrado:', tbody);
    
    if (!tbody) {
        console.error('❌ No se encontró el elemento tbody');
        return;
    }
    
    console.log('📊 Total de usuarios a mostrar:', users.length);
    tbody.innerHTML = '';
    
    users.forEach((user, index) => {
        console.log(`👤 Procesando usuario ${index + 1}:`, user);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-nowrap">
                <small class="text-muted">#${user.id.substring(0, 8)}...</small>
            </td>
            <td class="text-center">
                ${user.avatar ? 
                    `<img src="${user.avatar}" alt="${user.name}" class="img-thumbnail" style="width: 35px; height: 35px; object-fit: cover;">` :
                    `<div class="bg-light text-muted d-flex align-items-center justify-content-center mx-auto" style="width: 35px; height: 35px; font-size: 10px; border-radius: 4px;">
                        <i class="fas fa-user"></i>
                    </div>`
                }
            </td>
            <td>
                <div>
                    <strong>${user.name}</strong>
                    <br>
                    <small class="text-muted">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</small>
                </div>
            </td>
            <td>
                <div class="small">
                    <div class="text-break">${user.email}</div>
                    ${user.phone ? `<div class="text-muted">${user.phone}</div>` : ''}
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-${user.active == 1 ? 'success' : 'secondary'}">
                    <i class="fas fa-${user.active == 1 ? 'check-circle' : 'times-circle'} me-1"></i>
                    ${user.active == 1 ? 'Activo' : 'Inactivo'}
                </span>
            </td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-primary" onclick="editUser('${user.id}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.id}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
    
    // Actualizar también las tarjetas móviles
    updateMobileCards(users);
}

// Función para actualizar las tarjetas móviles
function updateMobileCards(users) {
    const mobileContainer = document.querySelector('.d-lg-none');
    if (!mobileContainer) return;
    
    mobileContainer.innerHTML = '';
    
    users.forEach(user => {
        const card = document.createElement('div');
        card.className = 'card mb-3 border-0 shadow-sm';
        card.innerHTML = `
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        ${user.avatar ? 
                            `<img src="${user.avatar}" alt="${user.name}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">` :
                            `<div class="bg-light text-muted d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 16px; border-radius: 8px;">
                                <i class="fas fa-user"></i>
                            </div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${user.name}</h6>
                        <p class="text-muted mb-1">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</p>
                        <small class="text-muted">#${user.id.substring(0, 8)}...</small>
                    </div>
                    <span class="badge bg-${user.active == 1 ? 'success' : 'secondary'}">
                        <i class="fas fa-${user.active == 1 ? 'check-circle' : 'times-circle'} me-1"></i>
                        ${user.active == 1 ? 'Activo' : 'Inactivo'}
                    </span>
                </div>
                <div class="mb-3">
                    <div class="small">
                        <div class="text-break"><i class="fas fa-envelope me-2"></i>${user.email}</div>
                        ${user.phone ? `<div class="text-muted"><i class="fas fa-phone me-2"></i>${user.phone}</div>` : ''}
                        ${user.address ? `<div class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>${user.address}</div>` : ''}
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="editUser('${user.id}')" title="Editar">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser('${user.id}')" title="Eliminar">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;
        mobileContainer.appendChild(card);
    });
}

// Funciones de filtrado y búsqueda
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('roleFilter').value = '';
    document.getElementById('statusFilter').value = '';
    fetchAndRenderUsers();
}

function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    // Nota: al estar conectado al backend, se re-renderiza con updateUsersTable
    // Mantengo applyFilters para compatibilidad pero no re-filtra filas existentes

    // Con backend, re-renderizamos tarjetas móviles desde updateMobileCards
}

// Funciones para manejo de archivos
function handleAvatarChange(event) {
    const file = event.target.files[0];
    if (file) {
        uploadAvatar(file);
    }
}

function uploadAvatar(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('type', 'avatar');
    
    console.log('🔄 Subiendo avatar...', file.name);
    
    fetch(`${APP_URL}/api/file-upload.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('📡 Respuesta del servidor:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text(); // Primero obtener como texto para debug
    })
    .then(text => {
        console.log('📤 Respuesta completa:', text);
        
        // Intentar parsear como JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('❌ Error al parsear JSON:', e);
            console.error('📄 Respuesta recibida:', text);
            throw new Error('Respuesta del servidor no es JSON válido');
        }
        
        if (data.success) {
            document.getElementById('avatarUrl').value = data.url;
            showAvatarPreview(data.url);
            console.log('✅ Avatar subido exitosamente:', data.url);
        } else {
            throw new Error(data.message || 'Error desconocido al subir imagen');
        }
    })
    .catch(error => {
        console.error('❌ Error al subir avatar:', error);
        alert('Error al subir imagen: ' + error.message);
    });
}

function showAvatarPreview(url) {
    const preview = document.getElementById('avatarPreview');
    const img = document.getElementById('previewImg');
    img.src = url;
    preview.style.display = 'block';
}

function previewAvatar() {
    const avatarUrl = document.getElementById('avatarUrl').value;
    if (avatarUrl) {
        showAvatarPreview(avatarUrl);
    }
}

// Aplicar filtros en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    
    if (searchInput) searchInput.addEventListener('input', debounce(fetchAndRenderUsers, 200));
    if (roleFilter) roleFilter.addEventListener('change', fetchAndRenderUsers);
    if (statusFilter) statusFilter.addEventListener('change', fetchAndRenderUsers);
    
    // Event listener para cambio de archivo de avatar
    const avatarFile = document.getElementById('avatarFile');
    if (avatarFile) {
        avatarFile.addEventListener('change', handleAvatarChange);
    }
    
    // El botón de guardar ya está configurado con onclick en el HTML
});

function buildUsersQuery() {
    const q = document.getElementById('searchInput').value.trim();
    const role = document.getElementById('roleFilter').value;
    const status = document.getElementById('statusFilter').value;
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (role) params.set('role', role);
    if (status !== '') params.set('active', status);
    return params.toString();
}

function fetchAndRenderUsers() {
    const qs = buildUsersQuery();
    const url = qs ? `${APP_URL}/admin/users/api?${qs}` : `${APP_URL}/admin/users/api`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateUsersTable(data.data);
            }
        })
        .catch(err => console.error('Error cargando usuarios:', err));
}

function debounce(fn, wait) {
    let t;
    return function(...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}
</script>