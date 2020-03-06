<?php
header('Content-Type: application/json');
require_once 'autoload.php';
session_start();

if (empty($_POST['method'])) $_POST['method'] = null;

// Controleer of velden zijn meegegeven behorende bij de methode, en roep juiste handler op
switch($_POST['method']) {
    case 'invitecode':
        checkFields(['invitecode']);
        handleInvitecode();
    case 'login':
        checkFields(['username', 'password']);
        handleLogin();
    case 'register':
        checkFields(['username', 'password', 'password2', 'email']);
        handleRegister();
    case 'logout':
        handleLogout();
    default:
        $error = new helpers\errorHandler('Onbekende methode', 400);
        $error->sendJSON();
};

function checkFields($fields) {
    foreach($fields as $field) {
        if(empty($_POST[$field])) {
            $error = new helpers\errorHandler('Niet alle velden zijn ingevuld', 400);
            $error->sendJSON();
        }
    }
}

function handleLogin()
{
    $loginResult = objects\User::login($_POST['username'], $_POST['password']);
    if($loginResult == false) {
        $error = new helpers\errorHandler('Gebruikersnaam of wachtwoord verkeerd', 401);
        $error->sendJSON();
    };
    $_SESSION['username'] = $_POST['username'];
    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleRegister()
{
    if($_POST['password'] != $_POST['password2']) {
        $error = new helpers\errorHandler('Wachtwoorden zijn niet gelijk', 400);
        $error->sendJSON();
    }
    $result = objects\User::createUser($_POST['username'], $_POST['password'], $_POST['email']);
    if($result instanceof helpers\errorHandler) $result->sendJSON();
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $_POST['username'];
    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleInvitecode()
{
    $result = objects\Wedding::validateWeddingCode($_POST['invitecode']);
    if($result instanceof helpers\errorHandler) $result->sendJSON();
    $_SESSION['username'] = null;
    $_SESSION['weddingID'] = $result;
    $response = new helpers\successHandler;
    $response->sendJSON();
}


function handleLogout() {
    $_SESSION = array();
    session_destroy();
    $response = new helpers\successHandler();
    $response->sendJSON();
}
