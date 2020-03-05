<?php
header('Content-Type: application/json');
require_once 'helper/errorHandler.php';
require_once 'helper/successHandler.php';
require_once 'objects/user.php';
require_once 'objects/wedding.php';
session_start();

// controleer of het POST is
if($_SERVER['REQUEST_METHOD'] !== "POST") {
    $error = new errorHandler('Alleen POST toegastaan', 400);
    $error->sendJSON();
};

// zet request in $data
$data = json_decode( file_get_contents('php://input') );
if(empty($data->method)) $data->method = null;

// Controleer of velden zijn meegegeven behorende bij de methode, en roep juiste handler op
switch($data->method) {
    case 'invitecode':
        checkFields($data, ['invitecode']);
        handleInvitecode($data->invitecode);
    case 'login':
        checkFields($data, ['username', 'password']);
        handleLogin($data->username, $data->password);
    case 'register':
        checkFields($data, ['username', 'password', 'password2', 'email']);
        handleRegister($data->username, $data->password, $data->email);
    case 'logout':
        handleLogout();
    default:
        $error = new errorHandler('Onbekende methode', 400);
        $error->sendJSON();
};

function checkFields($data, $fields) {
    foreach($fields as $field) {
        if(empty($data->$field)) {
            $error = new errorHandler('Niet alle velden zijn ingevuld', 400);
            $error->sendJSON();
        }
    }
}

function handleLogin($username, $password)
{
    $loginResult = User::login($username, $password);
    if($loginResult == false) {
        $error = new errorHandler('Gebruikersnaam of wachtwoord verkeerd', 401);
        $error->sendJSON();
    };
    $_SESSION['username'] = $username;
    $response = new successHandler;
    $response->sendJSON();
}

function handleRegister($username, $password, $email)
{
    $result = User::createUser($username, $password, $email);
    if($result instanceof errorHandler) $result->sendJSON();
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $username;
    $response = new successHandler;
    $response->sendJSON();
}

function handleInvitecode($invitecode)
{
    $result = Wedding::validateWeddingCode($invitecode);
    if($result instanceof errorHandler) $result->sendJSON();
    $_SESSION['username'] = null;
    $_SESSION['weddingID'] = $result;
    $response = new successHandler;
    $response->sendJSON();
}


function handleLogout() {
    $_SESSION = array();
    session_destroy();
    $response = new successHandler();
    $response->sendJSON();
}
?>