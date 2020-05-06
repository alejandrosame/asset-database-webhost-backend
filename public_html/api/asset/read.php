<?php
include_once '../config/database.php';
include_once '../objects/asset.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$stmt = $asset->read();
$num = $stmt->rowCount();

if ($num>0) {
    $arr=array();
    $arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $item=array(
            "id" => $id,
            "order" => $order_,
            "display_size" => $display_size,
            "printed_size" => $printed_size,
            "front_image_id" => $front_image_id,
            "back_image_id" => $back_image_id,
            "number" => $number,
            "name" => $name,
            "notes" => $notes,
            "created" => $created,
            "updated" => $updated,
            "products" => json_decode($products),
            "tags" => json_decode($tags),
        );

        array_push($arr["records"], $item);
    }

    http_response_code(200);
    echo json_encode($arr);
} else {
    http_response_code(404);
    echo json_encode(
        array("message" => "No assets found.")
    );
}
