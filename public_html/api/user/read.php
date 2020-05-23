<?php
include_once '../config/database.php';
include_once '../objects/user.php';
include_once __DIR__.'/../../logic/functions.php';

setHeaders();
$user = isAdmin();

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$stmt = $user->read();
$num = $stmt->rowCount();

$users_arr=array();
$users_arr["users"]=array();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    extract($row);

    $user_item=array(
        "id" => $id,
        "username" => $username,
        "admin" => (boolean)json_decode(strtolower($isadmin)),
        "created" => $created,
        "updated" => $updated
    );

    array_push($users_arr["users"], $user_item);
}

http_response_code(200);
echo json_encode($users_arr);
