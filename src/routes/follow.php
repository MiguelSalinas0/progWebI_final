<?php

require __DIR__ . '/../services/followservice.php';

$app->post('/follow/eval', 'EvalFollowUser');
$app->post('/follow/add', 'AddFollowUser');
$app->delete('/follow/remove/{user_id}/{followed_user_id}', 'RemoveFollowUser');


function EvalFollowUser($request, $response)
{
    $datos = evalFollow($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function AddFollowUser($request, $response)
{
    $datos = addFollow($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}


function RemoveFollowUser($request, $response, $args)
{
    $datos = deleteFollow($request, $response, $args);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}
