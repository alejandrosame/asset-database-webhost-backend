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

$images_arr=array();
$images_arr["images"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($images_arr["images"], $image->toArray($row));
}

http_response_code(200);
echo json_encode($images_arr);
