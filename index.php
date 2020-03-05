<?php
require_once 'vendor/autoload.php';
require_once 'database.php';
require_once 'objects/user.php';
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
  $user = User::getUser($_SESSION['username']);
  if($user == false) showLogin(['errormessage' => 'Sessie is verlopen, log opnieuw in.']);
  $renderOptions['name'] = $_SESSION['username'];

  // gebruiker actief, maar geen bruiloft bekend
  if ($user->wedding == null) {
    $renderOptions['action'] = 'create';
    render('base.twig', $renderOptions);
  }

  // bruiloft bekend
  $renderOptions['hasWedding'] = true;
  $renderOptions['person1'] = $user->wedding->person1;
  $renderOptions['person2'] = $user->wedding->person2;
  $renderOptions['date'] = $user->wedding->weddingdate;
  $renderOptions['invitecode'] = $user->wedding->invitecode;
  $renderOptions['gifts'] = $user->wedding->gifts;
  $renderOptions['linkingcode'] = $user->wedding->linkingcode;
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
  $result = Wedding::validateWeddingCode($_GET['code']);
  if($result instanceof errorHandler) {
    showLogin(['errormessage' => $result->errorMessage]);
  }
  $_SESSION['weddingID'] = $result;
}

function handleVisitWedding()
{
  $renderOptions = array();
  $wedding = Wedding::getWedding($_SESSION['weddingID']);
  if($wedding == null) showLogin(['errormessage' => 'Bruiloft bestaat niet meer']);
  $renderOptions['person1'] = $wedding->person1;
  $renderOptions['person2'] = $wedding->person2;
  $renderOptions['date'] = $wedding->weddingdate;
  $renderOptions['gifts'] = $wedding->gifts;
  $renderOptions['action'] = 'home';
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
