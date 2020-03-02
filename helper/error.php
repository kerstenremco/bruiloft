<?php
$errorMessage;
$errorCode;
function sendError() {
    global $errorMessage, $errorCode;
    if(func_num_args() == 2) {
        $errorMessage = func_get_arg(0);
        $errorCode = func_get_arg(1);
    }
    if($errorCode == null) $errorCode = 503;
    http_response_code($errorCode);
    echo json_encode(['status' => 'fail', 'message' =>  $errorMessage]);
    die();
}
?>