
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// DB credentials
$host = 'medfeluuser.mysql.db';
$db   = 'medfeluuser';
$user = 'medfeluuser';
$pass = 'Medfeluuser123';

// Authorization
$headers = getallheaders();


// Input validation
$sql = $_POST['query'] ?? '';
if (!$sql) {
    http_response_code(400);
    echo json_encode(['error' => 'No query provided']);
    exit;
}

// Query type check
$allowed = ['SELECT', 'INSERT', 'UPDATE', 'DELETE'];
$prefix = strtoupper(strtok(trim($sql), ' '));
if (!in_array($prefix, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Query type not allowed']);
    exit;
}

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Execute query
$result = $conn->query($sql);

if ($result === TRUE) {
    echo json_encode(['status' => 'success', 'affected_rows' => $conn->affected_rows]);
} elseif ($result) {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $rows]);
} else {
    http_response_code(400);
    echo json_encode(['error' => $conn->error]);
}

$conn->close();
?>
