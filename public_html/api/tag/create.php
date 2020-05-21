<?php
include_once '../config/database.php';
include_once '../objects/tag.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$tag = new Tag($db);
$data = json_decode(file_get_contents("php://input"));

if (
  !empty($data->name)
) {
    $tag->name=$data->name;

    if ($tag->create()) {
        http_response_code(201);
        echo json_encode(array(
          "id" => $tag->id,
          "name" => $tag->name
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create tag."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create tag. Data is incomplete."));
}
