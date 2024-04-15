<?php
include 'mysqli_connection.php';

class Feedback {
    public $id, $user_id, $content;

    public function __construct($id, $user_id, $content) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->content = $content;
    }

    // CRUD operations for Feedback
}
?>
