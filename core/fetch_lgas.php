<?php
require_once 'db_connect.php';

if (isset($_GET['state_id'])) {
    $stateId = intval($_GET['state_id']);
    $query = $db->prepare("SELECT id, name FROM lgas WHERE state_id = ? ORDER BY name");
    $query->bind_param("i", $stateId);
    $query->execute();
    $result = $query->get_result();
    
    $lgas = [];
    while ($row = $result->fetch_assoc()) {
        $lgas[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($lgas);
}