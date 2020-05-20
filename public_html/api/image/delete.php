<?php
include_once '../config/filesystem.php';
include_once '../config/database.php';
include_once '../objects/image.php';

// required headers
// Allow from any origin
if (isset($_SERVER["HTTP_ORIGIN"])) {
    //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Origin: *");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");

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
