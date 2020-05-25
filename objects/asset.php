<?php
include_once dirname(__FILE__).'/tag.php';
include_once dirname(__FILE__).'/product.php';

class Asset
{
    // database connection and table name
    private $conn;
    private $table_name = "asset";
    private $tag_table_name;
    private $product_table_name;

    // object properties
    public $id;
    public $order_;
    public $display_size;
    public $printed_size;
    public $front_image_id;
    public $back_image_id;
    public $number;
    public $name;
    public $notes;
    public $created;
    public $updated;
    public $products;
    public $tags;

    // constructor with $db as database connection
    public function __construct($db)
    {
        $this->conn = $db;

        $tag = new Tag($db);
        $product = new Product($db);
        $this->tag_table_name=$tag->table_name();
        $this->product_table_name=$product->table_name();
        unset($tag);
        unset($product);
    }

    public function read()
    {
        $query="SELECT a.*, COALESCE(t_array.tags, '[]') AS tags, COALESCE(p_array.products, '[]') AS products
        FROM ". $this->table_name ." a
        LEFT JOIN
          (SELECT a.id AS asset_id, JSON_ARRAYAGG(t.name) AS tags
          FROM ". $this->table_name ." a
          JOIN asset_has_tag aht ON (a.id = aht.asset_id)
          JOIN ". $this->tag_table_name ." t ON (aht.tag_id = t.id)
          GROUP BY (a.id)) as t_array ON (a.id = t_array.asset_id)
        LEFT JOIN
          (SELECT a.id AS asset_id, JSON_ARRAYAGG(p.name) AS products
          FROM ". $this->table_name ." a
          JOIN asset_has_product ahp ON (a.id = ahp.product_id)
          JOIN ". $this->product_table_name ." p ON (ahp.product_id = p.id)
          GROUP BY (a.id)) as p_array ON (a.id = p_array.asset_id)
        ORDER BY created ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                order_=:order, display_size=:display_size, printed_size=:printed_size, front_image_id=:front_image_id, back_image_id=:back_image_id, number=:number, name=:name, notes=:notes";

        $stmt = $this->conn->prepare($query);

        $this->order_=htmlspecialchars(strip_tags($this->order_));
        $this->display_size=htmlspecialchars(strip_tags($this->display_size));
        $this->printed_size=htmlspecialchars(strip_tags($this->printed_size));
        $this->front_image_id=htmlspecialchars(strip_tags($this->front_image_id));
        $this->back_image_id=htmlspecialchars(strip_tags($this->back_image_id));
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->notes=htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(":order", $this->order_);
        $stmt->bindParam(":display_size", $this->display_size);
        $stmt->bindParam(":printed_size", $this->printed_size);
        $stmt->bindParam(":front_image_id", $this->front_image_id);
        $stmt->bindParam(":back_image_id", $this->back_image_id);
        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":notes", $this->notes);

        if ($stmt->execute()) {
            $this->id=$this->conn->lastInsertId();
            return true;
        }

        error_log(json_encode($stmt->errorInfo()));

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
