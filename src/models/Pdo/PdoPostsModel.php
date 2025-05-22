<?php
namespace src\models\Pdo;

use PDO;
use src\core\Database\PdoDatabase;
use src\interfaces\PostsModelContract;
Class PdoPostsModel implements PostsModelContract{
  private string $table = "posts";
  private $conn; 
  public function __construct( PdoDatabase $PdoDatabase){
    $this->conn = $PdoDatabase;
  }
  public function read():array{
    $sql="SELECT 
           c.name as category_name,
           p.id,
           p.category_id,
           p.title,
           p.body,
           p.author,
           p.created_at
        FROM {$this->table} AS p
        LEFT JOIN
          categories AS c on p.category_id = c.id
        ORDER BY created_at";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $posts = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'body' => html_entity_decode($row['body']),
            'author' => $row['author'],
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name']
        ];
    }

    return $posts;
  }
}