<?php
// controllers/LocationController.php

class LocationController {
    // Mostrar todas las ubicaciones
    public function index() {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL, 'No tienes permisos para esta acción', 'error');
        }
        
        $locations = Location::getAll();
        
        // Cargar la vista
        include 'views/locations/index.php';
    }
    
    // Mostrar formulario para crear una ubicación
    public function create() {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/locations', 'No tienes permisos para esta acción', 'error');
        }
        
        // Cargar la vista
        include 'views/locations/create.php';
    }
    
    // Procesar el formulario para crear una ubicación
    public function store() {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/locations', 'No tienes permisos para esta acción', 'error');
        }
        
        // Validar y procesar los datos del formulario
        $result = Location::create($_POST);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/locations', $result['message'], 'success');
        } else {
            // En caso de error, volver al formulario
            $error = $result['message'];
            include 'views/locations/create.php';
        }
    }
    
    // Mostrar formulario para editar una ubicación
    public function edit($id) {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/locations', 'No tienes permisos para esta acción', 'error');
        }
        
        $location = Location::getById($id);
        
        if (!$location) {
            Utils::redirect(APP_URL . '/locations', 'Ubicación no encontrada', 'error');
        }
        
        // Cargar la vista
        include 'views/locations/edit.php';
    }
    
    // Procesar el formulario para actualizar una ubicación
    public function update($id) {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/locations', 'No tienes permisos para esta acción', 'error');
        }
        
        // Validar y procesar los datos del formulario
        $result = Location::update($id, $_POST);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/locations', $result['message'], 'success');
        } else {
            // En caso de error, volver al formulario
            $location = Location::getById($id);
            $error = $result['message'];
            include 'views/locations/edit.php';
        }
    }
    
    // Eliminar una ubicación
    public function delete($id) {
        // Verificar si el usuario es administrador
        if (!User::isAdmin()) {
            Utils::redirect(APP_URL . '/locations', 'No tienes permisos para esta acción', 'error');
        }
        
        $result = Location::delete($id);
        
        if ($result['success']) {
            Utils::redirect(APP_URL . '/locations', $result['message'], 'success');
        } else {
            Utils::redirect(APP_URL . '/locations', $result['message'], 'error');
        }
    }
}
