<?php
class Tag
{
    // database connection and table name
    private $conn;
    private $table_name = "tag";

    // object properties
    public $id;
    public $name;
    public $created;
    public $updated;
    public $error;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function table_name()
    {
        return $this->table_name;
    }

    public function read()
    {
        $query = "SELECT id, name, created, updated
            FROM
                " . $this->table_name . "
            ORDER BY
                created ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                name=:name";

        $stmt = $this->conn->prepare($query);

        $this->name=htmlspecialchars(strip_tags($this->name));

        $stmt->bindParam(":name", $this->name);

        try {
            if ($stmt->execute()) {
                $this->id=$this->conn->lastInsertId();
                return true;
            }
            error_log(implode(":", $stmt->errorInfo()));
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Tag exists
                $query = "SELECT id FROM " . $this->table_name . " WHERE name=:name";

                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":name", $this->name);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id = $row["id"];

                return true;
            } else {
                $this->error=$e->errorInfo[2];
                error_log(implode(":", $e->errorInfo));
            }
        }
        return false;
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
