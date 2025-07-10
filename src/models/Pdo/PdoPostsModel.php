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
  public function getPostByIdOrAuthor(string $term):array{
        $sql = "SELECT 
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
            WHERE p.id = :exactId  
               OR LOWER(author) LIKE :pattern";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':exactId' => $term,
            ':pattern' => '%' . strtolower($term) . '%'
        ]);

        return $stmt->fetchAll();
  }
  public function create(string $category_id,string $title,string $body,string $author):void{
    $sql = "INSERT INTO {$this->table} (category_id,title,body,author) VALUES(:category_id,:title,:body,:author)";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
      'category_id' => $category_id,
      'title' => $title,
      'body' => $body,
      'author' => $author
    ]);


  }
  public function update(int $id,string $category_id,string $title,string $body,string $author): void{
     $sql = "UPDATE {$this->table} SET category_id = :category_id,title = :title,body = :body,author = :author WHERE id = :id";

     $stmt = $this->conn->prepare($sql);
     $stmt->execute([
      'category_id' => $category_id,
      'title' => $title,
      'body' => $body,
      'author' => $author,
      'id' => $id
    ]);

  }
  public function Delete (int $id):void{
     $sql = "DELETE FROM {$this->table} WHERE id = :id";

     $stmt = $this->conn->prepare($sql);

     $stmt->execute([
      'id' => $id
    ]);
  }
}