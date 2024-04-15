<?php
include 'mysqli_connection.php';

class Room {
    public $id, $category, $size, $occupancy, $bed_type, $style, $image_url, $price;

    public function __construct($id, $category, $size, $occupancy, $bed_type, $style, $image_url, $price) {
        $this->id = $id;
        $this->category = $category;
        $this->size = $size;
        $this->occupancy = $occupancy;
        $this->bed_type = $bed_type;
        $this->style = $style;
        $this->image_url = $image_url;
        $this->price = $price;
    }

    public static function getAllRooms() {
        global $mysqli;
        $query = "SELECT * FROM room";
        $result = $mysqli->query($query);
        $rooms = [];
        while ($row = $result->fetch_assoc()) {
            $rooms[] = new Room($row['id'], $row['category'], $row['size'], $row['occupancy'], $row['bed_type'], $row['style'], $row['image_url'], $row['price']);
        }
        return $rooms;
    }

    public static function createRoom($category, $size, $occupancy, $bed_type, $style, $image_url, $price) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO room (category, size, occupancy, bed_type, style, image_url, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissid", $category, $size, $occupancy, $bed_type, $style, $image_url, $price);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    public static function getRoomById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM room WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new Room($row['id'], $row['category'], $row['size'], $row['occupancy'], $row['bed_type'], $row['style'], $row['image_url'], $row['price']);
        }
        return null;
    }

    public static function updateRoom($id, $category, $size, $occupancy, $bed_type, $style, $image_url, $price) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE room SET category = ?, size = ?, occupancy = ?, bed_type = ?, style = ?, image_url = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssissidi", $category, $size, $occupancy, $bed_type, $style, $image_url, $price, $id);
        return $stmt->execute();
    }

    public static function deleteRoom($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM room WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
