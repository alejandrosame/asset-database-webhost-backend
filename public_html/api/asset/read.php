<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$stmt = $asset->read();
$num = $stmt->rowCount();

$arr=array();
$arr["assets"]=array();

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
        "related_assets" => json_decode($related_creatures),
    );

    array_push($arr["assets"], $item);
}

http_response_code(200);
echo json_encode($arr);
