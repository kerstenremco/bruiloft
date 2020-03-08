<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') die();
header('Content-Type: application/json');
require_once 'autoload.php';
set_exception_handler('sendExeptionJson');
session_start();

if (empty($_POST['method'])) throw new Exception('Geen methode meegegeven', 400);

// Controleer of velden zijn meegegeven behorende bij de methode, en roep juiste handler op
switch ($_POST['method']) {
    case 'invitecode':
        checkFields(['invitecode']);
        handleInvitecode();
        break;
    case 'login':
        checkFields(['username', 'password']);
        handleLogin();
        break;
    case 'register':
        checkFields(['username', 'password', 'password2', 'email']);
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        throw new Exception('Onbekende methode', 400);
};

function checkFields($fields)
{
    foreach ($fields as $field) {
        if (empty($_POST[$field])) throw new Exception('Niet alle velden zijn ingevuld', 400);
    }
}

function handleLogin()
{
    $user = objects\User::login($_POST['username'], $_POST['password']);
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $user->get('username');
    helpers\successHandler::sendJSON();
}

function handleRegister()
{
    $user = objects\User::createUser($_POST['username'], $_POST['password'], $_POST['password2'], $_POST['email']);
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $user->get('username');
    helpers\successHandler::sendJSON();
}

function handleInvitecode()
{
    $wedding = objects\Wedding::validateWeddingCode($_POST['invitecode']);
    $_SESSION['username'] = null;
    $_SESSION['weddingID'] = $wedding->get('id');
    helpers\successHandler::sendJSON();
}


function handleLogout()
{
    $_SESSION = array();
    session_destroy();
    helpers\successHandler::sendJSON();
}
