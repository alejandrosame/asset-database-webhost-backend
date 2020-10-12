<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/product.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$data = json_decode(file_get_contents("php://input"));

if (
  isset($data->id) &&
  isset($data->name)
) {
    $product->id = $data->id;
    if ($product->update($data->name)) {
        http_response_code(201);
        echo json_encode(array("message" => "Product was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => $product->error));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update product. Data is incomplete."));
}
