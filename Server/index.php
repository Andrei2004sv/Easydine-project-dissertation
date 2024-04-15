<?php
require 'vendor/autoload.php';
// Include the MySQLi connection file
include 'mysqli_connection.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function extractUserIdFromJwt($jwtToken) {
    $secretKey = 'your-secret-key'; // Replace with your secret key
    try {
        $decodedToken = JWT::decode($jwtToken, new Key($secretKey, 'HS256'));
        $userId = $decodedToken->sub; // Assuming 'sub' field contains user ID
        return $userId;
    } catch (Exception $e) {
        // Handle decode error (invalid token, expired, etc.)
        return null;
    }
}
function getUserFromToken($token) {
    global $mysqli;
    $userId = extractUserIdFromJwt($token);
    if ($userId === null) {
        return null; // Token invalid or user ID not found in token
    }
    
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // Return user data or null if user not found
}



// Function definitions
function validateJwtToken($token) {
    // JWT validation logic (dummy implementation for example)
    $userId = extractUserIdFromJwt($token);
    return $userId;
}

function loadUserById($userId) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function authenticateUser($username, $password) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return null;
}

function userExists($username) {
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function createUser($username, $hashedPassword, $isAdmin) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $hashedPassword, $isAdmin);
    $stmt->execute();
}

function createJwtToken($userId) {
    // Token generation logic (dummy implementation for example)
    return "token-$userId";
}

function getUserIdFromToken() {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    return extractUserIdFromJwt($token);
}


// Route handling
$pathInfo = $_SERVER['PATH_INFO'] ?? '/';

// Welcome route
if ($pathInfo === '/') {
    echo "Welcome to the home page";
}

// Protected route example
elseif ($pathInfo === '/protected') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $userId = validateJwtToken($token);
    if ($userId) {
        echo json_encode(['message' => "Access granted for user with ID: $userId"]);
    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied']);
    }
}

