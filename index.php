<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Intervention\Image\ImageManagerStatic as Image;

require 'vendor/autoload.php';

require 'image.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Define app routes
$app->get('/', function (Request $request, Response $response, $args) {

  Image::configure(['driver' => 'imagick']);

  $wathermark = Image::canvas(300, 50, 'rgba(255,255,255,.1)');
  $wathermark->text('My Wathermark', 150, 10, function($font) {
    $font->file('RubikBubbles-Regular.ttf');
    $font->size(24);
    $font->color('rgba(255,255,255, .3)');
    $font->align('center');
    $font->valign('top');
    $font->angle(0);
  });
  
  
  $image = Image::make('src/foo.jpg')
    ->resize(800, 400)
    ->insert($wathermark, 'bottom-right', 10, 10)
    ->fit(800, 400)->response('jpg');

  $response->getBody()->write($image);
  return $response;

});

// Run app
$app->run();
