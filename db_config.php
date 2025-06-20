<?php 
	
	
// --- Database Configuration ---
// For security, it's better to store these in a separate file (e.g., db_config.php) and include it.
 define('DB_HOST', 'localhost');
 define('DB_USER', 'u124132715_SYP');
 define('DB_PASS', 'Salypimienta1!');
 define('DB_NAME', 'u124132715_SYP');
 //mysqli_connect('localhost', 'u124132715_SYP' ,'Salypimienta1!', 'u124132715_SYP'); 


// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // For API calls, return JSON error. For direct page load, can die.
    if (basename($_SERVER['PHP_SELF']) == 'api.php') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    } else {
        die("Connection failed: " . $conn->connect_error);
    }
}

$conn->set_charset("utf8mb4");


?>