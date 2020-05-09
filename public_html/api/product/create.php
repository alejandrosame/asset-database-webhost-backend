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
