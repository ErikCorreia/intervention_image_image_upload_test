<?php

use GuzzleHttp\Psr7\UploadedFile;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use Intervention\Image\ImageManagerStatic as Image;

require 'vendor/autoload.php';

require 'image.php';

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function($request, $response, $args){

  $tpl = require 'home.phtml';

  $oldResponse = $response;

  $newStream = new \GuzzleHttp\Psr7\LazyOpenStream($tpl, 'r');
  $newResponse = $oldResponse->withBody($newStream);

  return $newResponse;

});

$app->post('/', function($request, $response, $args){

  $directory = __DIR__;
  $params = $request->getParsedBody();
  $uploadedFiles = $request->getUploadedFiles();

  $uploadedFile = $uploadedFiles['image'];
  // $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

  if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
    $image = Image::make($uploadedFile->getStream()->getMetadata('uri'))
    ->resize(500, 300)
    // ->save($directory.'/resized-image.png')
    ->response();
  }

  $response->getBody()->write($image);

  return $response;

});

$app->get('/image', function (Request $request, Response $response, $args) {

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

// cache image => eretorna a resposta e cria um cache de 10 segundos
$app->get('/cached-image', function($require, $response, $args){

  Image::configure(['drive' => 'imagick']);

  
  $image = Image::cache(function($img){
 
    $wathermark = Image::canvas(300, 50, 'rgba(255,255,255,.1)');
    $wathermark->text('My Wathermark', 150, 10, function($font) {
      $font->file('RubikBubbles-Regular.ttf');
      $font->size(24);
      $font->color('rgba(255,255,255, .3)');
      $font->align('center');
      $font->valign('top');
      $font->angle(0);
    });

    $img->make('src/foo.jpg')->insert($wathermark, 'bottom-right', 50, 50)->save('bar.png');
    
  }, 20, true)->response('png');

  $response->getBody()->write($image);
  return $response; 

});
// Run app
$app->run();
