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
  isset($data->name) &&
  isset($data->number) &&
  isset($data->order) &&
  isset($data->product) &&
  isset($data->displaySize) &&
  isset($data->printSize)
) {
    $asset->number=$data->number;
    $asset->name=$data->name;
    $asset->order=$data->order;
    $asset->display_size=$data->displaySize;
    $asset->printed_size=$data->printSize;
    if (isset($data->front_image)) {
        $asset->front_image=$data->front_image;
    }
    if (isset($data->back_image)) {
        $asset->back_image=$data->back_image;
    }
    if (isset($data->notes)) {
        $asset->notes=$data->notes;
    }

    if ($asset->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Asset was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $asset->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create asset. Data is incomplete."));
}
