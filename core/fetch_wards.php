<?php
require_once 'db_connect.php';

if (isset($_GET['lga_id'])) {
    $lgaId = intval($_GET['lga_id']);
    $query = $db->prepare("SELECT id, name FROM wards WHERE lga_id = ? ORDER BY name");
    $query->bind_param("i", $lgaId);
    $query->execute();
    $result = $query->get_result();
    
    $wards = [];
    while ($row = $result->fetch_assoc()) {
        $wards[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($wards);
}