<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die();
header('Content-Type: application/json');
require_once 'autoload.php';
set_exception_handler('sendExeptionJson');
session_start();

if (empty($_POST['method'])) throw new Exception('Geen methode meegegeven', 400);

switch ($_POST['method']) {
    case 'create':
        checkFields(['person1', 'person2', 'date']);
        handleCreate();
        break;
    case 'update':
        checkFields(['person1', 'person2', 'date']);
        handleUpdate();
        break;
    case 'linkpartner':
        checkFields(['linkingcode']);
        handleLinkPartner();
        break;
    case 'updateSequence':
        checkFields(['sequence']);
        handleUpdateSequence();
        break;
    case 'createGift':
        checkFields(['name', 'summary']);
        handleCreateGift();
        break;
    case 'updateGift':
        checkFields(['oldname', 'name', 'summary']);
        handleUpdateGift();
        break;
    case 'delete':
        checkFields(['name']);
        handleDeleteGift();
        break;
    case 'invite':
        checkFields(['email']);
        handleSendInvite();
        break;
    case 'claimGift':
        checkFields(['name']);
        handleClaimGift();
        break;
    default:
        throw new Exception('Onbekende methode', 400);
}

function checkFields($fields)
{
    foreach ($fields as $field) {
        if (empty($_POST[$field])) throw new Exception('Niet alle velden zijn ingevuld', 400);
    }
}

function checkUserLoggedIn()
{
    if (empty($_SESSION['username'])) throw new Exception('Geen gebruiker ingelogd', 403);
}

function checkUserVisitsWedding()
{
    if (empty($_SESSION['weddingID'])) throw new Exception('Geen gebruiker ingelogd', 403);
}

function checkUserHasWedding($user, $musthavewedding)
{
    if($musthavewedding) {
        if (empty($user->wedding)) throw new Exception('Je hebt nog geen bruiloft aangemaakt', 400);
    } else {
        if (isset($user->wedding)) throw new Exception('Je hebt al een bruiloft aangemaakt', 400);
    }
}

function handleImage()
{
    if (($_FILES['image']['size'] > 0)) {
        try { 
            $img = new \helpers\imageHandler('gift', $_FILES['image']);
            $image = $img->saveImage();
            return $image;
        } catch(Exception $e) { 
            $imageError = $e->getMessage();
            return null;
        }
    } else {
        return null;
    }
}

function handleCreate()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, false);

    $wedding = objects\Wedding::create($_POST['person1'], $_POST['person2'], $_POST['date']);

    $user->weddingId = $wedding->get('id');
    $user->save();
    helpers\successHandler::sendJSON();
}

function handleUpdate()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    $user->wedding->person1 = $_POST['person1'];
    $user->wedding->person2 = $_POST['person2'];
    $user->wedding->weddingdate = $_POST['date'];
    $user->wedding->save();

    helpers\successHandler::sendJSON();
}

function handleLinkPartner()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, false);

    $wedding = objects\Wedding::validateLinkingCode($_POST['linkingcode']);

    // stel weding ID in bij user
    $user->weddingId = $wedding->get('id');
    $user->save();

    // verwijder linking code
    $wedding->linkingcode = null;
    $wedding->save();

    helpers\successHandler::sendJSON();
}

function handleCreateGift()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    $image = handleImage();

    $gift = objects\Gift::create($user->weddingId, $_POST['name'], $_POST['summary'], $image);

    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);
    helpers\successHandler::sendJSON($gift_objects);
}

function handleUpdateGift()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    $gift = objects\Gift::getGift($user->weddingId, $_POST['oldname']);

    // handle image
    $image = handleImage();
    if (isset($image) && isset($gift->image)) \helpers\imageHandler::removeImage('gift', $gift->image);

    $gift->name = $_POST['name'];
    $gift->summary = $_POST['summary'];
    if (isset($image)) $gift->image = $image;
    $gift->save();

    $gift->claimed = null;
    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);
    helpers\successHandler::sendJSON($gift_objects);
}

function handleUpdateSequence()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    // zet form element om naar array
    $sequence = explode(",", $_POST['sequence']);

    // Loop door cadeaus en update order
    for ($i = 0; $i < count($sequence); $i += 2) {
        $gift = objects\Gift::getGift($user->weddingId, $sequence[$i]);
        $gift->sequence = $sequence[$i + 1];
        $gift->save();
    }

    helpers\successHandler::sendJSON();
}

function handleDeleteGift()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    objects\Gift::delete($user->weddingId, $_POST['name']);
    helpers\successHandler::sendJSON();
}

function handleClaimGift()
{
    checkUserVisitsWedding();

    $gift = \objects\Gift::getGift($_SESSION['weddingID'], $_POST['name']);

    $gift->claimed = true;
    try { $result = $gift->save(); }
    catch(Exception $e) { throw new Exception('Cadeau kan niet worden geclaimed, probeer het later nogmaals', 400); }

    helpers\successHandler::sendJSON();
}

function handleSendInvite()
{

    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    // haal wedding op
    $wedding = objects\Wedding::getWedding($user->weddingId);

    // verstuur mail
    $link = 'http://localhost/bruiden?code=' . $user->wedding->invitecode;
    $subject = 'Uitnodiging van ' . $user->wedding->person1 . ' en ' . $user->wedding->person2;
    $content = 'Je bent uitgenodigd door ' . $user->wedding->person1 . ' en ' . $user->wedding->person2 . 'om hun wishlist te bekijken.<br />';
    $content .= 'Ga naar <a href="' . $link . '">' . $link . '</a> om de wishlist te bekijken!';
    $message = new helpers\sendMail($subject, $_POST['email'], $content);
    $message->sendMail();

    helpers\successHandler::sendJSON();
}
