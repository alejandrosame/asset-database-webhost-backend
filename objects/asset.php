<?php
include_once dirname(__FILE__).'/tag.php';
include_once dirname(__FILE__).'/product.php';
include_once dirname(__FILE__).'/creature.php';
include_once dirname(__FILE__).'/../logic/functions.php';

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

    public $error;

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

    private function generic_read_query(
        $prefilter = "",
        $where = ""
    ) {
        return "
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
        ". $prefilter ."
        LEFT JOIN tags_array AS t ON (a.id = t.asset_id)
        LEFT JOIN products_array AS p ON (a.id = p.asset_id)
        LEFT JOIN creature AS c ON (a.number = c. id)
        LEFT JOIN related_creatures_array AS rc ON (a.id = rc.id)
        LEFT JOIN (
          SELECT i_partitioned.*
          FROM (
            SELECT
                *,
                @row_number:=CASE
                    WHEN @number = number
                      THEN
                          @row_number + 1
                      ELSE
                           1
                    END AS rn,
                @number:=number,
                @side:=side
            FROM
                image,
                (SELECT @number:=0,@row_number:=0) as t
            WHERE side='front'
            ORDER BY
                created ASC
          ) i_partitioned
          WHERE i_partitioned.rn = 1
        ) AS dfi ON (a.number = dfi.number)
        LEFT JOIN (
          SELECT i_partitioned.*
          FROM (
            SELECT
                *,
                @row_number:=CASE
                    WHEN @number = number
                      THEN
                          @row_number + 1
                      ELSE
                           1
                    END AS rn,
                @number:=number,
                @side:=side
            FROM
                image,
                (SELECT @number:=0,@row_number:=0) as t
            WHERE side='back'
            ORDER BY
                created ASC
          ) i_partitioned
          WHERE i_partitioned.rn = 1
        ) AS dbi ON (a.number = dbi.number)
        ". $where ."
          ORDER BY a.number, a.order_ ASC
      ";
    }

    public function read()
    {
        $stmt = $this->conn->prepare($this->generic_read_query());
        $stmt->execute();

        return $stmt;
    }

    public function readPage(
        $from,
        $pageSize,
        $showProducts,
        $hideProducts,
        $showTags,
        $hideTags,
        $searchTerm
    ) {
        $searchTerm = htmlspecialchars(strip_tags($searchTerm));
        $showProducts = asArray(htmlspecialchars(strip_tags($showProducts)));
        $hideProducts = asArray(htmlspecialchars(strip_tags($hideProducts)));
        $showTags = asArray(htmlspecialchars(strip_tags($showTags)));
        $hideTags = asArray(htmlspecialchars(strip_tags($hideTags)));

        $prefilter = "";
        $where = "";

        if (count($showProducts) > 0) {
            $inQuery = implode(',', array_fill(0, count($showProducts), '?'));
            $prefilter = $prefilter . "
            INNER JOIN (
              SELECT asset_id FROM asset_has_product
              WHERE product_id IN (
                SELECT id FROM product WHERE name IN  (". $inQuery .")
              )
            ) show_product_filter ON (a.id = show_product_filter.asset_id)";
        }
        if (count($hideProducts) > 0) {
            $inQuery = implode(',', array_fill(0, count($hideProducts), '?'));
            $prefilter = $prefilter . "
            INNER JOIN (
              SELECT id as asset_id FROM asset
              WHERE id NOT IN (
                SELECT id FROM asset WHERE id IN (
                  SELECT asset_id FROM asset_has_product
                  WHERE product_id IN (
                    SELECT id FROM product WHERE name IN  (". $inQuery .")
                  )
                )
              )
            ) hide_product_filter ON (a.id = hide_product_filter.asset_id)";
        }

        if (count($showTags) > 0) {
            $inQuery = implode(',', array_fill(0, count($showTags), '?'));
            $prefilter = $prefilter . "
            INNER JOIN (
              SELECT asset_id FROM asset_has_tag
              WHERE tag_id IN (
                SELECT id FROM tag WHERE name IN  (". $inQuery .")
              )
            ) show_tag_filter ON (a.id = show_tag_filter.asset_id)";
        }
        if (count($hideTags) > 0) {
            $inQuery = implode(',', array_fill(0, count($hideTags), '?'));
            $prefilter = $prefilter . "
            INNER JOIN (
              SELECT id as asset_id FROM asset
              WHERE id NOT IN (
                SELECT id FROM asset WHERE id IN (
                  SELECT asset_id FROM asset_has_tag
                  WHERE tag_id IN (
                    SELECT id FROM tag WHERE name IN  (". $inQuery .")
                  )
                )
              )
            ) hide_tag_filter ON (a.id = hide_tag_filter.asset_id)";
        }

        if (!empty($searchTerm)) {
            $where = "HAVING LOWER(CONCAT(name, ',', number, ',', tags, products)) LIKE CONCAT('%', ?, '%')";
        }

        $query = $this->generic_read_query(
            $prefilter,
            $where
        ) . "
            LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);

        $startIndex=0;
        foreach ($showProducts as $k => &$el) {
            $startIndex = $startIndex+1;
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam($startIndex, $el);
        }
        foreach ($hideProducts as $k => &$el) {
            $startIndex = $startIndex+1;
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam($startIndex, $el);
        }
        foreach ($showTags as $k => &$el) {
            $startIndex = $startIndex+1;
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam($startIndex, $el);
        }
        foreach ($hideTags as $k => &$el) {
            $startIndex = $startIndex+1;
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam($startIndex, $el);
        }

        if (!empty($searchTerm)) {
            $startIndex=$startIndex+1;
            $stmt->bindParam($startIndex, $searchTerm);
        }
        $stmt->bindParam($startIndex+1, $from, PDO::PARAM_INT);
        $stmt->bindParam($startIndex+2, $pageSize, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    public function searchUpsert()
    {
        $where = "HAVING number=:number AND order_=:order";
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

        try {
            if ($stmt->execute()) {
                $this->id=$this->conn->lastInsertId();
                return true;
            }
            $this->error=json_encode($stmt->errorInfo());
            return false;
        } catch (\PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $this->error="Asset already exists.";
            } else {
                $this->error=implode(":", $e->errorInfo);
            }

            return false;
        }
    }

    public function getId()
    {
        $query = "SELECT id FROM " . $this->table_name .
               " WHERE number=:number AND order_=:order";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":number", $this->number, PDO::PARAM_INT);
        $stmt->bindParam(":order", $this->order, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            return false;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->id = $row["id"];

        return true;
    }

    public function update($data)
    {
        try {
            $this->number=htmlspecialchars(strip_tags($data->number));
            $this->order=htmlspecialchars(strip_tags($data->order));

            if (!$this->getId()) {
                $this->error="Asset not found";
                return false;
            }

            $setSection = array();

            if (isset($data->display_size)) {
                $this->display_size = htmlspecialchars(strip_tags(
                    $data->updateDisplaySize
                ));
                array_push($setSection, "display_size=:display_size");
            }
            if (isset($data->printed_size)) {
                $this->printed_size=htmlspecialchars(strip_tags($data->updatePrintSize));
                array_push($setSection, "printed_size=:printed_size");
            }
            if (isset($data->notes)) {
                $this->notes=htmlspecialchars(strip_tags($data->updateNotes));
                array_push($setSection, "notes=:notes");
            }

            if (!empty($setSection)) {
                $setSection = implode(',', $setSection);

                $query = "UPDATE " . $this->table_name . " SET " . $setSection . "
              WHERE number=:number AND order_=:order";

                $stmt = $this->conn->prepare($query);

                $stmt->bindParam(":number", $this->number, PDO::PARAM_INT);
                $stmt->bindParam(":order", $this->order, PDO::PARAM_INT);
                if (isset($data->display_size) && !empty($this->display_size)) {
                    $stmt->bindParam(":display_size", $this->display_size);
                }
                if (isset($data->printed_size) && !empty($this->printed_size)) {
                    $stmt->bindParam(":printed_size", $this->printed_size);
                }
                if (isset($data->notes) && !empty($this->notes)) {
                    $stmt->bindParam(":notes", $this->notes);
                }

                if (!$stmt->execute()) {
                    $this->error=json_encode($stmt->errorInfo());
                    return false;
                }
            }
            if (isset($data->updateProduct) &&
                !$this->updateProduct($data->updateProduct)
               ) {
                return false;
            }
            if (isset($data->deleteTags) && !$this->deleteTags($data->deleteTags)) {
                return false;
            }
            if (isset($data->addTags) && !$this->addTags($data->addTags)) {
                return false;
            }
            if (isset($data->deleteRelated) &&
                !$this->deleteRelated($data->deleteRelated)
               ) {
                return false;
            }
            if (isset($data->addRelated) && !$this->addRelated($data->addRelated)) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->error=implode(":", $e->errorInfo);
            return false;
        }
    }

    public function updateProduct($productName)
    {
        $productName=htmlspecialchars(strip_tags($productName));
        if (empty($productName)) {
            return true;
        }

        error_log("Before INSERT");

        $query = "INSERT INTO asset_has_product
                  SET asset_id=:asset_id, product_id=:product_id";

        $query = "INSERT INTO asset_has_product(asset_id, product_id)
          SELECT :asset_id, id FROM product WHERE name LIKE :product_name";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":product_name", $productName);

        try {
            if ($stmt->execute()) {
                return true;
            }
            $this->error=json_encode($stmt->errorInfo());

            return false;
        } catch (\PDOException $e) {
            $this->error=implode(":", $e->errorInfo);
            return false;
        }

        $query = "DELETE FROM asset_has_product WHERE product_id!=:product_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product->id, PDO::PARAM_INT);

        $stmt->execute();

        return true;
    }

    public function deleteTags($array)
    {
        if (count($array) == 0) {
            return true;
        }

        $inQuery = implode(',', array_fill(0, count($array), '?'));

        $query = "DELETE FROM asset_has_tag WHERE tag_id IN
          (SELECT id FROM tag WHERE name IN ( ". $inQuery ."))";

        $stmt = $this->conn->prepare($query);

        foreach ($array as $k => $el) {
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam(($k+1), $el);
        }

        if (!$stmt->execute()) {
            $this->error=json_encode($stmt->errorInfo());
            return false;
        }

        return true;
    }

    public function deleteRelated($array)
    {
        if (count($array) == 0) {
            return true;
        }

        $inQuery = implode(',', array_fill(0, count($array), '?'));

        $query = "DELETE FROM related_creatures WHERE creature IN (". $inQuery .")";

        $stmt = $this->conn->prepare($query);

        foreach ($array as $k => $el) {
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam(($k+1), $el, PDO::PARAM_INT);
        }

        if (!$stmt->execute()) {
            $this->error=json_encode($stmt->errorInfo());
            return false;
        }

        return true;
    }

    public function addTags($array)
    {
        if (count($array) == 0) {
            return true;
        }

        $inQuery = implode(',', array_fill(0, count($array), '?'));

        $query = "INSERT INTO asset_has_tag(asset_id, tag_id)
          SELECT ?, id FROM tag WHERE name IN (". $inQuery .")";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        foreach ($array as $k => $el) {
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam(($k+2), $el);
        }

        if (!$stmt->execute()) {
            $this->error=json_encode($stmt->errorInfo());
            return false;
        }

        return true;
    }

    public function addRelated($array)
    {
        if (count($array) == 0) {
            return true;
        }

        $inQuery = implode(',', array_fill(0, count($array), '?'));

        $query = "INSERT INTO related_creatures(asset, creature)
          SELECT ?, id FROM creature WHERE id IN (". $inQuery .")";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->id, PDO::PARAM_INT);
        foreach ($array as $k => $el) {
            $el = htmlspecialchars(strip_tags($el));
            $stmt->bindParam(($k+2), $el, PDO::PARAM_INT);
        }

        if (!$stmt->execute()) {
            $this->error=json_encode($stmt->errorInfo());
            return false;
        }

        return true;
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
