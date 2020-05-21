<?php
include_once '../config/filesystem.php';
include_once '../config/database.php';
include_once '../objects/image.php';
include_once __DIR__.'/../../logic/functions.php';

require __DIR__ . '/../../../vendor/autoload.php';

define('MB', 1048576);

setHeaders();
$user = isUser();

// Check required fields
$REQUIRED_FIELDS = ["file", "number", "name", "side"];
$missing_fields = array_filter($REQUIRED_FIELDS, function ($key) {
    return !(isset($_FILES[$key]) or isset($_POST[$key]));
});

if (count($missing_fields) > 0) {
    http_response_code(400);
    echo json_encode(
        array(
          "message" => "Missing the following fields: ". implode(", ", $missing_fields)
        )
    );
    return;
}

// Set arguments
$uploadFile = $_FILES["file"];
$uploadNumber = $_POST["number"];
$uploadName = $_POST["name"];
$uploadSide = $_POST["side"];

$database = new Database();
$db = $database->getConnection();
$image = new Image($db);

$targetFile = basename($uploadFile["name"]);
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
$check = getimagesize($uploadFile["tmp_name"]);
if ($check == false) {
    http_response_code(400);
    echo json_encode(
        array(
          "message" => "File is not an image."
        )
    );
    return;
}

// Check file size
if ($uploadFile["size"] > 10*MB) {
    http_response_code(400);
    echo json_encode(
        array(
        "message" => "Image file size is too big."
      )
    );
    return;
}
// Allow certain file formats
if ($imageFileType != "png") {
    http_response_code(400);
    echo json_encode(
        array(
          "message" => "Only PNG images are allowed."
        )
    );
    return;
}

$fileName = $uploadFile['name'];
$stream = fopen($uploadFile['tmp_name'], 'r+');
$imageContents = stream_get_contents($stream);

if (is_resource($stream)) {
    fclose($stream);
}

$hash = sha1($imageContents);

// Check here if file already exists using hash
$image->hash = $hash;

if ($image->hashExists()) {
    http_response_code(200);
    echo json_encode(
        array(
        "message" => "Image already exists on database."
        )
    );
    return;
}

$thumbnail = Imagecow\Image::fromString($imageContents);
$thumbnail->resize(100, 100);

$fullrespath = DIRECTORY_SEPARATOR . "fullres" . DIRECTORY_SEPARATOR . $hash . ".png";
$thumbpath = DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR . $hash . ".png";

if (!$filesystem->has($fullrespath)) {
    $filesystem->write($fullrespath, $imageContents);
}
if (!$filesystem->has($thumbpath)) {
    $filesystem->write($thumbpath, $thumbnail->getString());
}

$image->number = $uploadNumber;
$image->name = $uploadName;
$image->side = $uploadSide;

if (!$image->create()) {
    $filesystem->delete($fullrespath);
    $filesystem->delete($thumbpath);

    error_log($image->error);

    http_response_code(503);
    echo json_encode(array("message" => "Unable to upload image."));
    return;
}

http_response_code(201);
echo json_encode(
    array(
      "message" => "Image uploaded to database successfully."
    )
);
