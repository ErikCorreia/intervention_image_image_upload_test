<?php
//||===========================================================||
//|| Pagina da biblioneca | https://image.intervention.io/v2   ||
//||===========================================================||
//|| Verção minima do php | PHP >= 5.4 Fileinfo Extension      ||
//|| PHH Extensão         | GD Library (>=2.0)                 ||
//||                      | or Imagick PHP extension (>=6.5.7) ||
//||===========================================================||
//||             composer require intervention/image           ||
//||===========================================================||
//||                (Teste terminal php index.php)             ||
//||===========================================================||


//||=====================================================||
//||    php extension dependence     |      imagick      ||
//||=====================================================||
//||  class |      ImageManager      |       Canvas      ||
//||=====================================================||
//|| detail |     gera a imagem      |   cria uma imagem || 
//||        | (thumb, medium, large) |                   ||
//||=====================================================||

// include composer autoload
require 'vendor/autoload.php';

// import the Intervention Image Manager Class
use Intervention\Image\ImageManagerStatic as Image;

class ImageManager 
{    

    protected $image;
    protected $size;
    protected $path;

    public function __construct(string $image_origin = 'src/foo.jpg', string $path_to_save_image = 'src/')
    {
        // configure with favored image driver (gd by default)
        Image::configure(['driver' => 'imagick']);

        $this->image = Image::make($image_origin);

        // $this->path = 'storage/images/';

        $this->path = $path_to_save_image;
        
    }

    public function createWatherMark()
    {

        $watermark = Image::canvas(150, 100, '#25a11a');
        
        $points = [
            0,    50,  // Point 1 (x, y)
            75,   0,   // Point 1 (x, y)
            150,  50,  // Point 1 (x, y)
            75,   100, // Point 1 (x, y)
        ];
        
        $watermark->polygon($points, function ($draw) {
            $draw->background('#eeff01');
        });
        
        $watermark->ellipse(70, 70, 75, 50, function ($draw) {
            $draw->background('#0035e4');
        });
        
        // $watermark->blur(1);
        $this->image->insert($watermark, 'bottom-right', 100, 100);
        
    }

    public function saveImage(string $size = 'thumb'){
        switch($size){
            case 'thumb': 
                $this->image->resize(450, 220);
                $this->image->fit(450, 220);
                $this->image->save($this->path . $size.'.jpg');
            break;
    
            case 'medium': 
                $this->image->resize(800, 520);
                $this->image->fit(800, 520);
                $this->image->save($this->path . $size.'.jpg');
            break;
                
            case 'large': 
                $this->image->resize(1920, 1080);
                $this->image->fit(1920, 1080);
                $this->image->save($this->path . $size.'.jpg');
            break;
        }

    }
    public function destroy()
    {
        $this->image->destroy();        
    }
}

class Canvas
{
    static function create()
    {
        Image::configure(['driver' => 'imagick']);

        $img = Image::canvas(150, 150, '#fff');
        
        $img->ellipse(100, 100, 75, 75, function($draw){
            $draw->background('rgba(0,0,0,.5)');
        });
        
        $img->save('src/bar.png');
        $img->destroy();
    }

}

$img = new ImageManager();
// $img->createWatherMark();
$img->saveImage('large');
$img->saveImage('thumb');
$img->saveImage('medium');
$img->destroy();

// $canva = Canvas::create();
