<?php
include_once __DIR__.'/../config/database.php';
include_once __DIR__.'/../objects/user.php';
include_once __DIR__.'/../../logic/functions.php';

// required headers
// Allow from any origin
if (isset($_SERVER["HTTP_ORIGIN"])) {
    //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header("Access-Control-Allow-Origin: *");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    exit(0);
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Max-Age: 3600");

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
