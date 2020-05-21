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

if ($num>0) {
    $users_arr=array();
    $users_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $user_item=array(
            "id" => $id,
            "username" => $username,
            "email" => $email,
            "iasadmin" => $isadmin,
            "created" => $created,
            "updated" => $updated
        );

        array_push($users_arr["records"], $user_item);
    }

    http_response_code(200);
    echo json_encode($users_arr);
} else {
    http_response_code(404);
    echo json_encode(
        array("message" => "No users found.")
    );
}
