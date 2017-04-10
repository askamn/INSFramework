<?php
class ImageHandler
{
	public $options = array(
		/* Image resolution restrictions: */
		'max_width' => null,
		'max_height' => null,
		'min_width' => 1,
		'min_height' => 1,
		'orient_image' => false,			
		'image_versions' => array(                
			    'medium' => array(
    			'max_width' => 1920,
    			'max_height' => 1200,
    			'jpeg_quality' => 95
			),
    			'medium' => array(
    			'max_width' => 800,
    			'max_height' => 600,
    			'jpeg_quality' => 80
			),
    			'thumbnail' => array(
    			'max_width' => 80,
    			'max_height' => 80
			)
	    )
    );    
	protected function createScaledImage($filepath, $options) 
	{
        list($img_width, $img_height) = @getimagesize($filepath);
		
        if (!$img_width || !$img_height) 
		{
            return false;
        }
        $scale = min($options['max_width'] / $img_width, $options['max_height'] / $img_height);
		
		// Already scaled?
        if ($scale >= 1) 
		{
            return true;
        }
		
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
		
        switch (strtolower(substr(strrchr($file_name, '.'), 1))) 
		{
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($filepath);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($filepath);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($filepath);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $filepath, $image_quality);

        @imagedestroy($src_img);
        @imagedestroy($new_img);
        return $success;
    }
}
?>