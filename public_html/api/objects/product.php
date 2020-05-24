<?php
class Product
{
    // database connection and table name
    private $conn;
    private $table_name = "product";

    // object properties
    public $id;
    public $name;
    public $created;
    public $updated;

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

        if ($stmt->execute()) {
            $this->id=$this->conn->lastInsertId();
            return true;
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
