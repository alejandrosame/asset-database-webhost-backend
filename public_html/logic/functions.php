<?php
/**
 * Get header Authorization
 * */
function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}
/**
 * Get access token from header
 * */
function getBearerToken()
{
    $headers = getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}
/**
 * Create token
 * */
function createToken($user)
{
    include __DIR__ .'/../api/config/core.php';
    require __DIR__.'/../../vendor/autoload.php';

    $token = array(
       "iss" => $iss,
       "aud" => $aud,
       "iat" => $iat,
       "nbf" => $nbf,
       "exp" => $exp,
       "data" => array(
           "id" => $user->id,
           "username" => $user->username,
           "isadmin" => $user->isadmin
       )
    );

    return array(
      "tokenData" => $token,
      "token" => \Firebase\JWT\JWT::encode($token, $key)
    );
}
/**
 * Validate token
 * */
function validateToken()
{
    include __DIR__ .'/../api/config/core.php';
    require __DIR__.'/../../vendor/autoload.php';

    $DEFAULT = null;

    $token=getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(array("message" => "Missing auth token."));
        exit();
    }

    try {
        $decoded = \Firebase\JWT\JWT::decode($token, $key, array('HS256'));
    } catch (Exception $e) {
        error_log("Invalid token: ". $e->getMessage());
        http_response_code(401);
        echo json_encode(array("message" => "Invalid token."));
        exit();
    }
    return $decoded->data;
}
/**
 * Validate user
 * */
function isUser($checkIsAdmin = false)
{
    $data=validateToken();

    include_once __DIR__ .'/../api/config/database.php';
    include_once __DIR__ .'/../api/objects/user.php';

    $DEFAULT = null;

    $database = new Database();
    $db = $database->getConnection();

    // Check that user exists
    $user = new User($db);
    $user->id = $data->id;
    if (!$user->readOne()) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied."));
        exit();
    }
    if ($checkIsAdmin and !$user->isadmin) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied."));
        exit();
    }
    return $user;
}
/**
 * Validate admin
 * */
function isAdmin()
{
    return isUser(true);
}
/**
 * Set headers
 * */
function setHeadersWithoutContentType()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
        exit();
    }
}
function setHeaders()
{
    header("Content-Type: application/json; charset=UTF-8");
    setHeadersWithoutContentType();
}
