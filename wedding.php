<?php
header('Content-Type: application/json');
require_once './objects/wedding.php';
require_once './objects/gift.php';
require_once './objects/user.php';
require_once 'helper/errorHandler.php';
require_once 'helper/successHandler.php';
require_once 'helper/sendMail.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    $error = new errorHandler('Alleen POST toegastaan', 400);
    $error->sendJSON();
}

$data = json_decode(file_get_contents('php://input'));
if(empty($data->method)) $data->method = null;

switch ($data->method) {
    case 'create':
        checkFields($data, ['person1', 'person2', 'weddingdate']);
        handleCreate($data->person1, $data->person2, $data->weddingdate);
    case 'linkpartner':
        checkFields($data, ['linkingcode']);
        handleLinkPartner($data->linkingcode);
    case 'updateSequence':
        checkFields($data, ['sequence']);
        handleUpdateSequence($data->sequence);
    case 'delete':
        checkFields($data, ['name']);
        handleDeleteGift($data->name);
    case 'invite':
        checkFields($data, ['email']);
        handleSendInvite($data->email);
    default:
        $error = new errorHandler('Onbekende methode', 400);
        $error->sendJSON();
}

function checkFields($data, $fields) {
    foreach($fields as $field) {
        if(empty($data->$field)) {
            $error = new errorHandler('Niet alle velden zijn ingevuld', 400);
            $error->sendJSON();
        }
    }
}

function checkUserLoggedIn()
{
    if(empty($_SESSION['username'])) {
        $error = new errorHandler('Geen gebruiker ingelogd', 403);
        $error->sendJSON();
    }
}

function handleCreate($person1, $person2, $weddingdate)
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker nog geen wedding heeft aangemaakt
    $user = User::getUser($_SESSION['username']);
    if(isset($user->wedding)) {
        $error = new errorHandler('Je hebt al een bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // maak bruiloft aan
    $result = Wedding::create($person1, $person2, $weddingdate);
    if($result instanceof errorHandler) $result->sendJSON();

    // koppel wedding ID aan user
    $user->weddingId = $result;
    $user->save();
    $response = new successHandler;
    $response->sendJSON();
}

function handleLinkPartner($linkingcode)
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker nog geen wedding heeft aangemaakt
    $user = User::getUser($_SESSION['username']);
    if(isset($user->wedding)) {
        $error = new errorHandler('Je hebt al een bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // Zoek wedding behorende bij code
    $wedding = Wedding::validateLinkingCode($linkingcode);
    if($wedding instanceof errorHandler) $wedding->sendJSON();

    // stel weding ID in bij user
    $user->weddingId = $wedding->id;
    $user->save();

    // verwijder linking code
    $wedding->linkingcode = null;
    $wedding->save();

    $response = new successHandler;
    $response->sendJSON();
}

function handleUpdateSequence($sequences)
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = User::getUser($_SESSION['username']);
    if(empty($user->wedding)) {
        $error = new errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // Loop door cadeaus en update order
    foreach ($sequences as $_seqeunce) {
        $gift = Gift::getGift($user->weddingId, $_seqeunce[0]);
        if($gift == null) {
            $error = new errorHandler('Volgorde kan niet worden verwerkt doordat ' . $_seqeunce[0] . ' niet is gevonden', 400);
            $error->sendJSON();
        }
        $gift->sequence = $_seqeunce[1];
        $saved = $gift->save();
        if($saved == false) {
            $error = new errorHandler('Volgorde kan niet worden verwerkt', 400);
            $error->sendJSON();
        }
    }
    $response = new successHandler;
    $response->sendJSON();
}

function handleDeleteGift($name)
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = User::getUser($_SESSION['username']);
    if(empty($user->wedding)) {
        $error = new errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    $result = Gift::delete($user->weddingId, $name);
    if($result == false) {
        $error = new errorHandler('Cadeau kan niet worden verwijderd', 400);
        $error->sendJSON();
    }
    $response = new successHandler;
    $response->sendJSON();
}



function handleSendInvite($email)
{
    
    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = User::getUser($_SESSION['username']);
    if(empty($user->wedding)) {
        $error = new errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // haal wedding op
    $wedding = Wedding::getWedding($user->weddingId);
    
    // verstuur mail
    $link = 'http://localhost/bruiden?code=' . $user->wedding->invitecode;
    $subject = 'Uitnodiging van ' . $user->wedding->person1 . ' en ' . $user->wedding->person2;
    $content = 'Je bent uitgenodigd door ' . $user->wedding->person1 . ' en ' . $user->wedding->person2 . 'om hun wishlist te bekijken.<br />';
    $content .= 'Ga naar <a href="' . $link . '">' . $link . '</a> om de wishlist te bekijken!';
    $message = new sendMail($subject, $email, $content);
    $result = $message->sendMail();
    if($result instanceof errorHandler) $result->sendJSON();
    $response = new successHandler;
    $response->sendJSON();
}
