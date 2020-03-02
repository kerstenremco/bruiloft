<?php
header('Content-Type: application/json');
require_once './objects/wedding.php';
require_once './objects/kado.php';
require_once './objects/user.php';
require_once 'helper/error.php';
require_once 'helper/sendSuccessHttp.php';
if ($_SERVER['REQUEST_METHOD'] !== "POST") sendError('Alleen POST toegestaan!', 405);
session_start();
if (empty($_SESSION) || empty($_SESSION['userID'])) sendError('Niet ingelogd', 401);
$data = json_decode(file_get_contents('php://input'));
if (empty($data->method)) sendError('Geen methode meegegeven', 400);

switch ($data->method) {
    case 'create':
        handleCreate();
        break;
    case 'linkpartner':
        handleLinkPartner();
        break;
    case 'updateOrder':
        handleUpdateOrder();
        break;
    case 'deleteKado':
        handleDeleteKado();
        break;
    case 'invite':
        handleSendInvite();
        break;
    default:
        sendError('Methode niet bekend');
}

function handleCreate()
{
    global $data;
    if ((!isset($data->person1) || !isset($data->person2) || !isset($data->date))) sendError('Niet alle velden ingevuld', 400);
    $wedding = new Wedding();
    $wedding->userid = $_SESSION['userID'];
    $wedding->person1 = $data->person1;
    $wedding->person2 = $data->person2;
    $wedding->date = $data->date;
    $wedding->create() ? sendSuccessHttp() : sendError();
}

function handleUpdateOrder()
{
    global $data;
    $wedding = new Wedding();
    $wedding->userid = $_SESSION['userID'];
    if ($wedding->getWedding('user') == false) sendError();
    $kados = $wedding->kados;
    foreach ($data->order as $item) {
        $index = $item[0];
        $order = $item[1];
        foreach ($kados as $kado) {
            if ($kado->id == $index) {
                $kado->order = $order;
                $kado->save();
            }
        }
    }
    sendSuccessHttp();
}

function handleDeleteKado()
{
    global $data;
    $kado = new Kado();
    $kado->id = $data->id;
    $kado->delete() ? sendSuccessHttp() : sendError();
}

function handleLinkPartner()
{
    global $data;
    $wedding = new Wedding();
    $wedding->partneruserid = $_SESSION['userID'];
    $wedding->linkingcode = $data->linkingcode;
    $wedding->connectToPartner() ? sendSuccessHttp() : sendError();
}

function handleSendInvite()
{
    require_once './vendor/autoload.php';
    global $data;
    $wedding = new Wedding();
    $wedding->userid = $_SESSION['userID'];
    if ($wedding->getWedding('user') == false)  sendError();
    try {
        // Create the SMTP transport
        $transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 2525))
            ->setUsername('f1ffc8f4a696e9')
            ->setPassword('8d487d46a45a65');

        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = new Swift_Message();

        $message->setSubject('Uitnodiging van ' . $wedding->person1 . ' en ' . $wedding->person1);
        $message->setFrom(['info@bruidenapp.nl' => 'BruidenApp']);
        $message->addTo($data->email);

        // Set the plain-text part
        //$message->setBody('Hi there, we are happy to confirm your booking. Please check the document in the attachment.');
        // Set the HTML part
        $message->addPart('Je persoonlijke code om in te loggen is <b>' . $wedding->invitecode . '</b>', 'text/html');
        // Send the message
        $result = $mailer->send($message);
        sendSuccessHttp();
    } catch (Exception $e) {
        sendError('Maildienst buiten gebruik', 503);
    }
}
