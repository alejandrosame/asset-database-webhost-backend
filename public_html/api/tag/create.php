<?php
include_once '../config/database.php';
include_once '../objects/tag.php';

header("Access-Control-Allow-Origin: http://localhost/api/asset/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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
        echo json_encode(array("message" => "Tag was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create tag."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create tag. Data is incomplete."));
}
