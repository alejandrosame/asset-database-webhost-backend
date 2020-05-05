<?php
class Asset
{
    // database connection and table name
    private $conn;
    private $table_name = "asset";

    // object properties
    public $id;
    public $order;
    public $display_size;
    public $printed_size;
    public $front_image_id;
    public $back_image_id;
    public $number;
    public $name;
    public $notes;
    public $created;
    public $updated;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT id, order, display_size, printed_size, front_image_id, back_image_id, number, name, notes, created, updated
            FROM
                " . $this->table_name . "
            ORDER BY
                created DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                order=:order, display_size=:display_size, printed_size=:printed_size, front_image_id=:front_image_id, back_image_id=:back_image_id, number=:number, name=:name, notes=:notes";

        $stmt = $this->conn->prepare($query);

        $this->order=htmlspecialchars(strip_tags($this->order));
        $this->display_size=htmlspecialchars(strip_tags($this->display_size));
        $this->printed_size=htmlspecialchars(strip_tags($this->printed_size));
        $this->front_image_id=htmlspecialchars(strip_tags($this->front_image_id));
        $this->back_image_id=htmlspecialchars(strip_tags($this->back_image_id));
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->notes=htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(":order", $this->order);
        $stmt->bindParam(":display_size", $this->display_size);
        $stmt->bindParam(":printed_size", $this->printed_size);
        $stmt->bindParam(":front_image_id", $this->front_image_id);
        $stmt->bindParam(":back_image_id", $this->back_image_id);
        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
