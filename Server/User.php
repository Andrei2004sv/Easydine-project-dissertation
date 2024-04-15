<?php
include 'mysqli_connection.php';

class User {
    public $id, $username, $password, $is_admin;

    public function __construct($id, $username, $password, $is_admin) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password; // Ensure password is hashed
        $this->is_admin = $is_admin;
    }

    // Create a new User
    public static function createUser($username, $hashedPassword, $is_admin) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO users (username, password, is_admin) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $username, $hashedPassword, $is_admin);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    // Read a User by ID
    public static function getUserById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new User($row['id'], $row['username'], $row['password'], $row['is_admin']);
        }
        return null;
    }

    // Update a User
    public static function updateUser($id, $username, $hashedPassword, $is_admin) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE users SET username = ?, password = ?, is_admin = ? WHERE id = ?");
        $stmt->bind_param("ssii", $username, $hashedPassword, $is_admin, $id);
        return $stmt->execute();
    }

    // Delete a User
    public static function deleteUser($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Additional methods as needed
}

?>
