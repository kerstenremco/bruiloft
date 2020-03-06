<?php
header('Content-Type: application/json');
require_once 'autoload.php';
session_start();

if (empty($_POST['method'])) $_POST['method'] = null;
$file = $_FILES;
switch ($_POST['method']) {
    case 'create':
        checkFields(['person1', 'person2', 'date']);
        handleCreate();
    case 'update':
        checkFields(['person1', 'person2', 'date']);
        handleUpdate();
    case 'linkpartner':
        checkFields(['linkingcode']);
        handleLinkPartner();
    case 'updateSequence':
        checkFields(['sequence']);
        handleUpdateSequence();
    case 'createGift':
        checkFields(['name', 'summary']);
        handleCreateGift();
    case 'updateGift':
        checkFields(['oldname','name', 'summary']);
        handleUpdateGift();
    case 'delete':
        checkFields(['name']);
        handleDeleteGift();
    case 'invite':
        checkFields(['email']);
        handleSendInvite();
    case 'claimGift':
        checkFields(['name']);
        handleClaimGift();
    default:
        $error = new helpers\errorHandler('Onbekende methode', 400);
        $error->sendJSON();
}

function checkFields($fields)
{
    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            $error = new helpers\errorHandler('Niet alle velden zijn ingevuld', 400);
            $error->sendJSON();
        }
    }
}

function checkUserLoggedIn()
{
    if (empty($_SESSION['username'])) {
        $error = new helpers\errorHandler('Geen gebruiker ingelogd', 403);
        $error->sendJSON();
    }
}

function checkUserVisitsWedding()
{
    if (empty($_SESSION['weddingID'])) {
        $error = new helpers\errorHandler('Je bezoekt geen bruiloft', 403);
        $error->sendJSON();
    }
}

function handleCreate()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker nog geen wedding heeft aangemaakt
    $user = objects\User::getUser($_SESSION['username']);
    if (isset($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt al een bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // maak bruiloft aan
    $result = objects\Wedding::create($_POST['person1'], $_POST['person2'], $_POST['date']);
    if ($result instanceof helpers\errorHandler) $result->sendJSON();

    // koppel wedding ID aan user
    $user->weddingId = $result;
    $user->save();
    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleUpdate()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker wedding heeft
    $user = objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt geen bruiloft aangemaakt', 404);
        $error->sendJSON();
    }

    // pas bruiloft aan
    $user->wedding->person1 = $_POST['person1'];
    $user->wedding->person2 = $_POST['person2'];
    $user->wedding->weddingdate = $_POST['date'];
    $result = $user->wedding->save();
    if ($result == false) {
        $error = new helpers\errorHandler('Bruiloft kan niet worden bijgewerkt, probeer het later nogmaals.', 503);
        $error->sendJSON();
    }

    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleLinkPartner()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker nog geen wedding heeft aangemaakt
    $user = objects\User::getUser($_SESSION['username']);
    if (isset($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt al een bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // Zoek wedding behorende bij code
    $wedding = objects\Wedding::validateLinkingCode($_POST['linkingcode']);
    if ($wedding instanceof helpers\errorHandler) $wedding->sendJSON();

    // stel weding ID in bij user
    $user->weddingId = $wedding->id;
    $user->save();

    // verwijder linking code
    $wedding->linkingcode = null;
    $wedding->save();

    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleCreateGift()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker  wedding heeft aangemaakt
    $user = \objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // handle image
    $image = null;
    if(isset($_FILES['image'])) {
        $img = new \helpers\imageHandler('gift', $_FILES['image']);
        $result = $img->saveImage();
        if(($result instanceof \helpers\errorHandler) == false) $image = $result;
    }

    // maak cadeau
    $gift = objects\Gift::create($user->weddingId, $_POST['name'], $_POST['summary'], $image);

    if ($gift == null) {
        $error = new helpers\errorHandler('Gift kan niet worden aangemaakt, probeer het later nogmaals', 503);
        $error->sendJSON();
    }

    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);
    $response = new helpers\successHandler($gift_objects, 200);
    $response->sendJSON();
}

function handleUpdateGift()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker  wedding heeft aangemaakt
    $user = \objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    $gift = objects\Gift::getGift($user->weddingId, $_POST['oldname']);
        if ($gift == null) {
        $error = new helpers\errorHandler('Gift niet gevonden.', 404);
        $error->sendJSON();
    }

    // handle image
    if(isset($_FILES['image'])) {
        $img = new \helpers\imageHandler('gift', $_FILES['image']);
        $result = $img->saveImage();
        if(($result instanceof \helpers\errorHandler) == false) {
            $image = $result;
            if(isset($gift->image)) \helpers\imageHandler::removeImage('gift', $gift->image);
        };
    }

    $gift->name = $_POST['name'];
    $gift->summary = $_POST['summary'];
    if(isset($image)) $gift->image = $image;
    $result = $gift->save();

    if($result == false) {
        $error = new helpers\errorHandler('Updaten niet gelukt, probeer het later nogmaals', 503);
        $error->sendJSON();
    }

    $gift->claimed = null;
    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);
    $response = new helpers\successHandler($gift_objects, 200);
    $response->sendJSON();
}

