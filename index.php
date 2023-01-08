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

$app->get('/canva', function($request, $response, $args) {
  // <canvas class="canvasjs-chart-canvas" width="1904" height="370" style="position: absolute; user-select: none;"></canvas>

  $points = [

                        /* Espaço entre as colunas | altura da coluna */ 
/*ponto A eixos x e y*/            10,                       200,        
/*ponto B eixos x e y*/            50,                       200,
/*ponto C eixos x e y*/            50,                       370,
/*ponto D eixos x e y*/            10,                       370   
     
  ];

$points2 = [
  140,  80,  // Point 1 (x, y)
  100,  80,
  100,  370,
  140,  370   
];

  $image = Image::canvas(1904, 370, )
  ->polygon($points, function($draw){
    $draw->background('#000');
  })
  ->polygon($points2, function($draw){
    $draw->background('#ff000');
  })
  ->encode('data-url');

  $response->getBody()->write(json_encode([
    'data' => $image
  ]));

  return $response->withHeader('Content-type', 'application/json')
                  ->withHeader('Access-Control-Allow-Origin', '*')
                  ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                  ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');

});
// Run app
$app->run();
