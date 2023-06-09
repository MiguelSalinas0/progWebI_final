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
