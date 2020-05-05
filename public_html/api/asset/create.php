<?php
include_once '../config/database.php';
include_once '../objects/asset.php';

header("Access-Control-Allow-Origin: http://localhost/api/asset/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);
$data = json_decode(file_get_contents("php://input"));

if (
  !empty($data->order) &&
  !empty($data->display_size) &&
  !empty($data->printed_size) &&
  !empty($data->front_image_id) &&
  !empty($data->back_image_id) &&
  !empty($data->number) &&
  !empty($data->name) &&
  !empty($data->notes)
) {
    $asset->order=$data->order;
    $asset->display_size=$data->display_size;
    $asset->printed_size=$data->printed_size;
    $asset->front_image_id=$data->front_image_id;
    $asset->back_image_id=$data->back_image_id;
    $asset->number=$data->number;
    $asset->name=$data->name;
    $asset->notes=$data->notes;

    if ($asset->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Asset was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create asset."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create asset. Data is incomplete."));
}
