<?php
// ─── CORS HEADERS ────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/helpers.php';


class Comment{
        //db related properties
        private $conn;
        //behind the = is the name of the table within the database
        private $table = "comments";
        private $alias = "c";

        //table fields; same names as the table 
        public $id;
        public $content;
        public $postId;
        public $userId;
        public $created_at

        //constructor with db connection to be opened up immediately
        //function that is triggered automatically when an instance of the class is created
        public function __construct($db){
            $this->conn = $db;
        }

        //with interpolation it allows us to change the table name in the case that we change the table name within the database
        public function read(){
            $query = "SELECT * FROM {$this->table} AS {$this->alias} ORDER BY {$this->alias}.id ASC;";

            $stmt = $this->conn->prepare($query);

            $stmt->execute();

            return $stmt;
        }

        //a function that reads a single user
        public function readSingle(){
            $query = "SELECT *
                        FROM {$this->table} AS {$this->alias}
                        WHERE {$this->alias}.id = ?
                        LIMIT 1;"; //ensures that it only gives us one result from the SQL

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->id);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if($row > 0){
                $this->content = $row["content"];
                $this->postId = $row["postId"];
                $this->userId = $row["userId"];
                $this->created_at = $row["created_at"];
            }
            return $stmt;
        }

        public function readByArticle() {
            $query = "SELECT 
                        {$this->alias}.id,
                        {$this->alias}.content,
                        {$this->alias}.userId,
                        {$this->alias}.created_at,
                        u.username,
                        u.display_name
                    FROM {$this->table} AS {$this->alias}
                    LEFT JOIN users u ON {$this->alias}.userId = u.id
                    WHERE {$this->alias}.articleId = ?
                    ORDER BY {$this->alias}.created_at DESC";
    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->articleId);
            $stmt->execute();
            return $stmt;
        }

        // editing comments function
        public function editComment() {
            $query = "UPDATE {$this->table}
                        SET content = :content
                        WHERE id = :id
                        AND userId = :userId";  //only owner can update their comment
    
            $stmt = $this->conn->prepare($query);
    
            $this->id = htmlspecialchars(strip_tags($this->id));
            $this->content = htmlspecialchars(strip_tags($this->content));
            $this->userId = htmlspecialchars(strip_tags($this->userId));
    
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":content", $this->content);
            $stmt->bindParam(":userId", $this->userId);
    
            if ($stmt->execute()) {
                return true;
            }
    
            return false;
        }

        public function delete() {
            $query = "DELETE FROM {$this->table}
                    WHERE id = :id
                    AND userId = :userId";  //only owner can delete comment with this function
    
            $stmt = $this->conn->prepare($query);
    
            $this->id = htmlspecialchars(strip_tags($this->id));
            $this->userId = htmlspecialchars(strip_tags($this->userId));
    
            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":userId", $this->userId);
    
            if ($stmt->execute()) {
                return true;
            }
    
            return false;
        }

        //whoever has the role of admin can delete any comment
        public function adminDelete() {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
    
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(":id", $this->id);
    
            if ($stmt->execute()) {
                return true;
            }
    
            return false;
        }
    }
?>