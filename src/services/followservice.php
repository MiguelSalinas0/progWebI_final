<?php

// Evaluar si un usuario sigue a otro
function evalFollow($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    $user_id = $jsonData['user_id'];
    $followed_user_id = $jsonData['followed_user_id'];
    try {
        $query = "SELECT COUNT(*) FROM followers WHERE user_id = ? AND followed_user_id = ?";
        $statement = $pdo->prepare($query);
        if ($statement->execute([$user_id, $followed_user_id])) {
            $count = $statement->fetchColumn();
            $isFollowing = $count > 0;
            $data = ['success' => true, 'result' => $isFollowing];
        }
    } catch (PDOException $e) {
        $data = ['success' => false, 'result' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Dejar de seguir a un usuario
function deleteFollow($request, $response, $args)
{
    global $pdo;
    $data = [];
    $user_id = $args['user_id'];
    $followed_user_id = $args['followed_user_id'];
    try {
        $query = "DELETE FROM followers WHERE user_id = ? AND followed_user_id = ?";
        $statement = $pdo->prepare($query);
        $statement->bindParam(1, $user_id);
        $statement->bindParam(2, $followed_user_id);
        $statement->execute();
        if ($statement->rowCount() > 0) {
            $data = ['success' => true, 'result' => 'Follow eliminado exitosamente.'];
        }
    } catch (PDOException $e) {
        $data = ['success' => false, 'result' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Empezar a seguir a otro usuario
function addFollow($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    $user_id = $jsonData['user_id'];
    $followed_user_id = $jsonData['followed_user_id'];
    try {
        $query = "INSERT INTO followers (user_id, followed_user_id) VALUES (?, ?)";
        $statement = $pdo->prepare($query);
        $statement->execute([$user_id, $followed_user_id]);
        $data = ['success' => true, 'result' => 'Follow agregado exitosamente.'];
    } catch (PDOException $e) {
        $data = ['success' => false, 'result' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}
