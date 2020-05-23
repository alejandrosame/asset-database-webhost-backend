<?php
class User
{

    // database connection and table name
    private $conn;
    private $table_name = "user";

    // object properties
    public $id;
    public $username;
    public $isadmin;
    public $created;
    public $updated;
    public $password;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT id, username, isadmin, created, updated
            FROM
                " . $this->table_name . "
            ORDER BY
                created DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT id, username, isadmin, created, updated
            FROM " . $this->table_name . "
            WHERE id = :id
            LIMIT 0,1";

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->isadmin = $row['isadmin'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function exists()
    {
        $query = "SELECT id, username, isadmin, created, updated
            FROM " . $this->table_name . "
            WHERE username = ?
            LIMIT 0,1";

        # Sanitize input
        $this->username=htmlspecialchars(strip_tags($this->username));

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->isadmin = $row['isadmin'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function verifyUser($username, $password)
    {
        $this->username=$username;
        $user_exists = $this->exists();
        $correctPassword = $password == $password;

        return $user_exists and $correctPassword;
    }

    public function delete()
    {
        $this->id=htmlspecialchars(strip_tags($this->id));

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
