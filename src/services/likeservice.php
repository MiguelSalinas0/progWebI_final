<?php

// Lógica para dar o quitar un like a un post
function likePost($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    $userId = $jsonData['user_id'];
    $postId = $jsonData['post_id'];

    try {
        $liked = checkIfUserLikedPost($pdo, $userId, $postId);

        if ($liked) {
            removeLike($pdo, $userId, $postId);
            $data = ['success' => true, 'message' => 'Like removido'];
        } else {
            addLike($pdo, $userId, $postId);
            $data = ['success' => true, 'message' => 'Like agregado'];
        }
    } catch (Exception $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    return $data;
}

function checkIfUserLikedPost($pdo, $userId, $postId)
{
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?');
        $stmt->execute([$userId, $postId]);
        $count = $stmt->fetchColumn();
        return $count > 0;
    } catch (Exception $e) {
        throw new Exception('Error al verificar si el usuario dio like al post: ' . $e->getMessage());
    }
}

function addLike($pdo, $userId, $postId)
{
    try {
        $stmt = $pdo->prepare('INSERT INTO likes (user_id, post_id) VALUES (?, ?)');
        $stmt->execute([$userId, $postId]);
    } catch (Exception $e) {
        throw new Exception('Error al agregar el like al post: ' . $e->getMessage());
    }
}

function removeLike($pdo, $userId, $postId)
{
    try {
        $stmt = $pdo->prepare('DELETE FROM likes WHERE user_id = ? AND post_id = ?');
        $stmt->execute([$userId, $postId]);
    } catch (Exception $e) {
        throw new Exception('Error al remover el like del post: ' . $e->getMessage());
    }
}


// Función que trae todos los id de usuario que dieron like a una publicación
function allLikePost($postId)
{
    try {
        global $pdo;
        $stmt = $pdo->prepare('SELECT l.user_id FROM likes l WHERE l.post_id = ?');
        $stmt->execute([$postId]);
        $likes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $likes;
    } catch (Exception $e) {
        throw new Exception('Error al traer los id de usuarios que dieron like a un post: ' . $e->getMessage());
    }
}
