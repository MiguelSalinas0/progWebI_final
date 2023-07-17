<?php

// Trae todos los comentarios de un post
function getComments($id)
{
    global $pdo;
    $query = "SELECT 
    c.comment_id AS id_comentario,
    c.contenido AS contenido_comentario,
    c.fecha_comentario AS fecha_comentario,
    u.profile,
    CONCAT(u.apellido, ' ', u.nombre) AS autor
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.post_id = ?";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $posts;
}


// Agrega un nuevo comentario
function commentAdd($request, $response)
{
    global $pdo;
    $data = [];
    try {
        $body = $request->getBody();
        $jsonData = json_decode($body, true);
        $requiredFields = ['user_id', 'post_id', 'contenido'];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty($jsonData[$field])) {
                $data = ['success' => false, 'message' => 'Faltan campos requeridos o están vacíos.'];
                return $data;
            }
        }
        $query = "INSERT INTO comments (user_id, post_id, contenido) VALUES (?,?,?)";
        $statement = $pdo->prepare($query);
        $values = [
            $jsonData['user_id'],
            $jsonData['post_id'],
            $jsonData['contenido']
        ];
        $statement->execute($values);
        $commentId = $pdo->lastInsertId();
        if ($commentId) {
            $comment = getOneComment($commentId);
            $data = ['success' => true, 'data' => $comment];
        }
    } catch (Exception $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Eliminar comentarios
function removeComments($id)
{
    global $pdo;
    $query = 'DELETE FROM comments WHERE post_id = ?';
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
}


// Trae un unico comentario - utilizado en agregar
function getOneComment($id)
{
    global $pdo;
    $query = 'SELECT * FROM comments c WHERE c.comment_id = ?';
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $comment = $statement->fetch(PDO::FETCH_ASSOC);
    return $comment;
}
