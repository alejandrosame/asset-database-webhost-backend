<?php
include_once '../config/database.php';
include_once '../objects/product.php';

// required headers
// Allow from any origin
if (isset($_SERVER["HTTP_ORIGIN"])) {
    //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Origin: *");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$product = new Product($db);

// get product id
$data = json_decode(file_get_contents("php://input"));

// set product id to be deleted
$product->id = $data->id;

// delete the product
if ($product->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Product was deleted."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to delete product."));
}
