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
    public $order;
    public $display_size;
    public $printed_size;
    public $front_image;
    public $back_image;
    public $number;
    public $name;
    public $notes;
    public $created;
    public $updated;
    public $products;
    public $tags;
    public $related_creatures;

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

    private function generic_read_query()
    {
        return "
          WITH tags_array AS (
            SELECT a.id AS asset_id, JSON_ARRAYAGG(t.name) AS tags
            FROM ". $this->table_name ." a
            JOIN asset_has_tag aht ON (a.id = aht.asset_id)
            JOIN ". $this->tag_table_name ." t ON (aht.tag_id = t.id)
            GROUP BY (a.id)
          ),
          products_array AS (
            SELECT a.id AS asset_id, JSON_ARRAYAGG(p.name) AS products
            FROM ". $this->table_name ." a
            JOIN asset_has_product ahp ON (a.id = ahp.product_id)
            JOIN ". $this->product_table_name ." p ON (ahp.product_id = p.id)
            GROUP BY (a.id)
          ),
          creature_references AS (
            SELECT  c.id as number,
                    JSON_OBJECT('number', c.id, 'name', c.name, 'ref', a.id) as ref
            FROM creature c
            LEFT JOIN (
              SELECT a_partitioned.*
              FROM (
                SELECT  *,
                        ROW_NUMBER() OVER (PARTITION BY number ORDER BY order_ ASC) rn
                FROM ". $this->table_name ."
              ) a_partitioned
              WHERE a_partitioned.rn = 1) a
            ON (c.id = a.number)
          ),
          related_creatures_array AS (
            SELECT rc.asset AS id, JSON_ARRAYAGG(c.ref) AS refs
            FROM related_creatures rc
            LEFT JOIN creature_references c ON (rc.creature = c.number)
            GROUP BY (rc.asset)
          )
          SELECT  a.*,
                  c.name AS name,
                  COALESCE(t.tags, '[]') AS tags,
                  COALESCE(p.products, '[]') AS products,
                  COALESCE(rc.refs, '[]') AS related_creatures
          FROM asset a
          LEFT JOIN creature AS c ON (a.number = c. id)
          LEFT JOIN tags_array AS t ON (a.id = t.asset_id)
          LEFT JOIN products_array AS p ON (a.id = p.asset_id)
          LEFT JOIN related_creatures_array AS rc ON (a.id = rc.id)
          ORDER BY a.created ASC
      ";
    }

    public function read()
    {
        $stmt = $this->conn->prepare($this->generic_read_query());
        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                order_=:order, display_size=:display_size, printed_size=:printed_size, number=:number, notes=:notes";

        $this->order=htmlspecialchars(strip_tags($this->order));
        $this->display_size=htmlspecialchars(strip_tags($this->display_size));
        $this->printed_size=htmlspecialchars(strip_tags($this->printed_size));
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->notes=htmlspecialchars(strip_tags($this->notes));

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":order", $this->order);
        $stmt->bindParam(":display_size", $this->display_size);
        $stmt->bindParam(":printed_size", $this->printed_size);
        $stmt->bindParam(":notes", $this->notes);

        try {
            if ($stmt->execute()) {
                $this->id=$this->conn->lastInsertId();
                return true;
            }
            error_log(json_encode($stmt->errorInfo()));
            return false;
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->error="Asset already exists.";
            } else {
                $this->error=$e->errorInfo[2];
                error_log(implode(":", $e->errorInfo));
            }

            return false;
        }
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
