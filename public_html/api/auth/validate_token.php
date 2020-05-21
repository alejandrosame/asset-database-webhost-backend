<?php
require __DIR__.'/../../../vendor/autoload.php';
include_once __DIR__.'/../../logic/functions.php';

header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$data = isUser();

http_response_code(200);
echo json_encode(array(
  "message" => "Access granted.",
  "data" => $data
));
