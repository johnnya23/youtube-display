<?php

/**
 * class JMAYtOverlay
 * $urls is an array of thumbnail urls
 * $id is a video id
 * Pull images from youtube with fetch_image(
 */
class JMAYtOverlay {
    var $urls;
    var $id;

    public function __construct($urls, $id){
        $this->urls = $urls;
        $this->id = $id;
    }
    /*
     * get_url
     * @variable $urls is an array of image urls
     * starting with most desirable image size from youtube
     *
     *
     * */
    public function get_url(){
        $sep = DIRECTORY_SEPARATOR;
        $urls = $this->urls;
        $folder = realpath(plugin_dir_path(__FILE__)) . $sep . 'overlays';
        if (!is_dir($folder))
            mkdir($folder, '0755');

        foreach($urls as $url) {

            $ex = explode('.', basename($url));
            $ext = $ex[1];
            $return = plugins_url('/overlays/' . $this->id . '.' . $ext, __FILE__);

            //asign image to overlays folder with name of youtube id (plus extension)
            $filename = $folder . $sep . $this->id . '.' . $ext;
            //transient evaluated here -- also have to check for image file in overlays folder
            if(!file_exists($filename)){

                if($this->fetch_image($url, $folder, $this->id)){

                    break;//no need to check remaining images
                }
            }else
                break;
        }

        return $return;
    }

    /**
     * Fetch JPEG or PNG or GIF Image
     *
     * A custom function in PHP which lets you fetch jpeg or png images from remote server to your local server
     * Can also prevent duplicate by appending an increasing _xxxx to the filename. You can also overwrite it.
     *
     *
     * @author Swashata <swashata ~[at]~ intechgrity ~[dot]~ com>
     * @copyright Do what ever you wish - I like GPL <img draggable="false" class="emoji" alt="🙂" src="https://s.w.org/images/core/emoji/2.3/svg/1f642.svg"> (& love tux ;))
     * @link https://www.intechgrity.com/?p=808
     *
     * @param string $img_url The URL of the image. Should start with http or https followed by :// and end with .png or .jpeg or .jpg or .gif. Else it will not pass the validation
     * @param string $store_dir The directory where you would like to store the images.
     * @return string the location of the image (either relative with the current script or abosule depending on $store_dir_type)
     */
    protected function fetch_image($img_url, $store_dir = 'images', $filename = 'default') {
        //first get the base name of the image
        $i_name = $filename;

        //now try to guess the image type from the given url
        //it should end with a valid extension...
        //good for security too
        if(preg_match('/https?:\/\/.*\.png$/i', $img_url)) {
            $img_type = 'png';
        }
        else if(preg_match('/https?:\/\/.*\.(jpg|jpeg)$/i', $img_url)) {
            $img_type = 'jpg';
        }
        else if(preg_match('/https?:\/\/.*\.gif$/i', $img_url)) {
            $img_type = 'gif';
        }
        else {
            return ''; //possible error on the image URL
        }

        $dir_name = rtrim($store_dir, '/') . '/';

        //create the directory if not present
        if(!file_exists($dir_name))
            mkdir($dir_name, 0777, true);

        //calculate the destination image path
        $i_dest = $dir_name . $i_name . '.' . $img_type;

        //first check if the image is fetchable
        $img_info = @getimagesize($img_url);

        //is it a valid image?
        if(false == $img_info || !isset($img_info[2]) || !($img_info[2] == IMAGETYPE_JPEG || $img_info[2] == IMAGETYPE_PNG || $img_info[2] == IMAGETYPE_JPEG2000 || $img_info[2] == IMAGETYPE_GIF)) {
            return ''; //return empty string
        }

        //now try to create the image
        if($img_type == 'jpg') {
            $m_img = @imagecreatefromjpeg($img_url);
        } else if($img_type == 'png') {
            $m_img = @imagecreatefrompng($img_url);
            @imagealphablending($m_img, false);
            @imagesavealpha($m_img, true);
        } else if($img_type == 'gif') {
            $m_img = @imagecreatefromgif($img_url);
        } else {
            $m_img = FALSE;
        }

        //was the attempt successful?
        if(FALSE === $m_img) {
            return '';
        }

        //now attempt to save the file on local server
        if($img_type == 'jpg') {
            if(imagejpeg($m_img, $i_dest, 100))
                return $i_dest;
            else
                return '';
        } else if($img_type == 'png') {
            if(imagepng($m_img, $i_dest, 0))
                return $i_dest;
            else
                return '';
        } else if($img_type == 'gif') {
            if(imagegif($m_img, $i_dest))
                return $i_dest;
            else
                return '';
        }

        return '';
    }


}
