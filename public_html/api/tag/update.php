<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/tag.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$tag = new Tag($db);
$data = json_decode(file_get_contents("php://input"));

if (
  isset($data->id) &&
  isset($data->name)
) {
    $tag->id = $data->id;
    if ($tag->update($data->name)) {
        http_response_code(201);
        echo json_encode(array("message" => "Tag was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $tag->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update tag. Data is incomplete."));
}
