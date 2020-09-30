<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare asset object
$asset = new Asset($db);

// get product id
$data = json_decode(file_get_contents("php://input"));

// set asset id to be deleted
$asset->id = $data->id;

// delete the product
if ($asset->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Asset was deleted."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to delete asset."));
}
