<?php
include 'mysqli_connection.php';

class DeliveryOrder {
    public $id, $user_id, $details, $status, $delivery_address, $delivery_time;

    public function __construct($id, $user_id, $details, $status, $delivery_address, $delivery_time) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->details = $details;
        $this->status = $status;
        $this->delivery_address = $delivery_address;
        $this->delivery_time = $delivery_time;
    }

    // CRUD operations for DeliveryOrder
}
?>
