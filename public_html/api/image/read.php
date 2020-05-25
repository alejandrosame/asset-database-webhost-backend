<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/image.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$image = new Image($db);

$stmt = $image->read();
$num = $stmt->rowCount();

$images_arr=array();
$images_arr["images"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($row);

    $image_item=array(
        "id" => $id,
        "number" => $number,
        "name" => $name,
        "side" => $side,
        "fullURL" => "/api/image/serve.php?id=".$id,
        "thumbURL" => "/api/image/serve.php?id=".$id."&thumbnail",
        "created" => $created,
        "updated" => $updated
    );

    array_push($images_arr["images"], $image_item);
}

http_response_code(200);
echo json_encode($images_arr);
