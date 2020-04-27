<?php
class User
{

    // database connection and table name
    private $conn;
    private $table_name = "user";

    // object properties
    public $id;
    public $username;
    public $email;
    public $isadmin;
    public $created;
    public $updated;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT id, username, email, isadmin, created, updated
            FROM
                " . $this->table_name . "
            ORDER BY
                created DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute();

        return $stmt;
    }
}
