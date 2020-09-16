<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/printedSize.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$size = new PrintedSize($db);
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
        echo json_encode(array("message" => "Unable to create printed size entry."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create printed size entry. Data is incomplete."));
}
