<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') die();
header('Content-Type: application/json');
require_once 'autoload.php';
set_exception_handler('sendExeptionJson');
session_start();

// controleer of er een methode is meegegeven.
if (empty($_POST['method'])) {
    throw new Exception('Geen methode meegegeven', 400);
}

// controleer aan de hand van de methode eerst of de velden juist zijn ingevuld, roep vervolgens de juiste functie aan
switch ($_POST['method']) {
    case 'create':
        helpers\validator::validatePOST(['me', 'partner', 'date']);
        handleCreate();
        break;
    case 'update':
        helpers\validator::validatePOST(['me', 'partner', 'date']);
        handleUpdate();
        break;
    case 'linkpartner':
        helpers\validator::validatePOST(['linkingcode']);
        handleLinkPartner();
        break;
    case 'updateSequence':
        helpers\validator::validatePOST(['sequence']);
        handleUpdateSequence();
        break;
    case 'createGift':
        helpers\validator::validatePOST(['name', 'summary']);
        handleCreateGift();
        break;
    case 'updateGift':
        helpers\validator::validatePOST(['oldname', 'name', 'summary']);
        handleUpdateGift();
        break;
    case 'delete':
        helpers\validator::validatePOST(['name']);
        handleDeleteGift();
        break;
    case 'invite':
        helpers\validator::validatePOST(['email']);
        handleSendInvite();
        break;
    case 'claimGift':
        helpers\validator::validatePOST(['name']);
        handleClaimGift();
        break;
    default:
        throw new Exception('Onbekende methode', 400);
}

/**
 * checkUserLoggedIn
 * Controleer of gebruiker is ingelogd.
 * Throw exception indien niet ingelogd.
 *
 * @return void
 */
function checkUserLoggedIn()
{
    if (empty($_SESSION['username'])) {
        throw new Exception('Geen gebruiker ingelogd', 403);
    }
}

/**
 * checkUserVisitsWedding
 * Controleer of gebruiker geldige wedding bezoekt.
 * Throw exception indien niet.
 *
 * @return void
 */
function checkUserVisitsWedding()
{
    if (empty($_SESSION['weddingID'])) {
        throw new Exception('Geen gebruiker ingelogd', 403);
    }
}

/**
 * checkUserHasWedding
 * Controleer of ingelogde gebruiker een wedding heeft of niet.
 * Throw exception indien niet voldoet aan $musthavewedding
 *
 * @param  User $user
 * @param  bool $musthavewedding -> true indien gebruiker wedding MOET hebben, false indien gebruiker GEEN wedding mag hebben
 * @return void
 */
function checkUserHasWedding($user, $musthavewedding)
{
    if ($musthavewedding) {
        if (empty($user->wedding)) {
            throw new Exception('Je hebt nog geen bruiloft aangemaakt', 400);
        }
    } else {
        if (isset($user->wedding)) {
            throw new Exception('Je hebt al een bruiloft aangemaakt', 400);
        }
    }
}

/**
 * handleCreate
 * Maak wedding aan
 *
 * @return void
 */
function handleCreate()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    // controleer of gebruiker nog geen wedding heeft
    checkUserHasWedding($user, false);

    // Maak wedding instance
    $wedding = objects\Wedding::create($_POST['me'], $_POST['partner'], $_POST['date']);

    // Werk gebruiker bij aan de hand van aangemaakt wedding
    $user->weddingId = $wedding->id;
    $user->person_in_wedding = 1;
    $user->save();

    // Send success JSON
    helpers\successHandler::sendJSON();
}

/**
 * handleUpdate
 * update wedding
 *
 * @return void
 */
function handleUpdate()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    // controleer of user wedding heeft
    checkUserHasWedding($user, true);

    // Als er een afbeelding is meegestuurd, verwerk deze
    if($_FILES['image']['size'] > 0) {
        $img = new helpers\imageHandler('wedding', $_FILES['image']);
        $image = $img->saveImage();
    }

    // Als er een nieuwe afbeelding is geupload en er al een afbeelding was, verwijder deze
    if (isset($image) && isset($user->wedding->image)) {
        \helpers\imageHandler::removeImage('wedding', $user->wedding->image);
    }

    // Stel nieuw afbeeldingspad in op instance (indien beschikbaar)
    if (isset($image)) {
        $user->wedding->image = $image;
    }

    // controleer wie er is ingelogd om de persoon namen aan te passen
    if ($user->person_in_wedding == 1) {
        $user->wedding->person1 = $_POST['me'];
        $user->wedding->person2 = $_POST['partner'];
    } else if ($user->person_in_wedding == 2) {
        $user->wedding->person1 = $_POST['partner'];
        $user->wedding->person2 = $_POST['me'];
    }

    $user->wedding->weddingdate = $_POST['date'];
    $user->wedding->save();

    // Zet alle variabelen vana wedding om in array voor JSON response
    $wedding_objects = array();
    $wedding_objects['wedding'] = get_object_vars($user->wedding);
    // Verwijder gifts uit array (niet nodig)
    $wedding_objects['wedding']['gifts'] = null;

    helpers\successHandler::sendJSON($wedding_objects);
}

