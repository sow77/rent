<?php
include_once 'config/Database.php';
include_once 'models/Vehicle.php';

$database = new Database();
$db = $database->getConnection();
$vehicle = new Vehicle($db);

$location = isset($_GET['location']) ? $_GET['location'] : '';
$pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
$return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';

$stmt = $vehicle->search($location, $pickup_date, $return_date);
$vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($vehicles);
?>