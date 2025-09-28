/**
 * Sistema de detalles de reserva compartido
 * Funciona tanto en admin como en frontend
 */

class ReservationDetailsManager {
    constructor() {
        this.modalId = 'reservationDetailsModal';
        this.currentReservation = null;
        this.init();
    }

    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.createModal();
                this.bindEvents();
            });
        } else {
            this.createModal();
            this.bindEvents();
        }
    }

    createModal() {
        // Verificar si el modal ya existe
        if (document.getElementById(this.modalId)) {
            return;
        }

        const modalHTML = `
            <div class="modal fade" id="${this.modalId}" tabindex="-1" aria-labelledby="reservationDetailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reservationDetailsModalLabel">
                                <i class="fas fa-calendar-check me-2"></i>Detalles de Reserva
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="reservationDetailsContent">
                                <div class="text-center">
                                    <div class="spinner-border" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cerrar
                            </button>
                            <div id="reservationActions" class="d-none">
                                <button type="button" class="btn btn-primary" id="editReservationBtn">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </button>
                                <button type="button" class="btn btn-danger" id="deleteReservationBtn">
                                    <i class="fas fa-trash me-1"></i>Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    bindEvents() {
        // Eventos para los botones de acción
        document.addEventListener('click', (e) => {
            if (e.target.id === 'editReservationBtn') {
                this.editReservation();
            } else if (e.target.id === 'deleteReservationBtn') {
                this.deleteReservation();
            }
        });
    }

    async showReservationDetails(reservationId, showActions = false) {
        try {
            // Mostrar el modal con spinner
            const modal = new bootstrap.Modal(document.getElementById(this.modalId));
            modal.show();

            // Cargar los detalles de la reserva
            const response = await fetch(`${APP_URL}/admin/reservations/api/${reservationId}`);
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al cargar los detalles de la reserva');
            }

            this.currentReservation = data.data;
            this.renderReservationDetails(showActions);
        } catch (error) {
            console.error('Error al cargar detalles de reserva:', error);
            this.showError('Error al cargar los detalles de la reserva: ' + error.message);
        }
    }

    renderReservationDetails(showActions = false) {
        const reservation = this.currentReservation;
        if (!reservation) return;

        const statusColors = {
            'confirmada': 'success',
            'pendiente': 'warning',
            'cancelada': 'danger',
            'completada': 'info'
        };

        const statusIcons = {
            'confirmada': 'check-circle',
            'pendiente': 'clock',
            'cancelada': 'times-circle',
            'completada': 'check-double'
        };

        const content = `
            <div class="row g-4">
                <!-- Información del Usuario -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Información del Usuario</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; border-radius: 50%;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">${reservation.user_name || 'N/A'}</h6>
                                    <small class="text-muted">${reservation.user_email || 'N/A'}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Servicio -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-car me-2"></i>Información del Servicio</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-light text-muted d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; border-radius: 8px;">
                                    <i class="fas fa-${this.getEntityIcon(reservation.entity_type)}"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">${reservation.entity_name || 'N/A'}</h6>
                                    <small class="text-muted">${this.getEntityTypeLabel(reservation.entity_type)}</small>
                                </div>
                            </div>
                            ${reservation.entity_model_type ? `<p class="mb-0"><small class="text-muted">${reservation.entity_model_type}</small></p>` : ''}
                        </div>
                    </div>
                </div>

                <!-- Fechas y Estado -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Fechas de Reserva</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Fecha Inicio:</small>
                                    <strong>${this.formatDate(reservation.pickup_date)}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Fecha Fin:</small>
                                    <strong>${this.formatDate(reservation.return_date)}</strong>
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted d-block">Duración:</small>
                                    <span class="badge bg-secondary">${this.calculateDuration(reservation.pickup_date, reservation.return_date)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado y Costo -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-euro-sign me-2"></i>Estado y Costo</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="mb-3">
                                    <span class="badge bg-${statusColors[reservation.status]} fs-6">
                                        <i class="fas fa-${statusIcons[reservation.status]} me-1"></i>
                                        ${this.getStatusLabel(reservation.status)}
                                    </span>
                                </div>
                                <div class="display-6 text-success fw-bold">
                                    €${parseFloat(reservation.total_cost || 0).toFixed(2)}
                                </div>
                                <small class="text-muted">Total de la reserva</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ubicaciones -->
                ${reservation.pickup_location_name || reservation.return_location_name ? `
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Ubicaciones</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                ${reservation.pickup_location_name ? `
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Recogida:</small>
                                    <strong>${reservation.pickup_location_name}</strong>
                                </div>
                                ` : ''}
                                ${reservation.return_location_name ? `
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Devolución:</small>
                                    <strong>${reservation.return_location_name}</strong>
                                </div>
                                ` : ''}
                            </div>
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

                <!-- Información Adicional -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Adicional</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">ID de Reserva:</small>
                                    <code>${reservation.id}</code>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Fecha de Creación:</small>
                                    <span>${this.formatDate(reservation.created_at)}</span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Última Actualización:</small>
                                    <span>${this.formatDate(reservation.updated_at)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('reservationDetailsContent').innerHTML = content;

        // Mostrar/ocultar botones de acción
        const actionsDiv = document.getElementById('reservationActions');
        if (showActions) {
            actionsDiv.classList.remove('d-none');
        } else {
            actionsDiv.classList.add('d-none');
        }
    }

    getEntityIcon(entityType) {
        const icons = {
            'vehicle': 'car',
            'boat': 'ship',
            'transfer': 'bus'
        };
        return icons[entityType] || 'question';
    }

    getEntityTypeLabel(entityType) {
        const labels = {
            'vehicle': 'Vehículo',
            'boat': 'Barco',
            'transfer': 'Traslado'
        };
        return labels[entityType] || 'Desconocido';
    }

    getStatusLabel(status) {
        const labels = {
            'confirmada': 'Confirmada',
            'pendiente': 'Pendiente',
            'cancelada': 'Cancelada',
            'completada': 'Completada'
        };
        return labels[status] || 'Desconocido';
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    calculateDuration(startDate, endDate) {
        if (!startDate || !endDate) return 'N/A';
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return `${diffDays} día${diffDays !== 1 ? 's' : ''}`;
    }

    showError(message) {
        document.getElementById('reservationDetailsContent').innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    editReservation() {
        if (!this.currentReservation) return;
        
        // Cerrar el modal de detalles
        const modal = bootstrap.Modal.getInstance(document.getElementById(this.modalId));
        modal.hide();
        
        // Abrir el modal de edición (si existe)
        if (typeof editReservation === 'function') {
            editReservation(this.currentReservation.id);
        } else {
            console.warn('Función editReservation no encontrada');
        }
    }

    deleteReservation() {
        if (!this.currentReservation) return;
        
        if (confirm('¿Estás seguro de que deseas eliminar esta reserva?')) {
            // Cerrar el modal de detalles
            const modal = bootstrap.Modal.getInstance(document.getElementById(this.modalId));
            modal.hide();
            
            // Ejecutar la eliminación (si existe la función)
            if (typeof deleteReservation === 'function') {
                deleteReservation(this.currentReservation.id);
            } else {
                console.warn('Función deleteReservation no encontrada');
            }
        }
    }
}

// Crear instancia global
window.reservationDetailsManager = new ReservationDetailsManager();

// Función global para mostrar detalles de reserva
window.showReservationDetails = function(reservationId, showActions = false) {
    window.reservationDetailsManager.showReservationDetails(reservationId, showActions);
};
