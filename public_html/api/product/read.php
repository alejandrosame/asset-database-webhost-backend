<?php
include_once '../config/database.php';
include_once '../objects/product.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$stmt = $product->read();
$num = $stmt->rowCount();

if ($num>0) {
    $arr=array();
    $arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $user_item=array(
            "id" => $id,
            "name" => $name,
            "created" => $created,
            "updated" => $updated
        );

        array_push($arr["records"], $item);
    }

    http_response_code(200);
    echo json_encode($arr);
} else {
    http_response_code(404);
    echo json_encode(
        array("message" => "No products found.")
    );
}
