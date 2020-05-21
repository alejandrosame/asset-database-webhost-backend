<?php
include_once '../config/database.php';
include_once '../objects/tag.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isUser();

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$tag = new Tag($db);

// get product id
$data = json_decode(file_get_contents("php://input"));

// set product id to be deleted
$tag->id = $data->id;

// delete the product
if ($tag->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Product was deleted."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to delete product."));
}
