<?php
header('Content-Type: application/json');
require_once 'helper/error.php';
if($_SERVER['REQUEST_METHOD'] !== "POST") sendError('Alleen POST toegestaan');
$data = json_decode( file_get_contents('php://input') );
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
    case 'uitloggen':
    break;
    default:
        sendError('Onbekende methode');
        break;
};

require_once 'objects/user.php';
require_once 'objects/wedding.php';
session_start();

if($data->method == 'inloggen') {
    $user = new User();
    $user->gebruikersnaam = $data->gebruikersnaam;
    $user->password = $data->wachtwoord;
    if($user->validateUser()) {
        $_SESSION['userID'] = $user->id;
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        sendError('Gebruiker niet gevonden');
    }
}

if($data->method == 'registreren') {
    $user = new User();
    $user->gebruikersnaam = $data->gebruikersnaam;
    $user->password = $data->wachtwoord;
    $user->emailadres = $data->email;
    if($user->createUser()) {
        $_SESSION['userID'] = $user->id;
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        sendError('Gebruiker niet gevonden');
    }
}

if($data->method == 'uitnodigingscode') {
    $wedding = new Wedding();
    $wedding->invitecode = $data->code;
    if($wedding->validateWeddingCode()) {
        $_SESSION['weddingID'] = $wedding->id;
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        sendError('Ongeldige code');
    }
}

if($data->method == 'uitloggen') {
    $_SESSION = array();
    session_destroy();
    http_response_code(200);
    echo json_encode(array('status' => 'successful'));
}
?>