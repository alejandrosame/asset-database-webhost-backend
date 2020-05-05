<?php
include_once '../config/database.php';
include_once '../objects/product.php';

header("Access-Control-Allow-Origin: http://localhost/api/asset/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$data = json_decode(file_get_contents("php://input"));

if (
  !empty($data->name)
) {
    $product->name=$data->name;

    if ($product->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Product was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create product."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
}
