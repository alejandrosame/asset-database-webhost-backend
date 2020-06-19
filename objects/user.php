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

    public function create($username, $password, $admin)
    {
        include __DIR__.'/../config/core.php';

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                username=:username, password=:password, isadmin=:isadmin";

        $stmt = $this->conn->prepare($query);

        $this->username=htmlspecialchars(strip_tags($username));
        $this->password=htmlspecialchars(strip_tags($password));
        $admin=htmlspecialchars(strip_tags($admin));
        $this->isadmin = 0;
        if ($admin == "true") {
            $this->isadmin = 1;
        }

        $this->password=hash_hmac("sha512", $this->password, $pepper);
        $this->password=password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":isadmin", $this->isadmin);

        try {
            if ($stmt->execute()) {
                $this->id=$this->conn->lastInsertId();
                return true;
            }

            error_log(implode(":", $stmt->errorInfo()));
            return false;
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->error="User already exists.";
            } else {
                $this->error=$e->errorInfo[2];
                error_log(implode(":", $e->errorInfo));
            }

            return false;
        }
    }

    public function updateAdminStatus()
    {
        include __DIR__.'/../config/core.php';

        $query = "UPDATE
                " . $this->table_name . "
            SET isadmin = NOT isadmin
            WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->isadmin = !$this->isadmin;

        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }

        error_log(implode(":", $stmt->errorInfo()));
        return false;
    }

    public function read()
    {
        $query = "SELECT id, username, isadmin, created, updated
            FROM
                " . $this->table_name . "
            ORDER BY
                created ASC";

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
        $query = "SELECT id, username, password, isadmin, created, updated
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
            $this->password = $row['password'];
            $this->isadmin = $row['isadmin'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function verifyUser($username, $password)
    {
        include __DIR__.'/../config/core.php';

        $this->username=$username;
        $user_exists = $this->exists();

        $passwordPeppered = hash_hmac("sha512", $password, $pepper);
        $correctPassword = password_verify($passwordPeppered, $this->password);

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

    public function toArray($row=null)
    {
        if (isset($row)) {
            $this->id = $row["id"];
            $this->username = $row["username"];
            $this->isadmin = $row["isadmin"];
            $this->created = $row["created"];
            $this->updated = $row["updated"];
        }

        return array(
          "id" => $this->id,
          "username" => $this->username,
          "isAdmin" => (boolean)json_decode(strtolower($this->isadmin)),
          "created" => $this->created,
          "updated" => $this->updated
      );
    }
}
