<?php
class Creature
{
    // database connection and table name
    private $conn;
    private $table_name = "creature";

    // object properties
    public $id;
    public $name;
    public $created;
    public $updated;

    // output properties
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

    public function exists($id)
    {
        $query = "SELECT id, created, updated
          FROM " . $this->table_name . "
          WHERE id = :id
          LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num>0) {
            return true;
        }
        return false;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                id=:id,
                name=:name";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->name=htmlspecialchars(strip_tags($this->name));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":name", $this->name);

        if ($stmt->execute()) {
            $this->name = $this->conn->insert_id;
            return true;
        }

        $this->error = implode(":", $stmt->errorInfo());
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
