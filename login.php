<?php
header('Content-Type: application/json');
require_once 'helper/error.php';
require_once 'helper/sendSuccessHttp.php';
if($_SERVER['REQUEST_METHOD'] !== "POST") sendError('Alleen POST toegestaan', 405);
$data = json_decode( file_get_contents('php://input') );
switch($data->method) {
    case 'uitnodigingscode':
        if(!isset($data->code)) sendError('Niet alle velden zijn ingevuld!', 400);
        break;
    case 'inloggen':
        if(!(isset($data->gebruikersnaam) && isset($data->wachtwoord))) sendError('Niet alle velden zijn ingevuld!', 400);
        break;
    case 'registreren':
        if(!(isset($data->gebruikersnaam) && isset($data->wachtwoord) && isset($data->wachtwoord2) && isset($data->email))) sendError('Niet alle velden zijn ingevuld!', 400);
        break;
    case 'uitloggen':
    break;
    default:
        sendError('Onbekende methode', 400);
        break;
};

require_once 'objects/user.php';
require_once 'objects/wedding.php';
session_start();

if($data->method == 'inloggen') {
    $user = new User();
    $user->gebruikersnaam = $data->gebruikersnaam;
    $user->password = $data->wachtwoord;
    if ($user->validateUser()) {
        $_SESSION['userID'] = $user->id;
        sendSuccessHttp();
    } else {
        sendError();
    }
}

if($data->method == 'registreren') {
    $user = new User();
    $user->gebruikersnaam = $data->gebruikersnaam;
    $user->password = $data->wachtwoord;
    $user->emailadres = $data->email;
    if($user->createUser()) {
        $_SESSION['userID'] = $user->id;
        sendSuccessHttp();
    } else {
        sendError();
    }
}

if($data->method == 'uitnodigingscode') {
    $wedding = new Wedding();
    $wedding->invitecode = $data->code;
    if($wedding->validateWeddingCode() == false) sendError();
    $_SESSION['weddingID'] = $wedding->id;
    sendSuccessHttp();
}

if($data->method == 'uitloggen') {
    $_SESSION = array();
    session_destroy();
    sendSuccessHttp();
}
?>