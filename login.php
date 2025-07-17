<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'MemoSync';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $keep_logged_in = isset($_POST['keep_logged_in']);
    
    // Validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            
            // Set session timeout
            if ($keep_logged_in) {
                ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60);
                session_set_cookie_params(30 * 24 * 60 * 60);
            } else {
                ini_set('session.gc_maxlifetime', 2 * 60 * 60);
                session_set_cookie_params(2 * 60 * 60);
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['first_name'] . ' ' . $user['last_name']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
