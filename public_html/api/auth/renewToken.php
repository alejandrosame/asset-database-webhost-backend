<?php
include_once __DIR__.'/../../../logic/functions.php';

setHeaders();
$user = isUser();
$token = createToken($user);
http_response_code(200);
echo json_encode(
    array_merge(
        $user->toArray(),
        array(
          "token" => $token["token"],
          "expiresIn" => ($token["tokenData"]["exp"] - $token["tokenData"]["iat"])
        )
    )
);
