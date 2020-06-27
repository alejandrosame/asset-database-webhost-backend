<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/asset.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeadersWithoutContentType();
$user = isUser();
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=assets.csv');

$output = fopen('php://output', 'w');

# Header line
fputcsv($output, array("number", "name", "order", "printSize", "displaySize", "product",
"tags", "notes", "related"));

$database = new Database();
$db = $database->getConnection();

$asset = new Asset($db);

$stmt = $asset->read();

$arr=array();
$arr["assets"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row = formatCSVrow($row);
    fputcsv($output, $row);
}

http_response_code(200);
