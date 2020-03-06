<?php
namespace helpers;
class errorHandler {
    public $errorMessage;
    public $errorCode;
    function __construct($errormessage, $errorcode)
    {
        $this->errorMessage = $errormessage;
        $this->errorCode = $errorcode;
    }
    function sendJSON() {
        http_response_code($this->errorCode);
        echo json_encode(['status' => 'fail', 'message' =>  $this->errorMessage]);
        die();
    }
}
?>