<?php
require_once 'vendor/autoload.php';
require_once 'database.php';
require_once 'objects/user.php';
session_start();
$db = new Database('localhost', 'bruiden', 'root', 'rootroot');
$conn = $db->conn;
$loader = new \Twig\Loader\FilesystemLoader('views');
$twig = new \Twig\Environment($loader);
$renderOptions = array();

if(isset($_GET['code'])) checkWedding();

if (empty($_SESSION['user']) && empty($_SESSION['visitWedding'])) render("login.twig", []);
if (isset($_SESSION['user'])) handleUser();
if (isset($_SESSION['visitWedding'])) handleVisitWedding();

function handleUser()
{
  global $renderOptions;
  $user = $_SESSION['user'];
  $user->updateWedding();
  $renderOptions['naam'] = $user->gebruikersnaam;

  // gebruiker actief, maar geen bruiloft bekend
  if ($user->wedding == null) render('base.twig', ['action' => 'create']);

  // bruiloft bekend
  $user->wedding->getKados();
  $renderOptions['hasWedding'] = true;
  $renderOptions['person1'] = $user->wedding->person1;
  $renderOptions['person2'] = $user->wedding->person2;
  $renderOptions['date'] = $user->wedding->date;
  $renderOptions['invitecode'] = $user->wedding->invitecode;
  $renderOptions['kados'] = $user->wedding->kados;

  // gebruiker en bruiloft bekend, request naar bewerk bruiloft
  if (isset($_GET['action']) && $_GET['action'] == 'bewerken') render('base.twig', ['action' => 'edit']);

  // gebruiker en bruiloft bekend, request naar uitnodigen
  if (isset($_GET['action']) && $_GET['action'] == 'uitnodigen') render('base.twig', ['action' => 'invite']);

  // laat wishlist zien
  render('base.twig', ['action' => 'home']);
}

function handleVisitWedding()
{
  global $renderOptions;
  $wedding = $_SESSION['visitWedding'];
  $wedding->getKados();
  $renderOptions['person1'] = $wedding->person1;
  $renderOptions['person2'] = $wedding->person2;
  $renderOptions['date'] = $wedding->date;
  $renderOptions['kados'] = $wedding->kados;

  render('guest.twig', ['action' => 'home']);
}

function checkWedding()
{
  $wedding = new Wedding();
  $wedding->invitecode = $_GET['code'];
  if($wedding->validateWeddingCode()) $_SESSION['visitWedding'] = $wedding;
}


function render($template, $vars)
{
  global $renderOptions, $twig;
  $options = array_merge($renderOptions, $vars);
  http_response_code(200);
  echo $twig->render($template, $options);
  die();
}
