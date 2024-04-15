<?php
include 'mysqli_connection.php';

class HotelBooking {
    public $id, $user_id, $room_id, $check_in, $check_out;

    public function __construct($id, $user_id, $room_id, $check_in, $check_out) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->room_id = $room_id;
        $this->check_in = $check_in;
        $this->check_out = $check_out;
    }

    // Create a new HotelBooking
    public static function createBooking($user_id, $room_id, $check_in, $check_out) {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO hotel_bookings (user_id, room_id, check_in, check_out) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $room_id, $check_in, $check_out);
        $stmt->execute();
        return $mysqli->insert_id;
    }

    // Read a HotelBooking by ID
    public static function getBookingById($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT * FROM hotel_bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new HotelBooking($row['id'], $row['user_id'], $row['room_id'], $row['check_in'], $row['check_out']);
        }
        return null;
    }

    // Update a HotelBooking
    public static function updateBooking($id, $user_id, $room_id, $check_in, $check_out) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE hotel_bookings SET user_id = ?, room_id = ?, check_in = ?, check_out = ? WHERE id = ?");
        $stmt->bind_param("iissi", $user_id, $room_id, $check_in, $check_out, $id);
        return $stmt->execute();
    }

    // Delete a HotelBooking
    public static function deleteBooking($id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM hotel_bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Additional methods as needed
}
?>
