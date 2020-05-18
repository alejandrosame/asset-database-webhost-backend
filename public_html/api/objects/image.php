<?php
include_once 'creature.php';

class Image
{
    // database connection and table name
    private $conn;
    private $table_name = "image";

    // object properties
    public $id;
    public $hash;
    public $number;
    public $side;
    public $created;
    public $updated;

    // Output properties
    public $name;
    public $filename;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $creature = new Creature($this->conn);
        $query = "SELECT i.id as id, i.number as number, c.name as name, i.side as side, i.created, i.updated
            FROM " . $this->table_name . " i
            LEFT JOIN ". $creature->table_name() ." c
            ON (i.number = c.id)
            ORDER BY
                created DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function readOne()
    {
        $creature = new Creature($this->conn);
        $query = "SELECT i.id as id, i.number as number, c.name as name, i.side as side, i.created, i.updated
            FROM " . $this->table_name . " i
            LEFT JOIN ". $creature->table_name ." c
            ON (i.number = c.id)
            WHERE i.id = :id
            LIMIT 0,1";

        $this->id=htmlspecialchars(strip_tags($this->id));

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->number = $row['number'];
            $this->name = $row['name'];
            $this->side = $row['side'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            $this->filename = $this->number."_".$this->name."_".$this->side;
            return true;
        }
        return false;
    }

    public function hashExists()
    {
        $query = "SELECT id, hash, created, updated
            FROM " . $this->table_name . "
            WHERE hash = :hash
            LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":hash", $this->hash);
        $stmt->execute();
        $num = $stmt->rowCount();

        if ($num>0) {
            return true;
        }
        return false;
    }

    public function create()
    {
        $creature = new Creature($this->conn);
        if (!$creature->exists($this->number)) {
            $creature->id = $this->number;
            $creature->name = $this->name;
            if (!$creature->create()) {
                return false;
            }
        }

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                number=:number, side=:side, hash=:hash";

        $stmt = $this->conn->prepare($query);
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->side=htmlspecialchars(strip_tags($this->side));
        $this->hash=htmlspecialchars(strip_tags($this->hash));

        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":side", $this->side);
        $stmt->bindParam(":hash", $this->hash);

        if ($stmt->execute()) {
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
