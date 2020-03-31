<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die();
header('Content-Type: application/json');
require_once 'autoload.php';
set_exception_handler('sendExeptionJson');
session_start();

if (empty($_POST['method'])) {
    throw new Exception('Geen methode meegegeven', 400);
}

// Controleer of velden zijn meegegeven behorende bij de methode, en roep juiste handler op
switch ($_POST['method']) {
    case 'invitecode':
        helpers\validator::validatePOST(['invitecode']);
        handleInvitecode();
        break;
    case 'login':
        helpers\validator::validatePOST(['username', 'password']);
        handleLogin();
        break;
    case 'register':
        helpers\validator::validatePOST(['username', 'password', 'password2', 'email']);
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        throw new Exception('Onbekende methode', 400);
}

/**
 * handleLogin
 * Haal opgegeven gebruiker op en zet, indien gevonden, username in sessie
 *
 * @return void
 */
function handleLogin()
{
    $user = objects\User::login($_POST['username'], $_POST['password']);
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $user->username;
    helpers\successHandler::sendJSON();
}

/**
 * handleRegister
 * Registreer opgegeven gebruiker, en zet indien aangemaakt, username in sessie
 *
 * @return void
 */
function handleRegister()
{
    $user = objects\User::createUser($_POST['username'], $_POST['password'], $_POST['password2'], $_POST['email']);
    $_SESSION['weddingID'] = null;
    $_SESSION['username'] = $user->username;
    helpers\successHandler::sendJSON();
}

/**
 * handleInvitecode
 * Controleer wedding code en zet indien gevonden, weddingID in sessie
 *
 * @return void
 */
function handleInvitecode()
{
    $wedding = objects\Wedding::validateWeddingCode($_POST['invitecode']);
    $_SESSION['username'] = null;
    $_SESSION['weddingID'] = $wedding->id;
    helpers\successHandler::sendJSON();
}


/**
 * handleLogout
 *
 * @return void
 */
function handleLogout()
{
    $_SESSION = array();
    session_destroy();
    helpers\successHandler::sendJSON();
}
