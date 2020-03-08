<?php
namespace helpers;
class successHandler {
    static function sendJSON() {
        $successCode = 200;

        if(func_num_args() > 0) $successMessage = func_get_arg(0);
        if(func_num_args() == 2) $successCode = func_get_arg(1);

        $response = isset($successMessage) ? array('status' => 'successful', 'message' =>  $successMessage) : array('status' => 'successful');
        http_response_code($successCode);
        echo json_encode($response);
        die();
    }

}
?>