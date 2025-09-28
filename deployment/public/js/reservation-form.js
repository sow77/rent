/**
 * Sistema de formulario de reserva para el frontend
 */

class ReservationFormManager {
    constructor() {
        this.modalId = 'reservationFormModal';
        this.currentEntity = null;
        this.currentEntityType = null;
        this.init();
    }

    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.createModal();
                this.bindEvents();
                this.restoreFormData();
            });
        } else {
            this.createModal();
            this.bindEvents();
            this.restoreFormData();
        }
    }

    createModal() {
        // Eliminar modal existente si existe
        const existingModal = document.getElementById(this.modalId);
        if (existingModal) {
            existingModal.remove();
        }

        const modalHTML = `
            <div class="modal fade" id="${this.modalId}" tabindex="-1" aria-labelledby="reservationFormModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reservationFormModalLabel">
                                <i class="fas fa-calendar-plus me-2"></i>Reservar Servicio
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="reservationForm">
                                <div class="row g-3">
                                    <!-- Información del Servicio -->
                                    <div class="col-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Información del Servicio</h6>
                                                <div id="serviceInfo" class="d-flex align-items-center">
                                                    <div class="bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                                         style="width: 60px; height: 60px; border-radius: 8px;">
                                                        <i class="fas fa-car fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1" id="serviceName">Nombre del Servicio</h6>
                                                        <small class="text-muted" id="serviceType">Tipo de Servicio</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fechas -->
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
                                        <input type="email" class="form-control" name="email" id="email" required>
                                        <div class="invalid-feedback">Por favor ingresa tu email</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-phone me-1"></i>Teléfono</label>
                                        <input type="tel" class="form-control" name="phone" id="phone" required>
                                        <div class="invalid-feedback">Por favor ingresa tu teléfono</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-calendar me-1"></i>Fecha de Inicio</label>
                                        <input type="date" class="form-control" name="pickup_date" id="pickupDate" required>
                                        <div class="invalid-feedback">Por favor selecciona una fecha de inicio</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"><i class="fas fa-calendar-check me-1"></i>Fecha de Fin</label>
                                        <input type="date" class="form-control" name="return_date" id="returnDate" required>
                                        <div class="invalid-feedback">Por favor selecciona una fecha de fin</div>
                                    </div>

                                    <!-- Ubicaciones (solo para transfers) -->
                                    <div class="col-md-6" id="pickupLocationDiv" style="display: none;">
                                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Ubicación de Recogida</label>
                                        <select class="form-select" name="pickup_location_id" id="pickupLocation">
                                            <option value="">Seleccionar ubicación</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="returnLocationDiv" style="display: none;">
                                        <label class="form-label"><i class="fas fa-map-marker-alt me-1"></i>Ubicación de Devolución</label>
                                        <select class="form-select" name="return_location_id" id="returnLocation">
                                            <option value="">Seleccionar ubicación</option>
                                        </select>
                                    </div>

                                    <!-- Notas -->
                                    <div class="col-12">
                                        <label class="form-label"><i class="fas fa-sticky-note me-1"></i>Notas Adicionales</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Comentarios especiales, solicitudes adicionales, etc."></textarea>
                                    </div>

                                    <!-- Resumen de Costo -->
                                    <div class="col-12">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title"><i class="fas fa-calculator me-2"></i>Resumen de Costo</h6>
                                                <div class="display-6 fw-bold" id="totalCost">€0.00</div>
                                                <small>Precio total estimado</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" id="submitReservationBtn">
                                <i class="fas fa-check me-1"></i>Confirmar Reserva
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    bindEvents() {
        // Eventos para el formulario
        document.addEventListener('change', (e) => {
            if (e.target.id === 'pickupDate' || e.target.id === 'returnDate') {
                this.validateDates();
                this.calculateCost();
            }
        });

        // Evento para enviar la reserva
        document.addEventListener('click', (e) => {
            if (e.target.id === 'submitReservationBtn') {
                this.submitReservation();
            }
        });
    }

    showReservationForm(entityId, entityType, entityData) {
        this.currentEntity = entityData;
        this.currentEntityType = entityType;
        
        // Crear el modal si no existe
        this.createModal();
        
        // Actualizar la información del servicio
        this.updateServiceInfo(entityData, entityType);
        
        // Mostrar/ocultar campos según el tipo
        this.toggleLocationFields(entityType);
        
        // Cargar ubicaciones si es necesario
        if (entityType === 'transfer') {
            this.loadLocations();
        }
        
        // Establecer fecha mínima
        this.setMinimumDate();
        
        // Mostrar el modal
        const modalElement = document.getElementById(this.modalId);
        
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal element not found:', this.modalId);
        }
    }

    updateServiceInfo(entityData, entityType) {
        const serviceName = document.getElementById('serviceName');
        const serviceType = document.getElementById('serviceType');
        const serviceIcon = document.querySelector('#serviceInfo .fas');
        
        // Verificar que los elementos existan
        if (!serviceName || !serviceType || !serviceIcon) {
            console.error('Modal elements not found for updateServiceInfo');
            return;
        }
        
        // Actualizar nombre
        if (entityType === 'vehicle') {
            serviceName.textContent = `${entityData.brand} ${entityData.model}`;
            serviceType.textContent = 'Vehículo';
            serviceIcon.className = 'fas fa-car fs-4';
        } else if (entityType === 'boat') {
            serviceName.textContent = entityData.name;
            serviceType.textContent = 'Barco';
            serviceIcon.className = 'fas fa-ship fs-4';
        } else if (entityType === 'transfer') {
            serviceName.textContent = entityData.name;
            serviceType.textContent = 'Traslado';
            serviceIcon.className = 'fas fa-bus fs-4';
        }
    }

    toggleLocationFields(entityType) {
        const pickupDiv = document.getElementById('pickupLocationDiv');
        const returnDiv = document.getElementById('returnLocationDiv');
        
        // Verificar que los elementos existan
        if (!pickupDiv || !returnDiv) {
            console.error('Location fields not found for toggleLocationFields');
            return;
        }
        
        if (entityType === 'transfer') {
            pickupDiv.style.display = 'block';
            returnDiv.style.display = 'block';
        } else {
            pickupDiv.style.display = 'none';
            returnDiv.style.display = 'none';
        }
    }

    async loadLocations() {
        try {
            const response = await fetch(`${APP_URL}/admin/locations/api`);
            const data = await response.json();
            
            if (data.success) {
                const pickupSelect = document.getElementById('pickupLocation');
                const returnSelect = document.getElementById('returnLocation');
                
                // Limpiar opciones existentes
                pickupSelect.innerHTML = '<option value="">Seleccionar ubicación</option>';
                returnSelect.innerHTML = '<option value="">Seleccionar ubicación</option>';
                
                // Agregar opciones
                data.data.forEach(location => {
                    const option1 = document.createElement('option');
                    option1.value = location.id;
                    option1.textContent = location.name;
                    pickupSelect.appendChild(option1);
                    
                    const option2 = document.createElement('option');
                    option2.value = location.id;
                    option2.textContent = location.name;
                    returnSelect.appendChild(option2);
                });
            }
        } catch (error) {
            console.error('Error al cargar ubicaciones:', error);
        }
    }

    setMinimumDate() {
        const today = new Date().toISOString().split('T')[0];
        const pickupDate = document.getElementById('pickupDate');
        const returnDate = document.getElementById('returnDate');
        
        if (pickupDate) pickupDate.min = today;
        if (returnDate) returnDate.min = today;
    }

    validateDates() {
        const pickupDateEl = document.getElementById('pickupDate');
        const returnDateEl = document.getElementById('returnDate');
        
        if (!pickupDateEl || !returnDateEl) {
            return true; // Si no existen, no hay validación que hacer
        }
        
        const pickupDate = pickupDateEl.value;
        const returnDate = returnDateEl.value;
        
        if (pickupDate && returnDate) {
            const pickup = new Date(pickupDate);
            const returnD = new Date(returnDate);
            
            if (returnD <= pickup) {
                document.getElementById('returnDate').setCustomValidity('La fecha de fin debe ser posterior a la fecha de inicio');
            } else {
                document.getElementById('returnDate').setCustomValidity('');
            }
        }
    }

    calculateCost() {
        const pickupDate = document.getElementById('pickupDate').value;
        const returnDate = document.getElementById('returnDate').value;
        
        if (!pickupDate || !returnDate || !this.currentEntity) {
            document.getElementById('totalCost').textContent = '€0.00';
            return;
        }
        
        const pickup = new Date(pickupDate);
        const returnD = new Date(returnDate);
        const diffTime = Math.abs(returnD - pickup);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        let totalCost = 0;
        
        if (this.currentEntityType === 'transfer') {
            // Para transfers, el precio es fijo
            totalCost = parseFloat(this.currentEntity.price || 0);
        } else {
            // Para vehículos y barcos, precio por día
            const dailyRate = parseFloat(this.currentEntity.daily_rate || this.currentEntity.price || 0);
            totalCost = dailyRate * diffDays;
        }
        
        document.getElementById('totalCost').textContent = `€${totalCost.toFixed(2)}`;
    }

    async submitReservation() {
        const form = document.getElementById('reservationForm');
        
        // Validar formulario
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Verificar que el usuario esté autenticado
        const user = await this.getCurrentUser();
        if (!user) {
            // Solo mostrar modal de autenticación si el usuario NO está autenticado
            const email = document.getElementById('email').value;
            if (!email) {
                this.showAuthModal('Debes iniciar sesión para hacer una reserva', 'login');
                return;
            }
            
            // Verificar si el usuario está registrado pero no autenticado
            const userExists = await this.checkUserExists(email);
            if (userExists) {
                this.showAuthModal('Tu cuenta ya existe. Debes iniciar sesión para hacer una reserva', 'login');
            } else {
                this.showAuthModal('Debes registrarte para hacer una reserva', 'register');
            }
            return;
        }
        
        // Si el usuario está autenticado, usar su email automáticamente
        if (user.id && !document.getElementById('email').value) {
            // Obtener email del usuario autenticado si no está en el formulario
            try {
                const response = await fetch(`${APP_URL}/auth/current-user`);
                const data = await response.json();
                if (data.success && data.user && data.user.email) {
                    document.getElementById('email').value = data.user.email;
                }
            } catch (error) {
                console.log('No se pudo obtener el email del usuario:', error);
            }
        }
        
        // Preparar datos
        const formData = {
            user_id: user.id,
            email: document.getElementById('email').value,
            entity_type: this.currentEntityType,
            entity_id: this.currentEntity.id,
            phone: document.getElementById('phone').value,
            pickup_date: document.getElementById('pickupDate').value,
            return_date: document.getElementById('returnDate').value,
            notes: document.querySelector('textarea[name="notes"]').value
        };
        
        // Agregar ubicaciones si es transfer
        if (this.currentEntityType === 'transfer') {
            formData.pickup_location_id = document.getElementById('pickupLocation').value;
            formData.return_location_id = document.getElementById('returnLocation').value;
        }
        
        try {
            const response = await fetch(`${APP_URL}/admin/reservations/api`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById(this.modalId));
                modal.hide();
                
                // Mostrar mensaje de éxito
                this.showSuccessMessage('¡Reserva creada exitosamente!');
                
                // Limpiar formulario
                form.reset();
                form.classList.remove('was-validated');
            } else {
                throw new Error(data.message || 'Error al crear la reserva');
            }
        } catch (error) {
            console.error('Error al crear reserva:', error);
            alert('Error al crear la reserva: ' + error.message);
        }
    }

    async getCurrentUser() {
        // Primero verificar si las variables globales están disponibles (más rápido)
        const userId = (typeof currentUserId !== 'undefined' ? currentUserId : null) || window.currentUserId;
        if (userId) {
            return {
                id: userId,
                name: (typeof currentUserName !== 'undefined' ? currentUserName : null) || window.currentUserName || 'Usuario',
                email: (typeof currentUserEmail !== 'undefined' ? currentUserEmail : null) || window.currentUserEmail || '',
                role: (typeof currentUserRole !== 'undefined' ? currentUserRole : null) || window.currentUserRole || 'user'
            };
        }
        
        // Fallback a petición AJAX si las variables globales no están disponibles
        try {
            const response = await fetch(`${APP_URL}/auth/current-user`);
            const data = await response.json();
            return data.success ? data.user : null;
        } catch (error) {
            console.error('Error al obtener usuario actual:', error);
            return null;
        }
    }

    async checkUserExists(email) {
        try {
            const response = await fetch(`${APP_URL}/auth/check-user-exists`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email })
            });
            const data = await response.json();
            return data.success && data.data.exists;
        } catch (error) {
            console.error('Error al verificar usuario:', error);
            return false;
        }
    }

    showAuthModal(message, action) {
        // Preservar datos del formulario
        this.preserveFormData();
        
        // Crear URL de redirección con parámetros
        const redirectUrl = this.createRedirectUrl(action);
        
        // Mostrar modal de Bootstrap
        const modalHTML = `
            <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="authModalLabel">
                                <i class="fas fa-lock me-2"></i>Autenticación Requerida
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                ${message}
                            </div>
                            <p>¿Qué deseas hacer?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" onclick="window.location.href='${redirectUrl}'">
                                <i class="fas fa-${action === 'login' ? 'sign-in-alt' : 'user-plus'} me-1"></i>
                                ${action === 'login' ? 'Iniciar Sesión' : 'Registrarse'}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remover modal existente si existe
        const existingModal = document.getElementById('authModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Agregar modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('authModal'));
        modal.show();
        
        // Limpiar modal del DOM cuando se cierre
        document.getElementById('authModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    createRedirectUrl(action) {
        // Crear URL con parámetros de redirección
        const currentUrl = window.location.href;
        const redirectParam = encodeURIComponent(currentUrl);
        
        if (action === 'register') {
            // Para registro, redirigir a login después del registro
            return `${APP_URL}/register?redirect=${redirectParam}`;
        } else {
            // Para login, redirigir directamente
            return `${APP_URL}/login?redirect=${redirectParam}`;
        }
    }

    preserveFormData() {
        // Obtener todos los datos del formulario
        const formData = {
            entity_type: this.currentEntityType,
            entity_id: this.currentEntity.id,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            pickup_date: document.getElementById('pickupDate').value,
            return_date: document.getElementById('returnDate').value,
            notes: document.querySelector('textarea[name="notes"]').value
        };
        
        // Agregar ubicaciones si es transfer
        if (this.currentEntityType === 'transfer') {
            formData.pickup_location_id = document.getElementById('pickupLocation').value;
            formData.return_location_id = document.getElementById('returnLocation').value;
        }
        
        // Guardar en sessionStorage
        sessionStorage.setItem('reservationFormData', JSON.stringify(formData));
    }

    restoreFormData() {
        // Restaurar datos del formulario si existen
        const savedData = sessionStorage.getItem('reservationFormData');
        if (savedData) {
            try {
                const formData = JSON.parse(savedData);
                
                // Restaurar campos del formulario
                if (formData.email) {
                    document.getElementById('email').value = formData.email;
                }
                if (formData.phone) {
                    document.getElementById('phone').value = formData.phone;
                }
                if (formData.pickup_date) {
                    document.getElementById('pickupDate').value = formData.pickup_date;
                }
                if (formData.return_date) {
                    document.getElementById('returnDate').value = formData.return_date;
                }
                if (formData.notes) {
                    document.querySelector('textarea[name="notes"]').value = formData.notes;
                }
                if (formData.pickup_location_id) {
                    document.getElementById('pickupLocation').value = formData.pickup_location_id;
                }
                if (formData.return_location_id) {
                    document.getElementById('returnLocation').value = formData.return_location_id;
                }
                
                // Recalcular costo
                this.calculateCost();
                
                // Limpiar datos guardados
                sessionStorage.removeItem('reservationFormData');
                
            } catch (error) {
                console.error('Error al restaurar datos del formulario:', error);
            }
        }
    }

    showSuccessMessage(message) {
        // Crear toast de éxito
        const toastHTML = `
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Agregar al contenedor de toasts
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        // Mostrar el toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
        
        // Remover el toast después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}

// Crear instancia global
window.reservationFormManager = new ReservationFormManager();

// Función global para mostrar formulario de reserva
window.showReservationForm = function(entityId, entityType, entityData) {
    window.reservationFormManager.showReservationForm(entityId, entityType, entityData);
};