/**
 * handleLinkPartner
 * Link gebruiker aan opgegeven linkcode
 *
 * @return void
 */
function handleLinkPartner()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    // controleer of gebruiker nog geen wedding heeft
    checkUserHasWedding($user, false);

    // Haal wedding op welke bij linkcode hoort
    $wedding = objects\Wedding::validateLinkingCode($_POST['linkingcode']);

    // stel weding ID in bij user
    $user->weddingId = $wedding->id;
    // aangezien het een linkcode betreft, is dit person2 in de wedding
    $user->person_in_wedding = 2;
    $user->save();

    // verwijder linking code
    $wedding->linkingcode = null;
    $wedding->save();

    helpers\successHandler::sendJSON();
}

/**
 * handleCreateGift
 * Maak gift aan
 *
 * @return void
 */
function handleCreateGift()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    // controleer of gebruiker wedding heeft
    checkUserHasWedding($user, true);
;
    // Als er een afbeelding is meegestuurd, verwerk deze
    if($_FILES['image']['size'] > 0) {
        $img = new helpers\imageHandler('gift', $_FILES['image']);
        $image = $img->saveImage();
    } else {
        $image = null;
    }

    // maak gift aan
    $gift = objects\Gift::create($user->weddingId, $_POST['name'], $_POST['summary'], $image);

    // Zet gift om in array tbv JSON response
    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);

    helpers\successHandler::sendJSON($gift_objects);
}

/**
 * handleUpdateGift
 * update een gift
 *
 * @return void
 */
function handleUpdateGift()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    // controleer of gebruiker wedding heeft
    checkUserHasWedding($user, true);

    // haal gift op
    $gift = objects\Gift::getGift($user->weddingId, $_POST['oldname']);

    // Als er een afbeelding is meegestuurd, verwerk deze
    if($_FILES['image']['size'] > 0) {
        $img = new helpers\imageHandler('gift', $_FILES['image']);
        $image = $img->saveImage();
    }

    // Als er een nieuwe afbeelding is geupload en er al een afbeelding was, verwijder deze
    if (isset($image) && isset($gift->image)) {
        \helpers\imageHandler::removeImage('gift', $gift->image);
    }

    // werk gift bij
    $gift->name = $_POST['name'];
    $gift->summary = $_POST['summary'];
    if (isset($image)) $gift->image = $image;
    $gift->save();

    // zet gift claimed op null tbv response (nee, bruiden mogen niet in de response zien dat hun gift is geclaimed =D)
    $gift->claimed = null;
    $gift_objects = array();
    $gift_objects['gift'] = get_object_vars($gift);
    
    helpers\successHandler::sendJSON($gift_objects);
}

/**
 * handleUpdateSequence
 * Update de volgorde van gifts bij een bepaalde wedding
 * 
 * @return void
 */
function handleUpdateSequence()
{
    checkUserLoggedIn();

    $user = \objects\User::getUser($_SESSION['username']);
    // heeft gebruiker een wedding?
    checkUserHasWedding($user, true);

    // zet form element om naar array (naam,pos,naam,pos => [naam, pos, naam, pos])
    $sequence = explode(",", $_POST['sequence']);

    // Loop door cadeaus en update order
    for ($i = 0; $i < count($sequence); $i += 2) {
        // update gift
        objects\Gift::updateSequence($sequence[$i], $user->weddingId, $sequence[$i+1]);
    }

    helpers\successHandler::sendJSON();
}

/**
 * handleDeleteGift
 * Verwijder een gift
 *
 * @return void
 */
function handleDeleteGift()
{
    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    objects\Gift::delete($user->weddingId, $_POST['name']);
    helpers\successHandler::sendJSON();
}

/**
 * handleClaimGift
 * Claim een gift
 *
 * @return void
 */
function handleClaimGift()
{
    checkUserVisitsWedding();

    // Claim gift
    \objects\Gift::claimGift($_POST['name'], $_SESSION['weddingID']);

    helpers\successHandler::sendJSON();
}

/**
 * handleSendInvite
 * Stuur uitnodiginsmail naar gast
 *
 * @return void
 */
function handleSendInvite()
{

    checkUserLoggedIn();

    $user = objects\User::getUser($_SESSION['username']);
    checkUserHasWedding($user, true);

    // verstuur mail
    $link = DOMAIN.'?code='.$user->wedding->invitecode;

    $subject = 'Uitnodiging van '.$user->wedding->person1.' en '.$user->wedding->person2;

    $content = 'Je bent uitgenodigd door '.$user->wedding->person1.' en '.$user->wedding->person2.'om hun wishlist te bekijken.<br />';
    $content.= 'Ga naar <a href="'.$link.'">'.$link.'</a> om de wishlist te bekijken!';

    // Stuur mail via helper
    $message = new helpers\sendMail($subject, $_POST['email'], $content);
    $message->sendMail();

    helpers\successHandler::sendJSON();
}
