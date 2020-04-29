<?php
header("Access-Control-Allow-Origin: http://localhost/api/asset/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/filesystem.php';
define('MB', 1048576);

$uploadName = "fileA";
$target_file = basename($_FILES[$uploadName]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
// Check if image file is a actual image or fake image
$check = getimagesize($_FILES[$uploadName]["tmp_name"]);
if ($check == false) {
    $uploadOk = 0;
    http_response_code(400);

    echo json_encode(
        array(
          "message" => "File is not an image."
        )
    );
}
// Check if file already exists
if ($uploadOk == 1 && $filesystem->has($target_file)) {
    $uploadOk = 0;
    http_response_code(200);

    echo json_encode(
        array(
          "message" => "File already exists."
        )
    );
}

// Check file size
if ($uploadOk == 1 && $_FILES[$uploadName]["size"] > 10*MB) {
    $uploadOk = 0;
    http_response_code(400);

    echo json_encode(
        array(
        "message" => "File is too big."
      )
    );
}
// Allow certain file formats
if ($uploadOk == 1 && $imageFileType != "png") {
    $uploadOk = 0;
    http_response_code(400);

    echo json_encode(
        array(
          "message" => "Only PNG files are allowed."
        )
    );
}

if ($uploadOk == 1) {
    try {
        $stream = fopen($_FILES[$uploadName]['tmp_name'], 'r+');
        $filesystem->writeStream(
            $_FILES[$uploadName]['name'],
            $stream
        );
        if (is_resource($stream)) {
            fclose($stream);
        }

        http_response_code(200);

        echo json_encode(
            array(
              "message" => "File uploaded."
            )
        );
    } catch (Exception $exception) {
        echo "Connection error: " .
        http_response_code(500);

        echo json_encode(
            array(
              "message" => $exception->getMessage()
            )
        );
    }
}
