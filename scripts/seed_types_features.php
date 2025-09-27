<?php
// Seed de datos iniciales para types y features
// Uso: php scripts/seed_types_features.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

function upsertType(PDO $db, string $name, string $category): void {
    $stmt = $db->prepare('SELECT id FROM types WHERE LOWER(name)=LOWER(?) AND category=?');
    $stmt->execute([$name, $category]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        $upd = $db->prepare('UPDATE types SET active=1 WHERE id=?');
        $upd->execute([$existing]);
        echo "Type actualizado: {$category} / {$name}\n";
        return;
    }
    $ins = $db->prepare('INSERT INTO types (id, name, category, active) VALUES (UUID(), ?, ?, 1)');
    $ins->execute([$name, $category]);
    echo "Type insertado: {$category} / {$name}\n";
}

function upsertFeature(PDO $db, string $name, string $category, string $icon = 'fas fa-check'): void {
    // UNIQUE KEY global por nombre: comprobar solo por nombre
    $stmt = $db->prepare('SELECT id FROM features WHERE LOWER(name)=LOWER(?)');
    $stmt->execute([$name]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        $upd = $db->prepare('UPDATE features SET active=1, icon=? WHERE id=?');
        $upd->execute([$icon, $existing]);
        echo "Feature actualizada: {$category} / {$name}\n";
        return;
    }
    $ins = $db->prepare('INSERT INTO features (id, name, category, icon, active) VALUES (UUID(), ?, ?, ?, 1)');
    $ins->execute([$name, $category, $icon]);
    echo "Feature insertada: {$category} / {$name}\n";
}

try {
    $db = Database::getInstance()->getConnection();

    // Tipos por categoría
    // Para vehículos, incluir también las categorías del enum actual: económico, familiar, lujo, deportivo
    $vehicleTypes = ['económico', 'familiar', 'lujo', 'deportivo', 'sedán', 'suv', 'hatchback', 'pickup', 'furgoneta', 'eléctrico'];
    $boatTypes    = ['yate', 'velero', 'lancha', 'catamarán'];
    $transferTypes= ['limusina', 'minivan', 'suv', 'bus'];

    foreach ($vehicleTypes as $t) { upsertType($db, $t, 'vehicle'); }
    foreach ($boatTypes as $t) { upsertType($db, $t, 'boat'); }
    foreach ($transferTypes as $t) { upsertType($db, $t, 'transfer'); }

    // Features básicas
    $vehicleFeatures = [
        ['Aire acondicionado', 'vehicle', 'fas fa-fan'],
        ['GPS', 'vehicle', 'fas fa-location-arrow'],
        ['Automático', 'vehicle', 'fas fa-cogs'],
        ['Bluetooth', 'vehicle', 'fas fa-music'],
    ];
    $boatFeatures = [
        ['Tripulación incluida', 'boat', 'fas fa-user-friends'],
        ['Cabina', 'boat', 'fas fa-bed'],
        ['Equipo de seguridad', 'boat', 'fas fa-life-ring'],
    ];
    $transferFeatures = [
        ['Conductor profesional', 'transfer', 'fas fa-id-badge'],
        ['Agua y snacks', 'transfer', 'fas fa-glass-water'],
        ['Wi-Fi', 'transfer', 'fas fa-wifi'],
    ];

    foreach ($vehicleFeatures as [$n,$c,$i]) { upsertFeature($db, $n, $c, $i); }
    foreach ($boatFeatures as [$n,$c,$i]) { upsertFeature($db, $n, $c, $i); }
    foreach ($transferFeatures as [$n,$c,$i]) { upsertFeature($db, $n, $c, $i); }

    echo "\nSeed completado.\n";
} catch (Exception $e) {
    echo "Error en seed: " . $e->getMessage() . "\n";
    exit(1);
}


