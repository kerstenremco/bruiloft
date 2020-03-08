<?php
namespace helpers;
class imageHandler {
    private $image;
    private $path;
    private $valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'bmp');
    function __construct($type, $img)
    {
        $this->image = $img;
        switch($type) {
            case 'gift':
                $this->path = '/public/img/gifts/';
                break;
        }
    }

    function saveImage()
    {
        $ext = strtolower(pathinfo($this->image['name'], PATHINFO_EXTENSION));
        $final_name = rand(1000,1000000) . $this->image['name'] ;

        if(in_array($ext, $this->valid_extensions) == false) throw new \Exception('Type niet toegestaan', 403);

        $path = dirname(__DIR__, 1).$this->path.strtolower($final_name);

        if(move_uploaded_file($this->image['tmp_name'],$path) == false) throw new \Exception('Fout bij opslaan afbeelding', 500);
        
        return $final_name;
    }

    static function removeImage($type, $imgname)
    {
        switch($type) {
            case 'gift':
                $path = '/public/img/gifts/';
                break;
        }
        unlink(dirname(__DIR__, 1).$path.$imgname);
    }
}
?>