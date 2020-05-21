<?php
include_once '../config/database.php';
include_once '../objects/product.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isUser();

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
        echo json_encode(array(
          "id" => $product->id,
          "name" => $product->name
        ));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create product."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
}
