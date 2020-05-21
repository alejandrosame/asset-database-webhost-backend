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
    public $password;

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

    public function readOne()
    {
        $query = "SELECT id, username, email, isadmin, created, updated
            FROM " . $this->table_name . "
            WHERE id = :id
            LIMIT 0,1";

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $num = $stmt->rowCount();

        error_log("-->" . $this->id ." ". $num);

        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->isadmin = $row['isadmin'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function emailExists()
    {
        $query = "SELECT id, username, email, password, isadmin, created, updated
            FROM " . $this->table_name . "
            WHERE email = ?
            LIMIT 0,1";

        # Sanitize input
        $this->email=htmlspecialchars(strip_tags($this->email));

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        $num = $stmt->rowCount();

        // if email exists, assign values to object properties for easy access and use for php sessions
        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->password = $row['password'];
            $this->isadmin = $row['isadmin'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function verifyUser($email, $password)
    {
        $this->email=$email;
        $email_exists = $this->emailExists();
        $correctPassword = $password == $this->password;

        return $email_exists and $correctPassword;
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
