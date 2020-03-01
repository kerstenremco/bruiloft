<?php
function sendError($bericht) {
    http_response_code(503);
    echo json_encode(['status' => 'fail', 'message' =>  $bericht]);
    die();
}
?>