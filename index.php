<?php
require_once 'autoload.php';
session_start();
$db = new Database();
$conn = $db->conn;
$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader, ['debug' => true]);
$twig->addExtension(new \Twig\Extension\DebugExtension());

if(isset($_GET['code'])) checkWeddingCode();

if (empty($_SESSION['username']) && empty($_SESSION['weddingID'])) showLogin([]);
if (isset($_SESSION['username'])) handleUser();
if (isset($_SESSION['weddingID'])) handleVisitWedding();

function handleUser()
{
  $renderOptions = array();

  try { $user = objects\User::getUser($_SESSION['username']); }
  catch(Exception $e) { showLogin([]); }

  $renderOptions['name'] = $user->get('username');

  // gebruiker actief, maar geen bruiloft bekend
  if ($user->wedding == null) {
    $renderOptions['action'] = 'create';
    render('base.twig', $renderOptions);
  }

  // bruiloft bekend, stel afbeeldingpaden in
  foreach($user->wedding->gifts as $gift) {
    if($gift->image) $gift->imageSrc = GIFTS_IMG_PATH.$gift->image;
    else $gift->imageSrc = GIFTS_IMG_PATH.'default.png';
  }
  $renderOptions['hasWedding'] = true;
  $renderOptions['person1'] = $user->wedding->person1;
  $renderOptions['person2'] = $user->wedding->person2;
  $renderOptions['date'] = $user->wedding->weddingdate;
  $renderOptions['invitecode'] = $user->wedding->invitecode;
  $renderOptions['gifts'] = $user->wedding->gifts;
  $renderOptions['linkingcode'] = $user->wedding->linkingcode;

  if($user->wedding->image) $renderOptions['image']= WEDDINGS_IMG_PATH.$user->wedding->image;
  else $renderOptions['image'] = WEDDINGS_IMG_PATH.'default.jpg';
  

  // gebruiker en bruiloft bekend, request naar bewerk bruiloft
  if (isset($_GET['action']) && $_GET['action'] == 'bewerken') {
    $renderOptions['action'] = 'edit';
    render('base.twig', $renderOptions);
  }

  // gebruiker en bruiloft bekend, request naar uitnodigen
  if (isset($_GET['action']) && $_GET['action'] == 'uitnodigen') {
    $renderOptions['action'] = 'invite';
    render('base.twig', $renderOptions);
  }

  // laat wishlist zien
  $renderOptions['action'] = 'gifts';
  render('base.twig', $renderOptions);
}

function checkWeddingCode()
{
  // controleer of geen vreemde tekens bevat
  if(preg_match("/\W/", $_GET['code'])) showLogin(['errormessage' => 'Deze link is niet geldig']);

  // Zoek wedding, bij Exception stuur error
  try { $wedding = objects\Wedding::validateWeddingCode($_GET['code']); }
  catch(Exception $e) { showLogin(['errormessage' => 'Deze link is niet geldig']); }

  // stel wedding ID in in session
  $_SESSION['username'] = null;
  $_SESSION['weddingID'] = $wedding->get('id');
}

function handleVisitWedding()
{
  $renderOptions = array();
  try { $wedding = objects\Wedding::getWedding($_SESSION['weddingID']); }
  catch(Exception $e) { showLogin([]); }

  $renderOptions['person1'] = $wedding->person1;
  $renderOptions['person2'] = $wedding->person2;
  $renderOptions['date'] = $wedding->weddingdate;
  $renderOptions['gifts'] = $wedding->gifts;
  $renderOptions['action'] = 'home';

  // stel afbeeldingpaden en enabled / disabled button in
  foreach($wedding->gifts as $gift) {
    if($gift->claimed) $gift->disabled = 'disabled';
    else $gift->disabled = null;
    if($gift->image) $gift->imageSrc = GIFTS_IMG_PATH.$gift->image;
    else $gift->imageSrc = GIFTS_IMG_PATH.'default.png';
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
