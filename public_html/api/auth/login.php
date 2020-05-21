<?php
include_once __DIR__.'/../config/database.php';
include_once __DIR__.'/../objects/user.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

# TODO: Change for password verification
$user = new User($db);
if (!$user->verifyUser($data->email, $data->password)) {
    http_response_code(401);
    echo json_encode(array("error" => array("message" => "Wrong credentials.")));
    exit();
}

// Successful login
$token = createToken($user);
http_response_code(200);
echo json_encode(
    array(
        "token" => $token["token"],
        "id" => $user->id,
        "expiresIn" => ($token["tokenData"]["exp"] - $token["tokenData"]["iat"])
    )
);
