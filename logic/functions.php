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
    include __DIR__ .'/../config/core.php';
    require __DIR__.'/../vendor/autoload.php';

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
    include __DIR__ .'/../config/core.php';
    require __DIR__.'/../vendor/autoload.php';

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

    include_once __DIR__ .'/../config/database.php';
    include_once __DIR__ .'/../objects/user.php';

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
/*
 * Get check int
 */
function getCheckStr($field)
{
    $value = $_GET[$field];

    if ($value == null and $value != "0") {
        http_response_code(401);
        echo json_encode(array("message" => "Missing field '".$field."' to retrieve."));
        exit();
    }

    return $value;
}
/*
 * Get check int
 */
function getCheckInt($field, $zeroIsValid = false)
{
    $value = $_GET[$field];
    if ($value == "0") {
        $value = 0;
    } else {
        $value = filter_input(INPUT_GET, $field, FILTER_VALIDATE_INT);
        if ($value == null) {
            http_response_code(401);
            echo json_encode(array("message" => "Missing field '".$field."' to retrieve."));
            exit();
        }
        if ($value == false) {
            http_response_code(401);
            echo json_encode(array("message" => "Field '".$field."' must be int."));
            exit();
        }
    }

    if (!$zeroIsValid && $value == 0) {
        http_response_code(401);
        echo json_encode(array("message" => "Field '".$field."' must be bigger than 0."));
        exit();
    }
    if ($value < 0) {
        http_response_code(401);
        echo json_encode(array("message" => "Field '".$field."' must be positive."));
        exit();
    }

    return $value;
}
/*
 * Get paging info
 */
function getPagingInfo()
{
    $page = getCheckInt('page');
    $pageSize = getCheckInt('pageSize');

    return array(
      "page" => $page,
      "pageSize" => $pageSize,
      "from" => ($pageSize * $page) - $pageSize
    );
}
/*
 * Get parameter value
 */
function getParameter($param)
{
    if (isset($_GET[$param])) {
        return $_GET[$param];
    }
    return null;
}
/*
  Format related_creatures for CSV export
 */
function formatCSVrelated($JSON)
{
    $related = json_decode($JSON);
    $arr = array();
    foreach ($related as $ref) {
        array_push($arr, $ref->number);
    }
    return implode(',', $arr);
}
/*
  Format product for CSV export
 */
function formatCSVarray($arrayJSON)
{
    $arr = json_decode($arrayJSON);
    return implode(',', $arr);
}
/*
  Format row for CSV export
 */
function formatCSVrow($row)
{
    //"number", "name", "order", "printSize", "displaySize", "product", "tags", "notes", "related"));
    $newRow = array(
      $row["number"],
      $row["name"],
      $row["order_"],
      $row["printed_size"],
      $row["display_size"],
      formatCSVarray($row["products"]),
      formatCSVarray($row["tags"]),
      $row["notes"],
      formatCSVrelated($row["related_creatures"])
    );
    return $newRow;
}
/*
  Format as array
 */
function asArray($string)
{
    if (empty($string)) {
        return array();
    }
    return explode(',', $string);
}
