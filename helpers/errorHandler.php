<?php
function sendExeptionJson($e)
{
    http_response_code($e->getCode());
    echo json_encode(['status' => 'fail', 'message' =>  $e->getMessage()]);
    die();
}
?>