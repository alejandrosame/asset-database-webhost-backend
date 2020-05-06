<?php
include_once '../config/filesystem.php';
include_once '../config/database.php';
include_once '../objects/image.php';

header("Access-Control-Allow-Origin: *");
header("Content-type: image/png");

$database = new Database();
$db = $database->getConnection();
$image = new Image($db);

$data = json_decode(file_get_contents("php://input"));
$image->id=$data->id;

if (!$image->readOne()) {
    http_response_code(404);
    echo json_encode(
        array("message" => "Image not found.")
    );
    return;
}

header('Content-Disposition: filename='. $image->filename);

if ($data->isThumbnail) {
    $subpath="thumb";
} else {
    $subpath="fullres";
}
$path = DIRECTORY_SEPARATOR . $subpath . DIRECTORY_SEPARATOR . $image->hash . ".png";

$contents = $filesystem->read($path);
if (!$contents) {
    http_response_code(404);
    echo json_encode(
        array("message" => "Image not found on filesystem.")
    );
    return;
}

http_response_code(200);
echo $contents;
