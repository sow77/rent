/**
 * Sistema de detalles de reservas para usuarios
 * Maneja la visualización de detalles de reservas en dashboard y "Mis Reservas"
 */

class UserReservationDetailsManager {
    constructor() {
        this.modalId = 'userReservationDetailsModal';
        this.currentReservation = null;
    }

    init() {
        // Crear el modal si no existe
        this.createModal();
        
        // Hacer las funciones globales disponibles
        window.viewReservation = (reservationId) => this.viewReservation(reservationId);
        window.cancelReservation = (reservationId) => this.cancelReservation(reservationId);
    }

    createModal() {
        // Verificar si el modal ya existe
        if (document.getElementById(this.modalId)) {
            return;
        }

        const modalHTML = `
            <div class="modal fade" id="${this.modalId}" tabindex="-1" aria-labelledby="userReservationDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="userReservationDetailsModalLabel">
                                <i class="fas fa-calendar-check me-2"></i>Detalles de la Reserva
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="userReservationDetailsContent">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando detalles de la reserva...</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cerrar
                            </button>
                            <button type="button" class="btn btn-danger" id="cancelReservationBtn" style="display: none;">
                                <i class="fas fa-times me-1"></i>Cancelar Reserva
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    async viewReservation(reservationId) {
        try {
            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById(this.modalId));
            modal.show();

            // Cargar datos de la reserva
            const response = await fetch(`${APP_URL}/user/reservations/api/${reservationId}`);
            const data = await response.json();

            if (data.success) {
                this.currentReservation = data.reservation;
                this.renderReservationDetails();
            } else {
                this.showError('Error al cargar los detalles de la reserva: ' + data.message);
            }
        } catch (error) {
            console.error('Error al cargar reserva:', error);
            this.showError('Error al cargar los detalles de la reserva');
        }
    }

    renderReservationDetails() {
        const reservation = this.currentReservation;
        if (!reservation) return;

        const content = `
            <div class="row g-4">
                <!-- Información del Servicio -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Servicio</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                ${reservation.entity_image ? `
                                    <img src="${APP_URL}/uploads/${reservation.entity_image}" 
                                         class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                ` : `
                                    <div class="bg-light text-muted d-flex align-items-center justify-content-center me-3" 
                                         style="width: 80px; height: 80px; border-radius: 8px;">
                                        <i class="fas fa-${this.getEntityIcon(reservation.entity_type)} fa-2x"></i>
                                    </div>
                                `}
                                <div>
                                    <h5 class="mb-1">${reservation.entity_name}</h5>
                                    <span class="badge bg-secondary">${this.capitalizeFirst(reservation.entity_type)}</span>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">ID de Reserva:</small>
                                <code>${reservation.id}</code>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">Estado:</small>
                                <span class="badge bg-${this.getStatusClass(reservation.status)}">
                                    <i class="fas fa-${this.getStatusIcon(reservation.status)} me-1"></i>
                                    ${this.capitalizeFirst(reservation.status)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fechas y Costo -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Fechas y Costo</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <strong>Fecha de Inicio:</strong>
                                </div>
                                <p class="mb-0">${this.formatDate(reservation.pickup_date)}</p>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    <strong>Fecha de Fin:</strong>
                                </div>
                                <p class="mb-0">${this.formatDate(reservation.return_date)}</p>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-info me-2"></i>
                                    <strong>Duración:</strong>
                                </div>
                                <p class="mb-0">${this.calculateDuration(reservation.pickup_date, reservation.return_date)} días</p>
                            </div>
                            
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 mb-0">Total:</span>
                                    <span class="h4 text-primary mb-0">€${parseFloat(reservation.total_cost).toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información de Contacto</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted d-block">Email:</small>
                                <span>${reservation.email || 'No especificado'}</span>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">Teléfono:</small>
                                <span>${reservation.phone || 'No especificado'}</span>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted d-block">Fecha de Creación:</small>
                                <span>${this.formatDateTime(reservation.created_at)}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubicaciones (si es transfer) -->
                ${reservation.pickup_location_name || reservation.return_location_name ? `
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Ubicaciones</h6>
                        </div>
                        <div class="card-body">
                            ${reservation.pickup_location_name ? `
                            <div class="mb-2">
                                <small class="text-muted d-block">Recogida:</small>
                                <span>${reservation.pickup_location_name}</span>
                            </div>
                            ` : ''}
                            ${reservation.return_location_name ? `
                            <div class="mb-2">
                                <small class="text-muted d-block">Devolución:</small>
                                <span>${reservation.return_location_name}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
                ` : ''}

                <!-- Notas -->
                ${reservation.notes ? `
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notas</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${reservation.notes}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        `;

        document.getElementById('userReservationDetailsContent').innerHTML = content;

        // Mostrar/ocultar botón de cancelar según el estado
        const cancelBtn = document.getElementById('cancelReservationBtn');
        if (reservation.status === 'pendiente') {
            cancelBtn.style.display = 'inline-block';
            cancelBtn.onclick = () => this.cancelReservation(reservation.id);
        } else {
            cancelBtn.style.display = 'none';
        }
    }

    async cancelReservation(reservationId) {
        if (!confirm('¿Estás seguro de que quieres cancelar esta reserva?')) {
            return;
        }

        try {
            const response = await fetch(`${APP_URL}/user/reservations/api/${reservationId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Cerrar el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById(this.modalId));
                modal.hide();

                // Mostrar mensaje de éxito
                this.showSuccessMessage('Reserva cancelada exitosamente');

                // Recargar la página para actualizar la lista
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showError('Error al cancelar la reserva: ' + data.message);
            }
        } catch (error) {
            console.error('Error al cancelar reserva:', error);
            this.showError('Error al cancelar la reserva');
        }
    }

    showError(message) {
        document.getElementById('userReservationDetailsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    showSuccessMessage(message) {
        // Crear toast de éxito
        const toastHTML = `
            <div class="toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3" 
                 role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 10000;">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', toastHTML);

        // Mostrar el toast
        const toastElement = document.querySelector('.toast:last-child');
        const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
        toast.show();

        // Limpiar el toast después de que se oculte
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    // Métodos auxiliares
    getEntityIcon(entityType) {
        const icons = {
            'vehicle': 'car',
            'boat': 'ship',
            'transfer': 'bus'
        };
        return icons[entityType] || 'question';
    }

    getStatusClass(status) {
        const classes = {
            'confirmada': 'success',
            'pendiente': 'warning',
            'cancelada': 'danger'
        };
        return classes[status] || 'secondary';
    }

    getStatusIcon(status) {
        const icons = {
            'confirmada': 'check-circle',
            'pendiente': 'clock',
            'cancelada': 'times-circle'
        };
        return icons[status] || 'question';
    }

    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    calculateDuration(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }
}

// Crear instancia global
window.userReservationDetailsManager = new UserReservationDetailsManager();

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.userReservationDetailsManager.init();
});
