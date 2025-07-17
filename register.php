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
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        exit;
    }
    
    if (strlen($first_name) < 2 || strlen($last_name) < 2) {
        echo json_encode(['success' => false, 'message' => 'First name and last name must be at least 2 characters long']);
        exit;
    }
    
    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user (no created_at)
        $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $hashed_password]);
        
        $user_id = $pdo->lastInsertId();
        
        // Auto-login after registration
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful',
            'user' => [
                'id' => $user_id,
                'email' => $email,
                'name' => $first_name . ' ' . $last_name
            ]
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
