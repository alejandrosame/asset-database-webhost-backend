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

    $full_frontURL = null;
    $thumb_frontURL = null;
    if ($front_image !== null) {
        $full_frontURL = "/api/image/serve.php?id=".$front_image;
        $thumb_frontURL = "/api/image/serve.php?id=".$front_image."&thumbnail";
    }

    $full_backURL = null;
    $thumb_backURL = null;
    if ($back_image !== null) {
        $full_backURL = "/api/image/serve.php?id=".$back_image;
        $thumb_backURL = "/api/image/serve.php?id=".$back_image."&thumbnail";
    }

    $item=array(
        "id" => $id,
        "order" => $order_,
        "display_size" => $display_size,
        "printed_size" => $printed_size,
        "full_frontURL" => $full_frontURL,
        "thumb_frontURL" => $thumb_frontURL,
        "full_backURL" => $full_backURL,
        "thumb_backURL" => $thumb_backURL,
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
