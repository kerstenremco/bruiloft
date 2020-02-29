<?php
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD'] !== "POST") die();
$data = json_decode( file_get_contents('php://input') );
if((!isset($data->method)) || !(($data->method == 'uitnodigingscode') || !($data->method == 'inloggen') || !($data->method == 'registreren') || !($data->method == 'uitloggen'))) die();
switch($data->method) {
    case 'uitnodigingscode':
        if(!isset($data->code)) sendError('Niet alle velden zijn ingevuld!');
        break;
    case 'inloggen':
        if(!(isset($data->gebruikersnaam) && isset($data->wachtwoord))) sendError('Niet alle velden zijn ingevuld!');
        break;
    case 'registreren':
        if(!(isset($data->gebruikersnaam) && isset($data->wachtwoord) && isset($data->wachtwoord2) && isset($data->email))) sendError('Niet alle velden zijn ingevuld!');
        break;
};

require_once 'objects/user.php';

if($data->method == 'inloggen') {
    $user = new User($data->gebruikersnaam, $data->wachtwoord);
    $user->connect();
    if($user->validateUser()) {
        $user->disconnect();
        session_start();
        $_SESSION['user'] = $user;
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        http_response_code(401);
        echo json_encode(array('status' => 'fail', 'message' => 'gebruiker niet gevonden'));
    }
}

if($data->method == 'uitloggen') {
    session_start();
    $_SESSION = array();
    session_destroy();
    http_response_code(200);
    echo json_encode(array('status' => 'successful'));
}
// session_start();
// echo json_encode(array('status' => 'successful'));

function sendError($bericht) {
    echo $bericht;
    die();
}
?>