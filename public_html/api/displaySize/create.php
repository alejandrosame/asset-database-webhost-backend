<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/displaySize.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$size = new DisplaySize($db);
$data = json_decode(file_get_contents("php://input"));

if (
  !empty($data->name)
) {
    $size->name=$data->name;

    if ($size->create()) {
        http_response_code(201);
        echo json_encode(array(
          "id" => $size->id,
          "name" => $size->name
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create display size entry. Error: ". $size->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create display size entry. Data is incomplete."));
}
