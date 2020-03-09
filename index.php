<?php
require_once 'autoload.php';
session_start();
$db = new Database();
$conn = $db->conn;
$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

// als er een code is meegegeven, controleer code
if(isset($_GET['code'])) {
  checkWeddingCode();
}

// als er geen username of weddingID is sessie bekend is, laat login zien
if (empty($_SESSION['username']) && empty($_SESSION['weddingID'])) {
  showLogin([]);
}

// als username is sessie bekend is, hande user
if (isset($_SESSION['username'])) {
  handleUser();
}

// als weddingID bekend is in sessie, handle visit wedding
if (isset($_SESSION['weddingID'])) {
  handleVisitWedding();  
}

/**
 * handleUser
 * Wordt aangeroepen als er een user bekend is in de sessie en rendert de juiste pagina
 *
 * @return void
 */
function handleUser() {
  $renderOptions = array();

  // probeer gebruiker te vinden, indien niet bestaat, laat login pagina zien
  try { 
    $user = objects\User::getUser($_SESSION['username']);
  } catch(Exception $e) { 
    showLogin([]); 
  }

  $renderOptions['name'] = $user->username;

  // Als er geen bruiloft is gekoppeld aan de gebruiker, render template voor create wedding
  if ($user->wedding == null) {
    $renderOptions['action'] = 'create';
    $renderOptions['image'] = WEDDINGS_IMG_PATH.'default.jpg';
    render('base.twig', $renderOptions);
  }

  // Doorloop gifts van de wedding en stel de image paden in tbv template
  foreach($user->wedding->gifts as $gift) {
    if($gift->image) {
      // er is een afbeelding geupload bij deze gift
      $gift->imageSrc = GIFTS_IMG_PATH.$gift->image;
      $gift->ownimage = 'ownimage';
    } else {
      // er is geen afbeelding, stel default in
      $gift->imageSrc = GIFTS_IMG_PATH.'default.png';
    }
  }

  $renderOptions['hasWedding'] = true;

  // zet personen in bruiloft goed
  if($user->person_in_wedding == 1) {
    $renderOptions['me'] = $user->wedding->person1;
    $renderOptions['partner'] = $user->wedding->person2;
  } else if($user->person_in_wedding == 2) {
    $renderOptions['me'] = $user->wedding->person2;
    $renderOptions['partner'] = $user->wedding->person1;
  }

  $renderOptions['date'] = $user->wedding->weddingdate;
  $renderOptions['invitecode'] = $user->wedding->invitecode;
  $renderOptions['gifts'] = $user->wedding->gifts;
  $renderOptions['linkingcode'] = $user->wedding->linkingcode;

  // stel wedding image in tbv template
  if($user->wedding->image) {
    $renderOptions['image']= WEDDINGS_IMG_PATH.$user->wedding->image;
  } else {
    $renderOptions['image'] = WEDDINGS_IMG_PATH.'default.jpg';
  }
  
  // Wordt er een specieke pagina opgevraagd? Render deze
  if(isset($_GET['action'])) {
    if ($_GET['action'] == 'bewerken') {
      $renderOptions['action'] = 'edit';
      render('base.twig', $renderOptions);
    } else if ($_GET['action'] == 'uitnodigen') {
      $renderOptions['action'] = 'invite';
      render('base.twig', $renderOptions);
    }
  }
  
  // Render default pagina (wishlist)
  $renderOptions['action'] = 'gifts';
  render('base.twig', $renderOptions);
}

/**
 * checkWeddingCode
 * Controleert wedding code. Indien goed, stel weddingID in in session. Indien fout, laat foutmelding zien.
 *
 * @return void
 */
function checkWeddingCode()
{
  // controleer of code geen vreemde tekens bevat
  if(preg_match("/\W/", $_GET['code'])) {
    showLogin(['errormessage' => 'Deze link is niet geldig']);
  }

  // Zoek wedding, bij Exception stuur error
  try { 
    $wedding = objects\Wedding::validateWeddingCode($_GET['code']); 
  } catch(Exception $e) { 
    showLogin(['errormessage' => 'Deze link is niet geldig']);
  }

  // stel wedding ID in in session
  $_SESSION['username'] = null;
  $_SESSION['weddingID'] = $wedding->id;
}

/**
 * handleVisitWedding
 * Probeer wedding te zoeken op basis van ID in sessie. Indien goed, render juiste pagina. Indien niet bestaat, laat inlogpagna zien.
 *
 * @return void
 */
function handleVisitWedding()
{
  $renderOptions = array();

  // Zoek wedding. Indien niet bestaat, laat inlogpagina zien
  try { 
    $wedding = objects\Wedding::getWedding($_SESSION['weddingID']);
  } catch(Exception $e) { 
    showLogin([]);
  }

  $renderOptions['person1'] = $wedding->person1;
  $renderOptions['person2'] = $wedding->person2;
  $renderOptions['date'] = $wedding->weddingdate;
  $renderOptions['gifts'] = $wedding->gifts;
  $renderOptions['action'] = 'home';

  // stel afbeeldingpaden en enabled / disabled button in per gift tbv template
  foreach($wedding->gifts as $gift) {
    // stel disabled / null in
    if($gift->claimed) {
      $gift->disabled = 'disabled';
    } else {
      $gift->disabled = null;
    }

    // stel afbeeldingpad in
    if($gift->image) {
      $gift->imageSrc = GIFTS_IMG_PATH.$gift->image;
      $gift->ownimage = 'ownimage';
    } else {
      $gift->imageSrc = GIFTS_IMG_PATH.'default.png';
    }
  }

  render('guest.twig', $renderOptions);
}

function showLogin($vars)
{
  $_SESSION = array();
  session_destroy();
  render("login.twig", $vars);
}


function render($template, $vars)
{
  global $twig;
  http_response_code(200);
  echo $twig->render($template, $vars);
  die();
}
?>