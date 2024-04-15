<?php
include 'mysqli_connection.php';

class SpecialOrder {
    public $id, $user_id, $request, $status;

    public function __construct($id, $user_id, $request, $status) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->request = $request;
        $this->status = $status;
    }

    // Create a new SpecialOrder
    public static function createSpecialOrder($user_id, $request, $status) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO special_orders (user_id, request, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $request, $status);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    // Read a SpecialOrder by ID
    public static function getSpecialOrderById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM special_orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new SpecialOrder($row['id'], $row['user_id'], $row['request'], $row['status']);
        }
        return null;
    }

    // Update a SpecialOrder
    public static function updateSpecialOrder($id, $request, $status) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE special_orders SET request = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssi", $request, $status, $id);
        return $stmt->execute();
    }

    // Delete a SpecialOrder
    public static function deleteSpecialOrder($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM special_orders WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Additional methods as needed
}

?>
