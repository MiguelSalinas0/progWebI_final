<?php

require __DIR__ . '/commentservice.php';


// Trae todos los post con comentarios y el autor
function getAllPost()
{
    global $pdo;
    $data = [];
    try {
        $query = "SELECT
        p.post_id AS post_id,
        p.contenido AS contenido,
        p.fecha_publicacion AS fecha_publicacion,
        u.user_id AS user_id,
        (SELECT COUNT(*) FROM Likes l WHERE l.post_id = p.post_id) AS cantidad_likes,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) AS cantidad_comentarios
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        ORDER BY p.fecha_publicacion DESC;";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($posts as &$post) {
            $post['comentarios'] = getComments($post['post_id']);
            $post['autor'] = getAutor($post['user_id']);
        }
        $data = ['success' => true, 'data' => $posts];
    } catch (PDOException $e) {
        $data = ['success' => false, 'error' => $e->getMessage()];
    }
    return $data;
}

function getAutor($id) // Trae datos del autor de un post
{
    global $pdo;
    $user = [];
    $query = "SELECT u.nombre, u.apellido, u.profile
    FROM users u
    WHERE u.user_id = ?";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    return $user;
}


// Trae todos los post realizados por un usuario con comentarios
function getAllPostUser($request, $response, $args)
{
    global $pdo;
    $id = $args['id'];

    // Verificar si el usuario existe
    $userQuery = "SELECT COUNT(*) FROM users WHERE user_id = ?";
    $userStatement = $pdo->prepare($userQuery);
    $userStatement->bindParam(1, $id);
    $userStatement->execute();
    $userExists = $userStatement->fetchColumn();

    if (!$userExists) {
        $data = ['success' => false, 'message' => 'Usuario no encontrado'];
        return $data;
    }

    // El usuario existe, obtener los posts
    $posts = [];
    $query = "SELECT p.post_id, p.contenido, p.fecha_publicacion, 
    (SELECT COUNT(*) FROM Likes l WHERE l.post_id = p.post_id) AS cantidad_likes
    FROM posts p
    JOIN users u ON p.user_id = u.user_id
    WHERE u.user_id = ?";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($posts as &$post) {
        $post['comentarios'] = getComments($post['post_id']);
    }
    $data = ['success' => true, 'autor' => getAutor($id), 'posts' => $posts];
    return $data;
}


// Se registra un nuevo post
function addPostU($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    if (isset($jsonData['user_id'], $jsonData['contenido']) && !empty($jsonData['user_id']) && !empty($jsonData['contenido'])) {
        try {
            $query = "INSERT INTO posts (user_id, contenido) VALUES (?,?)";
            $statement = $pdo->prepare($query);
            $values = [
                $jsonData['user_id'],
                $jsonData['contenido']
            ];
            $statement->execute($values);
            $postId = $pdo->lastInsertId();
            if ($postId) {
                $post = getOnePost($postId);
                $data = ['success' => true, 'data' => $post];
            }
        } catch (PDOException $e) {
            $data = ['success' => false, 'error' => 'Error en la consulta SQL'];
        }
    } else {
        $data = ['success' => false, 'error' => 'Datos incorrectos o incompletos'];
    }
    return $data;
}


// Se actualiza un post
function updatePost($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    if (isset($jsonData['post_id'], $jsonData['contenido']) && !empty($jsonData['post_id']) && !empty($jsonData['contenido'])) {
        try {
            $contenido = $jsonData['contenido'];
            $post_id = $jsonData['post_id'];
            $query = "UPDATE posts SET contenido = ? WHERE posts.post_id = ?";
            $statement = $pdo->prepare($query);
            $statement->execute([$contenido, $post_id]);
            if ($statement->rowCount() > 0) {
                $post = getOnePost($post_id);
                $data = ['success' => true, 'data' => $post];
            }
        } catch (PDOException $e) {
            $data = ['success' => false, 'message' => 'Error:' . $e->getMessage()];
        }
    } else {
        $data = ['success' => false, 'error' => 'Datos incorrectos o incompletos'];
    }
    return $data;
}


// Eliminar un post
function deletePost($request, $response, $args)
{
    global $pdo;
    $id = $args['id'];
    $data = [];
    try {
        removeComments($id);
        $query = 'DELETE FROM posts WHERE post_id = ?';
        $statement = $pdo->prepare($query);
        $statement->bindParam(1, $id);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            $data = ['success' => true, 'message' => 'Post eliminado exitosamente.'];
        } else {
            $data = ['success' => false, 'message' => 'No se encontró el post especificado.'];
        }
    } catch (PDOException $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Se trae un unico post - utilizado en registrar, actualizar
function getOnePost($id)
{
    global $pdo;
    $query = 'SELECT * FROM posts p WHERE p.post_id = ?';
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $post = $statement->fetch(PDO::FETCH_ASSOC);
    return $post;
}
