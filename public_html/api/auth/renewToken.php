<?php
include_once __DIR__.'/../../logic/functions.php';

header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$user = isUser();
$token = createToken($user);
http_response_code(200);
echo json_encode(
    array(
        "token" => $token["token"],
        "id" => $user->id,
        "expiresIn" => ($token["tokenData"]["exp"] - $token["tokenData"]["iat"])
    )
);
