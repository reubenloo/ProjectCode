<?php
// get_components.php
session_start();

include 'db_connect.php';

$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

if (isset($_GET['student_module_id'])) {
    $student_module_id = $_GET['student_module_id'];

    $sql = "SELECT c.component_id, c.component_name 
            FROM components c
            JOIN student_modules sm ON c.module_id = sm.module_id
            WHERE sm.student_module_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_module_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $components = [];

    while ($row = $result->fetch_assoc()) {
        $components[] = ['component_id' => $row['component_id'], 'component_name' => $row['component_name']];
    }

    $stmt->close();

    echo json_encode($components);
} else {
    echo json_encode(['error' => 'No student_module_id provided']);
}
?>
