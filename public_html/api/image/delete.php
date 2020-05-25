<?php
include_once __DIR__.'/../../../config/filesystem.php';
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/image.php';
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();

$user = isUser();
// get database connection
$database = new Database();
$db = $database->getConnection();

$image = new Image($db);
$data = json_decode(file_get_contents("php://input"));
$image->id = $data->id;

if (!$image->readOne()) {
    http_response_code(404);
    echo json_encode(
        array("message" => "Image not found.")
    );
    return;
}

if ($image->delete()) {
    $fullrespath = DIRECTORY_SEPARATOR . "fullres" . DIRECTORY_SEPARATOR . $image->hash . ".png";
    $thumbpath = DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . $image->hash . ".png";

    if ($filesystem->has($fullrespath)) {
        $filesystem->delete($fullrespath);
    }
    if ($filesystem->has($thumbpath)) {
        $filesystem->delete($thumbpath);
    }

    http_response_code(200);
    echo json_encode(array("message" => "Image was deleted."));
} else {
    http_response_code(503);
    echo json_encode(array("message" => "Unable to delete image."));
}
