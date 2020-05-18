<?php
include_once '../config/filesystem.php';
include_once '../config/database.php';
include_once '../objects/image.php';

require __DIR__ . '/../../../vendor/autoload.php';

define('MB', 1048576);

header("Access-Control-Allow-Origin: http://localhost/api/asset/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();
$image = new Image($db);

$uploadName = "fileA";
$target_file = basename($_FILES[$uploadName]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
$check = getimagesize($_FILES[$uploadName]["tmp_name"]);
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
if ($_FILES[$uploadName]["size"] > 10*MB) {
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

$fileName = $_FILES[$uploadName]['name'];
$stream = fopen($_FILES[$uploadName]['tmp_name'], 'r+');
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

if (!$image->create()) {
    $filesystem->delete($fullrespath);
    $filesystem->delete($thumbpath);

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
