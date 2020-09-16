<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/printedSize.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$size = new PrintedSize($db);

// get product id
$data = json_decode(file_get_contents("php://input"));

// set product id to be deleted
$size->id = $data->id;

// delete the product
if ($size->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Printed size entry was deleted."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to delete printed size entry."));
}
