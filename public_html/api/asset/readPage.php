<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$pageInfo = getPagingInfo();
$showProducts = getParameter('showProducts');
$hideProducts = getParameter('hideProducts');
$showTags = getParameter('showTags');
$hideTags = getParameter('hideTags');
$searchTerm = getParameter('searchTerm');

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$stmt = $asset->readPage(
    $pageInfo["from"],
    $pageInfo["pageSize"],
    $showProducts,
    $hideProducts,
    $showTags,
    $hideTags,
    $searchTerm
);
$num = $stmt->rowCount();

$arr=array();
$arr["assets"]=array();
$arr["page"]=$pageInfo["page"];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($arr["assets"], $asset->toArray($row));
}

http_response_code(200);
echo json_encode($arr);
