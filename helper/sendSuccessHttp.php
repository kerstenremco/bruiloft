<?php
$successMessage;
$successCode;
function sendSuccessHttp()
{
    global $successMessage, $successCode;
    if(func_num_args() == 2) {
        $successMessage = func_get_arg(0);
        $successCode = func_get_arg(1);
    }
    if($successCode == null) $successCode = 200;
    http_response_code($successCode);
    $response = $successMessage != null ? array('status' => 'successful', 'message' =>  $successMessage) : array('status' => 'successful');
    echo json_encode($response);
    die();
}
?>