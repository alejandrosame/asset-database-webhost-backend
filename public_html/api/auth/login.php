<?php
include_once '../config/database.php';
include_once '../objects/user.php';

require __DIR__ . '/../../../vendor/autoload.php';
use \Firebase\JWT\JWT;

// required headers
header("Access-Control-Allow-Origin: http://localhost/api/auth/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

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

    http_response_code(200);

    $jwt = JWT::encode($token, $key);
    echo json_encode(
        array(
                "message" => "Successful login.",
                "jwt" => $jwt
            )
        );
} else {

    // set response code
    http_response_code(401);

    // tell the user login failed
    echo json_encode(array("message" => "Login failed."));
}
