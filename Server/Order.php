<?php
include 'mysqli_connection.php';

class Order {
    public $id, $user_id, $details, $status, $order_type;

    public function __construct($id, $user_id, $details, $status, $order_type) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->details = $details;
        $this->status = $status;
        $this->order_type = $order_type;
    }

    // Create a new Order
    public static function createOrder($user_id, $details, $status, $order_type) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, details, status, order_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $details, $status, $order_type);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    // Read an Order by ID
    public static function getOrderById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new Order($row['id'], $row['user_id'], $row['details'], $row['status'], $row['order_type']);
        }
        return null;
    }

    // Update an Order
    public static function updateOrder($id, $user_id, $details, $status, $order_type) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE orders SET user_id = ?, details = ?, status = ?, order_type = ? WHERE id = ?");
        $stmt->bind_param("issii", $user_id, $details, $status, $order_type, $id);
        return $stmt->execute();
    }

    // Delete an Order
    public static function deleteOrder($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Additional methods as needed
}
?>
