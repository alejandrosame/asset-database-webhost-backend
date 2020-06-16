<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);
$data = json_decode(file_get_contents("php://input"));

if (
  isset($data->order) &&
  isset($data->number)
) {
    if ($asset->update($data)) {
        http_response_code(201);
        echo json_encode(array("message" => "Asset was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $asset->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update asset. Data is incomplete."));
}
