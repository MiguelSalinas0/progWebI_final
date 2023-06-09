<?php

require __DIR__ . '/../services/likeservice.php';

$app->post('/like/eval', 'EvalLikePost');


function EvalLikePost($request, $response)
{
    $datos = likePost($request, $response);
    $response->getBody()->write(json_encode($datos, JSON_PRETTY_PRINT));
    return $response;
}
