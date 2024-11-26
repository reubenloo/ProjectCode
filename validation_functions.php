<?php
// validation_functions.php

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_student_id($student_id) {
    return preg_match('/^[A-Za-z0-9]{8}$/', $student_id);
}
?>