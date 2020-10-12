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
  isset($data->id) &&
  isset($data->name)
) {
    $size->id = $data->id;
    if ($size->update($data->name)) {
        http_response_code(201);
        echo json_encode(array("message" => "PrintedSize was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $size->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update printedSize. Data is incomplete."));
}