function handleUpdateSequence()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // zet form element om naar array
    $sequence = explode(",", $_POST['sequence']);

    // Loop door cadeaus en update order
    for ($i = 0; $i < count($sequence); $i+=2) {
        $gift = objects\Gift::getGift($user->weddingId, $sequence[$i]);
        if ($gift == null) {
            $error = new helpers\errorHandler('Volgorde kan niet worden verwerkt doordat ' . $sequence[0] . ' niet is gevonden', 400);
            $error->sendJSON();
        }
        $gift->sequence = $sequence[$i+1];
        $saved = $gift->save();
        if ($saved == false) {
            $error = new helpers\errorHandler('Volgorde kan niet worden verwerkt', 400);
            $error->sendJSON();
        }
    }

    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleDeleteGift()
{
    // controleer of gebruiker is ingelogd
    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    $result = objects\Gift::delete($user->weddingId, $_POST['name']);
    if ($result == false) {
        $error = new helpers\errorHandler('Cadeau kan niet worden verwijderd', 400);
        $error->sendJSON();
    }
    $response = new helpers\successHandler;
    $response->sendJSON();
}

function handleClaimGift()
{
    checkUserVisitsWedding();

    $gift = \objects\Gift::getGift($_SESSION['weddingID'], $_POST['name']);

    if (empty($gift)) {
        $error = new helpers\errorHandler('Cadeau is niet gevonden', 404);
        $error->sendJSON();
    }

    $gift->claimed = true;
    $result = $gift->save();

    if ($result == false) {
        $error = new helpers\errorHandler('Cadeau kan niet worden geclaimed, probeer het later nogmaals', 400);
        $error->sendJSON();
    }

    $response = new helpers\successHandler;
    $response->sendJSON();
}



function handleSendInvite()
{

    checkUserLoggedIn();

    // controleer of gebruiker een wedding heeft
    $user = objects\User::getUser($_SESSION['username']);
    if (empty($user->wedding)) {
        $error = new helpers\errorHandler('Je hebt nog geen bruiloft aangemaakt', 400);
        $error->sendJSON();
    }

    // haal wedding op
    $wedding = objects\Wedding::getWedding($user->weddingId);

    // verstuur mail
    $link = 'http://localhost/bruiden?code=' . $user->wedding->invitecode;
    $subject = 'Uitnodiging van ' . $user->wedding->person1 . ' en ' . $user->wedding->person2;
    $content = 'Je bent uitgenodigd door ' . $user->wedding->person1 . ' en ' . $user->wedding->person2 . 'om hun wishlist te bekijken.<br />';
    $content .= 'Ga naar <a href="' . $link . '">' . $link . '</a> om de wishlist te bekijken!';
    $message = new helpers\sendMail($subject, $_POST['email'], $content);
    $result = $message->sendMail();
    if ($result instanceof helpers\errorHandler) $result->sendJSON();
    $response = new helpers\successHandler;
    $response->sendJSON();
}
