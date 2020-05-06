<?php
class Image
{

    // database connection and table name
    private $conn;
    private $table_name = "image";

    // object properties
    public $id;
    public $filename;
    public $hash;
    public $created;
    public $updated;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT id, filename, hash, created, updated
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
        $query = "SELECT id, filename, hash, created, updated
            FROM
                " . $this->table_name . "
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
            $this->filename = $row['filename'];
            $this->hash = $row['hash'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function hashExists()
    {
        $query = "SELECT id, filename, hash, created, updated
            FROM " . $this->table_name . "
            WHERE hash = ?
            LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->hash);
        $stmt->execute();
        $num = $stmt->rowCount();

        // if hash exists, assign values to object properties for easy access and use for php sessions
        if ($num>0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->filename = $row['filename'];
            $this->created = $row['created'];
            $this->updated = $row['updated'];

            return true;
        }
        return false;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                filename=:filename, hash=:hash";

        $stmt = $this->conn->prepare($query);
        $this->filename=htmlspecialchars(strip_tags($this->filename));

        $stmt->bindParam(":filename", $this->filename);
        $stmt->bindParam(":hash", $this->hash);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
