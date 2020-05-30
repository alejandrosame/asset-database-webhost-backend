<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/image.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();
$pageInfo = getPagingInfo();

$database = new Database();
$db = $database->getConnection();

$image = new Image($db);

$stmt = $asset->readPage($pageInfo["from"], $pageInfo["page_size"]);

$arr=array();
$arr["images"]=array();
$arr["page"]=$pageInfo["page"];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($arr["images"], $image->toArray($row));
}

http_response_code(200);
echo json_encode($arr);
