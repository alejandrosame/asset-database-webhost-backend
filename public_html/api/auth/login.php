<?php
include_once '../config/core.php';
include_once '../config/database.php';
include_once '../objects/user.php';

require __DIR__ . '/../../../vendor/autoload.php';
use \Firebase\JWT\JWT;

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

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

$user = new User($db);
$user->email = $data->email;

$email_exists = $user->emailExists();

# TODO: Change for password_verify
$result = $data->password == $user->password;
if ($email_exists && $result) {
    $token = array(
       "iss" => $iss,
       "aud" => $aud,
       "iat" => $iat,
       "nbf" => $nbf,
       "exp" => $exp,
       "data" => array(
           "id" => $user->id,
           "username" => $user->username,
           "email" => $user->email,
           "isadmin" => $user->isadmin
       )
    );

    $jwt = JWT::encode($token, $key);
    http_response_code(200);
    echo json_encode(
        array(
                "idToken" => $jwt,
                "localId" => $user->id,
                "expiresIn" => ($exp - $iat)
            )
        );
} else {
    http_response_code(401);
    echo json_encode(array("error" => array("message" => "Wrong credentials.")));
}