// Login endpoint
elseif ($pathInfo === '/login' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $username = $inputData['username'] ?? '';
    $password = $inputData['password'] ?? '';

    $user = authenticateUser($username, $password);
    if ($user) {
        $token = createJwtToken($user['id']);
        echo json_encode(['success' => true, 'is_admin' => $user['is_admin'], 'token' => $token]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
    }
}

// Registration endpoint
elseif ($pathInfo === '/register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputData = json_decode(file_get_contents('php://input'), true);
    $username = $inputData['username'] ?? '';
    $password = $inputData['password'] ?? '';
    $is_admin = $inputData['is_admin'] ?? false;

    if (!userExists($username) && $username != '' && $password != '') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        createUser($username, $hashedPassword, $is_admin);
        $userId = getUserIdByUsername($username); // Implement getUserIdByUsername to fetch ID after user creation
        $token = createJwtToken($userId);
        echo json_encode(['success' => true, 'username' => $username, 'is_admin' => $is_admin, 'token' => $token]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid input or username already exists']);
    }
}

// ... [Continue from the registration endpoint code]

// Route to get room service items
elseif ($pathInfo === '/room-service/items' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $result = $mysqli->query("SELECT * FROM room_service_items");
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode($items);
}

// Route to add a new room service item
elseif ($pathInfo === '/room-service/items' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $mysqli->prepare("INSERT INTO room_service_items (name, description, price, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $data['name'], $data['description'], $data['price'], $data['image_url']);
    
    if ($stmt->execute()) {
        $newItemId = $stmt->insert_id;
        echo json_encode(['message' => 'Item added successfully', 'id' => $newItemId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error adding item']);
    }
}

// Route to delete a room service item
elseif (preg_match('/\/room-service\/items\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $itemId = $matches[1];
    $stmt = $mysqli->prepare("DELETE FROM room_service_items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['message' => 'Item deleted successfully']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found']);
    }
}

// Route to view a booking
elseif (preg_match('/\/booking\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $bookingId = $matches[1];

    $stmt = $mysqli->prepare("SELECT * FROM hotel_bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($booking = $result->fetch_assoc()) {
        // include 'view_booking.php'; // Display booking details
        echo json_encode($booking); // For simplicity, just echoing the booking details
    } else {
        http_response_code(404);
        echo "Booking not found";
    }
}

// Route to edit a booking
elseif (preg_match('/\/edit\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $bookingId = $matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    $stmt = $mysqli->prepare("UPDATE hotel_bookings SET name = ?, room_type = ?, check_in = ?, check_out = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $data['name'], $data['room_type'], $data['check_in'], $data['check_out'], $bookingId);

    if ($stmt->execute()) {
        header("Location: index.php"); // Redirect to index page
    } else {
        echo "Error updating booking";
    }
}

// Route to delete a booking
elseif (preg_match('/\/delete\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $bookingId = $matches[1];

    $stmt = $mysqli->prepare("DELETE FROM hotel_bookings WHERE id = ?");
    $stmt->bind_param("i", $bookingId);

    if ($stmt->execute()) {
        header("Location: index.php"); // Redirect to index page
    } else {
        echo "Error deleting booking";
    }
}

// Route to place a special order
elseif ($pathInfo === '/special-order' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $specialRequest = $data['request'] ?? '';

    if (!$specialRequest) {
        http_response_code(400);
        echo json_encode(['error' => 'No special request provided']);
    } else {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $userId = extractUserIdFromJwt($token); 
        $stmt = $mysqli->prepare("INSERT INTO special_orders (user_id, request) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $specialRequest);

        if ($stmt->execute()) {
            echo json_encode(['success' => 'Your special request has been submitted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while processing your request']);
        }
    }
}

// Route to place a room service order
elseif ($pathInfo === '/room-service/order' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $items = $data['items'] ?? [];

    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $userId = extractUserIdFromJwt($token);


    foreach ($items as $item) {
        $itemId = $item['id'] ?? null;
        if (!$itemId) continue;

        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, details, order_type) VALUES (?, ?, 'Food')");
        $stmt->bind_param("is", $userId, $item['name']);
        $stmt->execute();
    }

    echo json_encode(['message' => 'Order placed successfully']);
}

// Route to check order status
elseif (preg_match('/\/order\/status\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $orderId = $matches[1];
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $currentUser = getUserFromToken($token);
   

    $stmt = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();

    if (!$order) {
        http_response_code(404);
        echo "Order not found";
    } elseif ($order['user_id'] != $currentUser['id'] && !$currentUser['is_admin']) {
        echo "You do not have permission to view this order.";
    } else {
        $status = getOrderStatus($order);
        include 'order_status.php';
    }
}

// Route to handle user profile
elseif (preg_match('/\/profile\/(\d+)/', $pathInfo, $matches)) {
    $requestedUserId = $matches[1];
    $currentUserId = getCurrentUserId();

    if ($currentUserId != $requestedUserId) {
        http_response_code(403);
        echo json_encode(['message' => 'Access denied']);
    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $stmt = $mysqli->prepare("SELECT username, email, phone_number FROM users WHERE id = ?");
        $stmt->bind_param("i", $requestedUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        $orderStmt = $mysqli->prepare("SELECT * FROM orders WHERE user_id = ?");
        $orderStmt->bind_param("i", $requestedUserId);
        $orderStmt->execute();
        $ordersResult = $orderStmt->get_result();
        $orders = [];
        while ($order = $ordersResult->fetch_assoc()) {
            $orders[] = $order;
        }
        
        $userData['orders'] = $orders;
        echo json_encode($userData);
    }
}

// Contact route
elseif ($pathInfo === '/contact') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        // Handle contact form submission
        header("Location: contact.php");
    } else {
        // Show contact form
        include 'contact_form.php';
    }
}

// Admin dashboard route
elseif ($pathInfo === '/admin' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $currentUser = getUserFromToken($token);
    if (!$currentUser['is_admin']) {
        header("Location: index.php");
    } else {
        include 'admin_dashboard.php';
    }
}

// Add room route
elseif ($pathInfo === '/booking' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $category = $data['category'] ?? '';
    $size = $data['size'] ?? '';
    $occupancy = $data['occupancy'] ?? '';
    $bedType = $data['bed_type'] ?? '';
    $style = $data['style'] ?? '';
    $imageUrl = $data['image_url'] ?? '';
    $price = $data['price'] ?? 0;

    if ($category && $size && $occupancy && $bedType && $style && $price) {
        $roomId = addRoom($category, $size, $occupancy, $bedType, $style, $imageUrl, $price);
        if ($roomId) {
            echo json_encode(['message' => 'Room successfully added', 'room_id' => $roomId]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while adding the room']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required room information']);
    }
}

// Get bookings for admin
elseif ($pathInfo === '/admin/bookings' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $result = $mysqli->query("SELECT * FROM hotel_bookings");
    $bookings = [];
    while ($booking = $result->fetch_assoc()) {
        $bookings[] = $booking;
    }
    echo json_encode(['bookings' => $bookings]);
}

// Get orders for admin
elseif ($pathInfo === '/admin/orders' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $result = $mysqli->query("SELECT * FROM orders");
    $orders = [];
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
    echo json_encode(['orders' => $orders]);
}

// Delete room route
elseif (preg_match('/\/booking\/(\d+)/', $pathInfo, $matches) && $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    $roomId = $matches[1];

    $checkStmt = $mysqli->prepare("SELECT * FROM rooms WHERE id = ?");
    $checkStmt->bind_param("i", $roomId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Room not found']);
    } else {
        $deleteStmt = $mysqli->prepare("DELETE FROM rooms WHERE id = ?");
        $deleteStmt->bind_param("i", $roomId);
        if ($deleteStmt->execute()) {
            echo json_encode(['message' => 'Room successfully deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'An error occurred while deleting the room']);
        }
    }
}

// Route to get available rooms
elseif ($pathInfo === '/booking' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $result = $mysqli->query("SELECT * FROM rooms");
    $rooms = [];
    while ($room = $result->fetch_assoc()) {
        $rooms[] = $room;
    }
    echo json_encode($rooms);
}

// Route to make a booking
elseif ($pathInfo === '/make-booking' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $userId = extractUserIdFromJwt($token);
    $roomId = $data['roomId'];
    $checkIn = date('Y-m-d H:i:s', strtotime($data['checkIn']));
    $checkOut = date('Y-m-d H:i:s', strtotime($data['checkOut']));

    $stmt = $mysqli->prepare("INSERT INTO hotel_bookings (user_id, room_id, check_in, check_out) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $userId, $roomId, $checkIn, $checkOut);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Booking successful']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while making the booking']);
    }
}


