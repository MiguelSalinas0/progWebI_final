<?php

include 'funciones.php';

// Login
function ingresar($request, $response)
{
    global $pdo;
    $data = [];
    $body = $request->getBody();
    $jsonData = json_decode($body, true);
    $email = $jsonData['email'];
    $password = $jsonData['password'];
    try {
        $query = 'SELECT * FROM users u WHERE u.correo_electronico = ?';
        $statement = $pdo->prepare($query);
        $statement->bindParam(1, $email);
        $statement->execute();
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$user || $password !== $user['contrasena']) {
            $data = ['success' => false, 'message' => 'Credenciales inválidas'];
        } else {
            $data = ['success' => true, 'message' => 'Inicio de sesión exitoso', 'user' => $user];
        }
    } catch (PDOException $e) {
        $data = ['success' => false, 'error' => $e->getMessage()];
    }
    return $data;
}


// Trae a todos los usuarios
function getAllUser($request, $response, $args)
{
    global $pdo;
    $id = $args['id'];
    $data = [];
    try {
        $query = "SELECT u.user_id, u.nombre, u.apellido, u.correo_electronico, u.biografia, u.profile
        FROM users u
        WHERE u.user_id <> ?;";
        $statement = $pdo->prepare($query);
        $statement->bindParam(1, $id);
        $statement->execute();
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as &$user) {
            $user['profile'] = codificarIMG($user['profile']);
        }
        $data = ['success' => true, 'data' => $users];
    } catch (PDOException $e) {
        $data = ['success' => false, 'error' => $e->getMessage()];
    }
    return $data;
}


// Trae un unico usuario
function getOneUser($request, $response, $args)
{
    global $pdo;
    $id = $args['id'];
    $user = [];
    $query = "SELECT u.user_id, u.nombre, u.apellido, u.correo_electronico, u.biografia, u.profile,
    (SELECT COUNT(*) FROM followers f WHERE u.user_id = f.followed_user_id) AS cantidad_seguidores,
    (SELECT COUNT(*) FROM followers f WHERE u.user_id = f.user_id) AS cantidad_seguidos
    FROM users u
    WHERE u.user_id = ?";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        if (!empty($user['profile'])) {
            $user['profile'] = codificarIMG($user['profile']);
        }
    }
    return $user;
}


// Trae dos arreglos, uno con los seguidores y otro con los seguidos
function getFollowAndFollowers($request, $response, $args)
{
    $id = $args['id'];
    $data = ['seguidores' => getFollowers($id), 'seguidos' => getFollowU($id)];
    return $data;
}

function getFollowers($id) // Trae los seguidores
{
    global $pdo;
    $query = "SELECT u.user_id, u.nombre, u.apellido, u.correo_electronico, u.profile
    FROM users u
    JOIN followers f ON f.followed_user_id = ?
    WHERE u.user_id = f.user_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $followers = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($followers as &$user) {
        $user['profile'] = codificarIMG($user['profile']);
    }
    return $followers;
}

function getFollowU($id) // Trae los seguidos
{
    global $pdo;
    $query = "SELECT u.user_id, u.nombre, u.apellido, u.correo_electronico, u.profile
    FROM users u
    JOIN followers f ON f.user_id = ?
    WHERE u.user_id = f.followed_user_id";
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $follow = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($follow as &$user) {
        $user['profile'] = codificarIMG($user['profile']);
    }
    return $follow;
}


// Actualiza la foto de perfil de un usuario
function uploadIMG($request, $response)
{
    global $pdo;
    $uploadedFile = $request->getUploadedFiles()['image'];
    $formData = $request->getParsedBody();
    $imageName = $formData['imageName'];
    $u_id = $formData['userId'];
    $destination = dirname(dirname(__DIR__)) . '/imagenes/';
    $data = [];
    try {
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($destination, $uploadedFile, $imageName);
            $query = "UPDATE users SET users.profile = ? WHERE users.user_id = ?";
            $statement = $pdo->prepare($query);
            $values = [
                $filename,
                $u_id
            ];
            $statement->execute($values);
            if ($statement->rowCount() > 0) {
                $user = getOne($u_id);
                $data = ['success' => true, 'data' => $user];
            }
        }
    } catch (Exception $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}

// Guarda la foto de perfil y retorna el nombre y la extensión 
function moveUploadedFile($directory, $uploadedFile, $imageName)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $filename = sprintf('%s.%0.8s', $imageName, $extension);
    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    return $filename;
}


// Actualiza información del usuario
function updateInf($request, $response, $args)
{
    global $pdo;
    $id = $args['id'];
    $data = [];
    try {
        $body = $request->getBody();
        $jsonData = json_decode($body, true);
        // Validar que todos los campos estén presentes y no estén vacíos
        $requiredFields = ['nombre', 'apellido', 'correo_electronico', 'biografia'];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty($jsonData[$field])) {
                $data = ['success' => false, 'message' => 'Faltan campos requeridos o están vacíos.'];
                return $data;
            }
        }
        $query = "UPDATE users SET nombre = ?, apellido = ?, correo_electronico = ?, biografia = ? WHERE users.user_id = ?";
        $statement = $pdo->prepare($query);
        $values = [
            $jsonData['nombre'],
            $jsonData['apellido'],
            $jsonData['correo_electronico'],
            $jsonData['biografia'],
            $id
        ];
        $statement->execute($values);
        if ($statement->rowCount() > 0) {
            $user = getOne($id);
            $data = ['success' => true, 'data' => $user];
        }
    } catch (Exception $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Registra un nuevo usuario
function registerU($request, $response)
{
    global $pdo;
    $data = [];
    try {
        $body = $request->getBody();
        $jsonData = json_decode($body, true);
        // Validar que todos los campos estén presentes y no estén vacíos
        $requiredFields = ['nombre', 'apellido', 'correo_electronico', 'contrasena', 'biografia'];
        foreach ($requiredFields as $field) {
            if (!isset($jsonData[$field]) || empty($jsonData[$field])) {
                $data = ['success' => false, 'message' => 'Faltan campos requeridos o están vacíos.'];
                return $data;
            }
        }
        $query = "INSERT INTO users (nombre, apellido, correo_electronico, contrasena, biografia) VALUES (?,?,?,?,?)";
        $statement = $pdo->prepare($query);
        $values = [
            $jsonData['nombre'],
            $jsonData['apellido'],
            $jsonData['correo_electronico'],
            $jsonData['contrasena'],
            $jsonData['biografia']
        ];
        $statement->execute($values);
        $userId = $pdo->lastInsertId();
        if ($userId) {
            $user = getOne($userId);
            $data = ['success' => true, 'data' => $user];
        }
    } catch (Exception $e) {
        $data = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
    return $data;
}


// Trae un unico usuario con todos los datos - utilizado en registrar y actualizar
function getOne($id)
{
    global $pdo;
    $query = 'SELECT * FROM users WHERE users.user_id = ?';
    $statement = $pdo->prepare($query);
    $statement->bindParam(1, $id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    return $user;
}
