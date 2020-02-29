<?php
require './objects/wedding.php';
require './objects/user.php';
session_start();
if(empty($_SESSION)) sendError('Niet ingelogd');
header('Content-Type: application/json');
//if($_SERVER['REQUEST_METHOD'] !== "POST") die();
$data = json_decode(file_get_contents('php://input'));
if((!isset($data->person1) || !isset($data->person2) || !isset($data->date))) sendError('Niet alle velden ingevuld');

if($_SERVER['REQUEST_METHOD'] == "POST") {
    $wedding = new Wedding();
    $wedding->userid = $_SESSION['user']->id;
    $wedding->person1 = $data->person1;
    $wedding->person2 = $data->person2;
    $wedding->date = $data->date;
    if($wedding->create()) {
        http_response_code(200);
        echo json_encode(array('status' => 'successful'));
    } else {
        http_response_code(401);
        echo json_encode(array('status' => 'fail', 'message' => 'Bruiloft kan niet worden aangemaakt'));
    }
}

function sendError($bericht) {
    http_response_code(401);
    echo json_encode(array('status' => 'fail', 'message' => $bericht));
    die();
}
?>