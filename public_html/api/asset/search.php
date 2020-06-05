<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$searchTerm = getCheckStr('searchTerm');

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$stmt = $asset->search($searchTerm);

$arr=array();
$arr["assets"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($arr["assets"], $asset->toArray($row));
}

http_response_code(200);
echo json_encode($arr);
