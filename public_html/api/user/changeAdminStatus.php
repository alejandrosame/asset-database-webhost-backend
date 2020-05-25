<?php
include_once '../config/database.php';
include_once '../objects/user.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isAdmin();

$database = new Database();
$db = $database->getConnection();

// get id of user to be updated
$data = json_decode(file_get_contents("php://input"));

if ($data->id == $user->id) {
    http_response_code(400);
    echo json_encode(array("message" => "Illegal operation."));
    exit();
}

$user = new User($db);
$user->id = $data->id;
if ($user->updateAdminStatus()) {
    http_response_code(200);
    echo json_encode(array("message" => "User admin status was updated."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to update user admin status."));
}
