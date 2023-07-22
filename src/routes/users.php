<?php

require __DIR__ . '/../services/userservice.php';

$app->get('/user/getall/{id}', 'getAllUsers');
$app->get('/user/getuser/{id}', 'getUser');
$app->get('/user/getfollowers/{id}', 'getFollow');
$app->post('/user/register', 'register');
$app->post('/user/login', 'login');
$app->put('/user/update/{id}', 'updateInfo');


function getAllUsers($request, $response, $args)
{
    $datos = getAllUser($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function getUser($request, $response, $args)
{
    $datos = getOneUser($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function getFollow($request, $response, $args)
{
    $datos = getFollowAndFollowers($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function register($request, $response, $args)
{
    $datos = registerU($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function login($request, $response)
{
    $datos = ingresar($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function updateInfo($request, $response, $args)
{
    $datos = updateInf($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}
