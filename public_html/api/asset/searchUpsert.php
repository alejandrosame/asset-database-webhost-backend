<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$asset->number = getCheckInt('number', true);
$asset->order = getCheckInt('order', true);

$stmt = $asset->searchUpsert();

$arr=array();
$arr["assets"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($arr["assets"], $asset->toArray($row));
}

http_response_code(200);
echo json_encode($arr);
