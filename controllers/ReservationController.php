<?php
// controllers/ReservationController.php

class ReservationController {
    // Mostrar todas las reservas
    public function index() {
        // Verificar si el usuario está autenticado
        if (!User::isAuthenticated()) {
            Utils::redirect(APP_URL . '/login', 'Debes iniciar sesión para ver tus reservas', 'warning');
        }
        
        $reservations = Reservation::getAll();
        
        // Cargar la vista
        include 'views/reservations/index.php';
    }
    
    // Mostrar una reserva específica
    public function show($id) {
        // Verificar si el usuario está autenticado
        if (!User::isAuthenticated()) {
            Utils::redirect(APP_URL . '/login', 'Debes iniciar sesión para ver esta reserva', 'warning');
        }
        
        $reservation = Reservation::getById($id);
        
        if (!$reservation) {
            Utils::redirect(APP_URL . '/reservations', 'Reserva no encontrada', 'error');
        }
        
        // Cargar la vista
        include 'views/reservations/show.php';
    }
    
    // Mostrar formulario para crear una reserva
    public function create($vehicle_id) {
        // Verificar si el usuario está autenticado
        if (!User::isAuthenticated()) {
            Utils::redirect(APP_URL . '/login', 'Debes iniciar sesión para realizar una reserva', 'warning');
        }
        
        $vehicle = Vehicle::getById($vehicle_id);
        
        if (!$vehicle) {
            Utils::redirect(APP_URL . '/vehicles', 'Vehículo no encontrado', 'error');
        }
        
        // Cargar la vista
        include 'views/reservations/create.php';
    }
    
    // Procesar el formulario para crear una reserva
    public function store() {
        // Verificar si el usuario está autenticado
        if (!User::isAuthenticated()) {
            Utils::redirect(APP_URL . '/login', 'Debes iniciar sesión para realizar una reserva', 'warning');
        }
        
        // Validar y procesar los datos del formulario
        $result = Reservation::create($_POST);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/reservations', $result['message'], 'success');
        } else {
            // En caso de error, volver al formulario
            $vehicle = Vehicle::getById($_POST['vehicle_id']);
            $error = $result['message'];
            include 'views/reservations/create.php';
        }
    }
    
    // Cancelar una reserva
    public function cancel($id) {
        // Verificar si el usuario está autenticado
        if (!User::isAuthenticated()) {
            Utils::redirect(APP_URL . '/login', 'Debes iniciar sesión para cancelar una reserva', 'warning');
        }
        
        $result = Reservation::cancel($id);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/reservations', $result['message'], 'success');
        } else {
            Utils::redirect(APP_URL . '/reservations', $result['message'], 'error');
        }
    }
    
    // Actualizar el estado de una reserva (solo admin)
    public function updateStatus($id) {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/reservations', 'No tienes permisos para esta acción', 'error');
        }
        
        $status = $_POST['status'] ?? '';
        
        if (empty($status)) {
            Utils::redirect(APP_URL . '/reservations', 'Estado no válido', 'error');
        }
        
        $result = Reservation::updateStatus($id, $status);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/reservations', $result['message'], 'success');
        } else {
            Utils::redirect(APP_URL . '/reservations', $result['message'], 'error');
        }
    }
}
