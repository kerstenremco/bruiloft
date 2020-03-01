<?php
header('Content-Type: application/json');
require_once './objects/wedding.php';
require_once './objects/user.php';
require_once 'helper/error.php';
if ($_SERVER['REQUEST_METHOD'] !== "POST") sendError('Alleen POST toegestaan!');
session_start();
if (empty($_SESSION) || empty($_SESSION['user'])) sendError('Niet ingelogd');
$data = json_decode(file_get_contents('php://input'));
if (empty($data->method) || $data->method !== 'create' && $data->method !== 'updateOrder' && $data->method !== 'deleteKado') sendError('Methode niet bekend');

if ($data->method == 'create') handleCreate();
if ($data->method == 'updateOrder') handleUpdateOrder();
if ($data->method == 'deleteKado') handleDeleteKado();

function handleCreate()
{
    global $data;
    if ((!isset($data->person1) || !isset($data->person2) || !isset($data->date))) sendError('Niet alle velden ingevuld');

    $wedding = new Wedding();
    $wedding->userid = $_SESSION['user']->id;
    $wedding->person1 = $data->person1;
    $wedding->person2 = $data->person2;
    $wedding->date = $data->date;
    if ($wedding->create()) {
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        http_response_code(401);
        echo json_encode(array('status' => 'fail', 'message' => 'Bruiloft kan niet worden aangemaakt'));
    }
}

function handleUpdateOrder()
{
    global $data;
    $wedding = $_SESSION['user']->wedding;
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
}

function handleDeleteKado()
{
    global $data;
    $deleted = false;
    $wedding = $_SESSION['user']->wedding;
    $kados = $wedding->kados;
    foreach ($kados as $kado) {
        if ($kado->id == $data->id) {
            $deleted = $kado->delete();
            break;
        }
    }
    if($deleted) {
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        http_response_code(401);
        echo json_encode(array('status' => 'fail', 'message' => 'Kado kan niet worden verwijderd'));
    }
}
