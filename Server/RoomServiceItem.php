<?php
include 'mysqli_connection.php';

class RoomServiceItem {
    public $id, $name, $description, $price, $image_url;

    public function __construct($id, $name, $description, $price, $image_url) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->image_url = $image_url;
    }

    // Create a new Room Service Item
    public static function createItem($name, $description, $price, $image_url) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO room_service_items (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $name, $description, $price, $image_url);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    // Read a Room Service Item by ID
    public static function getItemById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM room_service_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new RoomServiceItem($row['id'], $row['name'], $row['description'], $row['price'], $row['image_url']);
        }
        return null;
    }

    // Update a Room Service Item
    public static function updateItem($id, $name, $description, $price, $image_url) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE room_service_items SET name = ?, description = ?, price = ?, image_url = ? WHERE id = ?");
        $stmt->bind_param("ssdsi", $name, $description, $price, $image_url, $id);
        return $stmt->execute();
    }

    // Delete a Room Service Item
    public static function deleteItem($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM room_service_items WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Additional methods as needed
}
?>
