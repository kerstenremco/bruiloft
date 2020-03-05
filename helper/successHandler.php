<?php
class successHandler {
    private $successMessage = null;
    private $successCode = 200;
    function __construct() {
        if(func_num_args() == 2) {
            $this->successMessage = func_get_arg(0);
            $this->successCode = func_get_arg(1);
        }
    }
    function sendJSON() {
        $response = $this->successMessage != null ? array('status' => 'successful', 'message' =>  $this->successMessage) : array('status' => 'successful');
        echo json_encode($response);
        die();
    }
}
?>