<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Intervention\Image\ImageManagerStatic as Image;

require 'vendor/autoload.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/image', function (Request $request, Response $response, $args) {
  Image::configure(['driver' => 'imagick']);  
  $image = Image::make('src/foo.jpg')->response('jpg');
    $response->getBody()->write($image);
    return $response;
});

// Run app
$app->run();
