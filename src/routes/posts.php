<?php

require __DIR__ . '/../services/postservice.php';

$app->get('/post/getall', 'getAll');
$app->post('/post/add', 'addPost');
$app->get('/post/getallpostuser/{id}', 'getAllPostU');
$app->put('/post/update', 'updateContPost');
$app->delete('/post/remove/{id}', 'removePost');

$app->post('/post/addcomment', 'addComment');
$app->get('/post/getcomment/{id}', 'getComment');

function getAll($request, $response)
{
    $datos = getAllPost();
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function addPost($request, $response)
{
    $datos = addPostU($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function getAllPostU($request, $response, $args)
{
    $datos = getAllPostUser($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function updateContPost($request, $response)
{
    $datos = updatePost($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function removePost($request, $response, $args)
{
    $datos = deletePost($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}

function addComment($request, $response)
{
    $datos = commentAdd($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}

function getComment($request, $response, $args)
{
    $datos = commentGet($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}
