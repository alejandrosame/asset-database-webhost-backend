<?php
include_once __DIR__.'/../../../config/database.php';
include_once __DIR__.'/../../../objects/user.php';
include_once __DIR__.'/../../../logic/functions.php';

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
    array_push($users_arr["users"], $user->toArray($row));
}

http_response_code(200);
echo json_encode($users_arr);
