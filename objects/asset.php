<?php
include_once dirname(__FILE__).'/tag.php';
include_once dirname(__FILE__).'/product.php';
include_once dirname(__FILE__).'/creature.php';

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

    private function generic_read_query($where = "")
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
          default_images AS (
            SELECT i_partitioned.*
            FROM (
              SELECT  *,
                      ROW_NUMBER() OVER (PARTITION BY number,side ORDER BY created ASC) rn
              FROM image
            ) i_partitioned
            WHERE i_partitioned.rn = 1
          ),
          default_front_images AS (
            SELECT * FROM default_images WHERE side='front'
          ),
          default_back_images AS (
            SELECT * FROM default_images WHERE side='back'
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
          SELECT  a.id AS id,
                  a.order_ AS order_,
                  a.display_size AS display_size,
                  a.printed_size AS printed_size,
                  a.notes AS notes,
                  a.created AS created,
                  a.updated AS updated,
                  c.id AS number,
                  c.name AS name,
                  COALESCE(a.front_image, dfi.id) AS front_image,
                  COALESCE(a.back_image, dbi.id) AS back_image,
                  COALESCE(t.tags, '[]') AS tags,
                  COALESCE(p.products, '[]') AS products,
                  COALESCE(rc.refs, '[]') AS related_creatures
          FROM asset a
          LEFT JOIN creature AS c ON (a.number = c. id)
          LEFT JOIN default_front_images AS dfi ON (a.number = dfi.number)
          LEFT JOIN default_back_images AS dbi ON (a.number = dbi.number)
          LEFT JOIN tags_array AS t ON (a.id = t.asset_id)
          LEFT JOIN products_array AS p ON (a.id = p.asset_id)
          LEFT JOIN related_creatures_array AS rc ON (a.id = rc.id) ".
          $where ."
          ORDER BY a.number, a.order_ ASC
      ";
    }

    public function read()
    {
        $stmt = $this->conn->prepare($this->generic_read_query());
        $stmt->execute();

        return $stmt;
    }

    public function readPage($from, $page_size, $searchTerm)
    {
        $searchTerm = htmlspecialchars(strip_tags($searchTerm));
        $where = "";
        if (!empty($searchTerm)) {
            $where = "HAVING LOWER(CONCAT(name, ',', number, ',', tags, products)) LIKE CONCAT('%', ?, '%')";
        }

        $query = $this->generic_read_query($where) . "
         LIMIT ?, ?";

        $stmt = $this->conn->prepare($query);

        $startIndex=0;
        if (!empty($searchTerm)) {
            $startIndex=1;
            $stmt->bindParam(1, $searchTerm);
        }
        $stmt->bindParam($startIndex+1, $from, PDO::PARAM_INT);
        $stmt->bindParam($startIndex+2, $page_size, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }

    public function searchUpsert()
    {
        $where = "WHERE number=:number AND order_=:order";
        $query = $this->generic_read_query($where);

        $stmt = $this->conn->prepare($query);

        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->order=htmlspecialchars(strip_tags($this->order));

        $stmt->bindParam(":number", $this->number, PDO::PARAM_INT);
        $stmt->bindParam(":order", $this->order, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $this->number=htmlspecialchars(strip_tags($this->number));
        $this->name=htmlspecialchars(strip_tags($this->name));

        $creature = new Creature($this->conn);
        if (!$creature->exists($this->number)) {
            $creature->id = $this->number;
            $creature->name = $this->name;
            if (!$creature->create()) {
                $this->error=$creature->error;
                return false;
            }
        }

        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                order_=:order, display_size=:display_size, printed_size=:printed_size, number=:number, notes=:notes";

        $this->order=htmlspecialchars(strip_tags($this->order));
        $this->display_size=htmlspecialchars(strip_tags($this->display_size));
        $this->printed_size=htmlspecialchars(strip_tags($this->printed_size));
        $this->notes=htmlspecialchars(strip_tags($this->notes));

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":order", $this->order);
        $stmt->bindParam(":display_size", $this->display_size);
        $stmt->bindParam(":printed_size", $this->printed_size);
        $stmt->bindParam(":number", $this->number);
        $stmt->bindParam(":notes", $this->notes);

        $this->product=htmlspecialchars(strip_tags($this->product));

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

    public function toArray($row)
    {
        extract($row);

        $full_frontURL = null;
        $thumb_frontURL = null;
        if ($front_image !== null) {
            $full_frontURL = "/api/image/serve.php?id=".$front_image;
            $thumb_frontURL = "/api/image/serve.php?id=".$front_image."&thumbnail";
        }

        $full_backURL = null;
        $thumb_backURL = null;
        if ($back_image !== null) {
            $full_backURL = "/api/image/serve.php?id=".$back_image;
            $thumb_backURL = "/api/image/serve.php?id=".$back_image."&thumbnail";
        }

        return array(
          "id" => $id,
          "order" => $order_,
          "display_size" => $display_size,
          "printed_size" => $printed_size,
          "full_frontURL" => $full_frontURL,
          "thumb_frontURL" => $thumb_frontURL,
          "full_backURL" => $full_backURL,
          "thumb_backURL" => $thumb_backURL,
          "number" => $number,
          "name" => $name,
          "notes" => $notes,
          "created" => $created,
          "updated" => $updated,
          "products" => json_decode($products),
          "tags" => json_decode($tags),
          "related_assets" => json_decode($related_creatures),
      );
    }
}
