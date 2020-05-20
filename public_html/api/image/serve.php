<?php
include_once '../config/filesystem.php';
include_once '../config/database.php';
include_once '../objects/image.php';

header("Access-Control-Allow-Origin: *");

// Check required fields
$REQUIRED_FIELDS = ["id"];
$missing_fields = array_filter($REQUIRED_FIELDS, function ($key) {
    return !(isset($_GET[$key]));
});

if (count($missing_fields) > 0) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(400);
    echo json_encode(
        array(
          "message" => "Missing the following parameters: ". implode(", ", $missing_fields)
        )
    );
    return;
}

$queryId = $_GET["id"];
$queryIsThumbnail = (isset($_GET["thumbnail"]) or $_GET["thumbnail"] === true);

$database = new Database();
$db = $database->getConnection();
$image = new Image($db);

$image->id=$queryId;

if (!$image->readOne()) {
    http_response_code(404);
    echo json_encode(
        array("message" => "Image not found.")
    );
    return;
}

if ($queryIsThumbnail) {
    $subpath="thumb";
    $image->filename = "thumb_".$image->filename;
} else {
    $subpath="fullres";
}

header("Content-type: image/png");
header('Content-Disposition: filename='. $image->filename);

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
