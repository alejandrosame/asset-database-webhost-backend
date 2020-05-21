<?php
include_once '../config/database.php';
include_once '../objects/tag.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();

$database = new Database();
$db = $database->getConnection();

$tag = new Tag($db);

$stmt = $tag->read();
$num = $stmt->rowCount();

$arr=array();
$arr["tags"]=array();

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

    array_push($arr["tags"], $item);
}

http_response_code(200);
echo json_encode($arr);
