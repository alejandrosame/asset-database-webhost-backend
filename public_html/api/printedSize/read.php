<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/printedSize.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();

$database = new Database();
$db = $database->getConnection();

$size = new PrintedSize($db);

$stmt = $size->read();
$num = $stmt->rowCount();

$arr=array();
$arr["printed_sizes"]=array();

// retrieve our table contents
// fetch() is faster than fetchAll()
// http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($row);

    $item=array(
        "id" => $id,
        "name" => $name,
        "created" => $created,
        "updated" => $updated
    );

    array_push($arr["printed_sizes"], $item);
}

http_response_code(200);
echo json_encode($arr);
