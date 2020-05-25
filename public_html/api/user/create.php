<?php
include_once '../config/database.php';
include_once '../objects/user.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isAdmin();

$database = new Database();
$db = $database->getConnection();

$product = new User($db);
$data = json_decode(file_get_contents("php://input"));

if (
  !empty($data->username) and !empty($data->password) and !empty($data->admin)
) {
    if ($user->create($data->username, $data->password, $data->admin)) {
        http_response_code(201);
        echo json_encode(array(
          "id" => $user->id,
          "username" => $data->username,
          "admin" => $data->admin
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $user->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
