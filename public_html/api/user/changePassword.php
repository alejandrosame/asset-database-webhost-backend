<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/user.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

// get username of user to be updated
$data = json_decode(file_get_contents("php://input"));
if ($data->username != $user->username && !boolval($user->isadmin)) {
    http_response_code(400);
    echo json_encode(array("message" => "Illegal operation."));
    exit();
}

$user = new User($db);
$user->username = $data->username;
$user->password = $data->password;
if ($user->updatePassword()) {
    http_response_code(200);
    echo json_encode(array("message" => "User password was updated."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to update user password."));
}
