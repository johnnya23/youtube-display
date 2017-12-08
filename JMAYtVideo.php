<?php
class JMAYtVideo {
    var $id;
    var $api;
    var $col_space;
    var $box_string;
    var $button_string;
    var $h3_string;
    var $trans_atts_id;
    var $item_font_length;

    public function __construct($id_code, $api_code){
        $this->api = $api_code;
        $this->id = $id_code;

    }
    protected function curl($url){
        global $jmayt_options_array;
        $curl = curl_init($url);

        $whitelist = array('127.0.0.1', "::1");
        if($jmayt_options_array['dev'] && in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//for localhost
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//for localhost
        }

        //curl_setopt($curl, CURLOPT_SSLVERSION,3);//forMAMP
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        $return = json_decode($result, true);
        if(!$return || array_key_exists ('error', $return)){
            if(array_key_exists ('error', $return))
                $return = $return['error']['errors'][0]['reason'];//keyInvalid, playlistNotFound, accessNotConfigured, ipRefererBlocked, keyExpired
            else
                $return = 'unknown';
        }
        return $return;
    }

    /*
     * function video_snippet()
     * @param string $id  a video id
     * @uses string $this->api the api key for the youtube site
     * @return array the snippet array for an individual video
     *
     * */
    private function video_snippet($id){
        if(substr( $id, 0, 4 ) === "http"){
            if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
                $id = $match[1];
            }
        }
        $snippet = array();
        $youtube = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $id . '&fields=items%2Fsnippet&key=' . $this->api;
        $curl_array = JMAYtVideo::curl($youtube);
        if(is_array($curl_array)){
            if(!count($curl_array['items']))
                return 'videoNotFound';
            $snippet = $curl_array['items'][0]['snippet'];
        }elseif(is_string($curl_array))
            $snippet = $curl_array;
        return $snippet;
    }

    /*
 * function map_meta()
 * @param $id string a video id (only for embed url)
 * @param $snippet the snippet array from youtube api
 * @return $yt_meta_array_items array schema values mapped to schema properties
 *
 * */
    private function map_meta($snippet, $id){//map youtude array to schema proprties
        $yt_meta_array_items['name'] = $snippet['title'];
        $yt_meta_array_items['publisher'] = $snippet['channelTitle'];
        $yt_meta_array_items['description'] = $snippet['description'];
        $yt_meta_array_items['thumbnailUrl'] = $snippet['thumbnails']['default']['url'];
        $yt_meta_array_items['standardUrl'] = $snippet['thumbnails']['standard']['url'];
        $yt_meta_array_items['embedURL'] = 'https://www.youtube.com/embed/' . $id;
        $yt_meta_array_items['uploadDate'] = $snippet['publishedAt'];
        return $yt_meta_array_items;
    }

    /*
     * function process_display_atts() processes relavent attributes (if present) into object properties
     * for use by single_html() and markup()
     * @param array $atts shortcode attributes to be processed
     *
     * @return array $return ('gutter', 'display') attribute - value pairs to be returned
     * to shortcode function
     *
     * */
    public function process_display_atts($atts){
        $this->col_space =
        $this->box_string =
        $this->button_string =
        $this->h3_string =
        $this->trans_atts_id = '';
        $this->item_font_length = -23;
        $return = array();
        //the relavent atributes to check for values
        $display_att_list = array( 'item_font_color', 'item_font_size', 'item_font_alignment', 'item_font_length', 'item_bg', 'item_border', 'item_gutter','item_spacing','button_font','button_bg', 'width', 'alignment' );
        //produce $display_atts with relavent values (if any)
        foreach($atts as $index => $att){
            if ( in_array( $index, $display_att_list ) ) {
                $trans_atts_id .= $index . $att;
                $display_atts[$index] = $att;
            }
        }
        //check for values and process producing style strings for each
        if(count($display_atts)){
            extract($display_atts);
            $this->trans_atts_id = $trans_atts_id;
            //number of characters in h3
            if(isset($item_font_length)) $this->item_font_length = $item_font_length;
            //box gutter and vertical spacing
            if($item_gutter || $item_spacing){
                if($item_gutter){
                    $item_gutter = floor($item_gutter/2);
                    $return['gutter'] = 'margin-left:-' . $item_gutter . 'px;margin-right:-' . $item_gutter . 'px;';
                }

                $gutter = $item_gutter? 'padding-left:' . $item_gutter . 'px;padding-right:' . $item_gutter . 'px;':'';
                $spacing = $item_spacing? 'margin-bottom:' . $item_spacing . 'px;':'';
                $format = ' style="%s%s" ';
                $col_space = sprintf($format, $spacing, $gutter);
                $this->col_space = $col_space;
            }
            //single box width and alignment
            if($width || $alignment){
                $return['display'] = $width? 'width:' . $width . ';': '';
                if($alignment == 'right' || $alignment == 'left') {
                    $return['display'] .= 'float:' . $alignment . ';';
                    $return['display'] .= 'margin-top: 5px;';
                    $op = $alignment == 'left'? 'right':'left';
                    $return['display'] .= 'margin-' . $op . ':20px;';
                }
            }
            //single or list box border and bg
            if($item_bg || $item_border){
                $bg = $item_bg? 'background-color:' . $item_bg . ';':'';
                $border = $item_border? 'border:solid 2px ' . $item_border . '':'';
                $format = ' style="%s%s" ';
                $box_string = sprintf($format, $bg, $border);
                $this->box_string = $box_string;
            }
            //expansion button font color and bg
            if($button_font || $button_bg){
                $color = $button_font? 'color:' . $button_font . ';':'';
                $bg = $button_bg? 'background-color:' . $button_bg . ';':'';
                $format = ' style="%s%s" ';
                $button_string = sprintf($format, $bg, $color);
                $this->button_string = $button_string;
            }
            //h3 color size and align
            if($item_font_color || $item_font_size || $item_font_alignment){
                $color = $item_font_color? 'color:' . $item_font_color . ';':'';
                $size = $item_font_size? 'font-size:' . $item_font_size . 'px;':'';
                $align = $item_font_alignment? 'text-align:' . $item_font_alignment . ';':'';
                $format = ' style="%s%s%s" ';
                $h3_string = sprintf($format, $color, $size, $align);
                $this->h3_string = $h3_string;
            }
        }
        return $return;
    }

    /*
     * function jma_youtube_schema_html()
     * returns schema html from $yt_meta_array_items array (see above)
     *
 * */
    function jma_youtube_schema_html($yt_meta_array_items){

        foreach($yt_meta_array_items as $prop => $yt_meta_array_item)
            $return .= '<meta itemprop="' . $prop . '" content="' . str_replace('"', '\'',$yt_meta_array_item)   . '" />';
        return $return;
    }


    protected function error_handler($string){
        switch ($string) {//keyInvalid, playlistNotFound, accessNotConfigured or quotaExceeded, ipRefererBlocked, keyExpired, videoNotFound
            case 'keyInvalid':
            case 'keyExpired':
            $explaination = '<p>keyInvalid or keyExpired:<br/>';
            $explaination .= 'Either the api value is blank or the wrong value has been inserted for the api value see: WordPress Dashboard > Settings > YouTube Playlists with Schema';
            $explaination .= '</p>';
                break;
            case 'accessNotConfigured':
            case 'quotaExceeded':
            $explaination = '<p>accessNotConfigured or quotaExceeded:<br/>';
            $explaination .= 'This generally means that the YouTube Data Api is not enalbed for your Google Project. Try going <a href="https://console.developers.google.com/apis/api/" target="_blank" >here</a> make sure you are in the correct project. Find the api under the Library tab and click "Enable" toward the top of the tab content';
            $explaination .= '</p>';
                break;
            case 'ipRefererBlocked':
                $explaination = '<p>ipRefererBlocked:<br/>';
                $explaination .= 'This generally means that the domain for this website is excluded by restrictions set on the api credentials.  Try going <a href="https://console.developers.google.com/apis/api/" target="_blank" >here</a> make sure you are in the correct project. Find the api key under the Credentials tab and click the edit pencil. A common mistake is "*.domain.com/*" the first dot often needs to be removed if the domain is not on a "www" format.';
                $explaination .= '</p>';
                break;
            case 'playlistNotFound':
            case 'videoNotFound':
            $explaination = '<p>playlistNotFound or videoNotFound:<br/>';
            $explaination .= '<strong>Good News! </strong>Your api code is correct either the video id or plylist id is incorrect';
            $explaination .= '</p>';
                break;
            default:
                $explaination = '<p>Unknown:<br/>';
                $explaination .= 'Unknown error. Make sure the list or video is public. Check value at: WordPress Dashboard > Settings > YouTube Playlists with Schema. Check settings <a href="https://console.developers.google.com/apis/api/" target="_blank" >here</a>.';
                $explaination .= '</p>';
        }
        $return = '<div class="doink-wrap"><h2>doink</h2>';
        $return .= '<p>';
        $return .= 'There are several possible reasons for an error (keyInvalid or keyExpired, accessNotConfigured or quotaExceeded, ipRefererBlocked, playlistNotFound or videoNotFound)';
        $return .= '</p>';
        $return .= '<p>';
        $return .= 'Current error:<strong>' . $string . '</strong>';
        $return .= '</p>';
        $return .= '<p>';
        $return .= $explaination;
        $return .= '</p>';
        $return .= '</div>';

        return $return;
    }

    /*
     * function single_html()
     * @param string $id - the video id
     * @uses $this->box_string $this->h3_string from process_display_atts()
     * returns video box html
     *
    * */
    protected function single_html($id, $list = false){
        global $jmayt_options_array;
        $snippet = JMAYtVideo::video_snippet($id);
        if(is_string($snippet)){
            return JMAYtVideo::error_handler($snippet);
        }else{
        $meta_array = JMAYtVideo::map_meta($snippet, $id);
        $h3_title = $meta_array['name'];
        $elipsis = '';
        if($this->item_font_length == -23  && $jmayt_options_array['item_font_length']){
            $length = $jmayt_options_array['item_font_length'];
        }elseif($this->item_font_length > 0){
            $length = $this->item_font_length;
        }else{
            $length = 0;
        }
        if($length && (strlen($meta_array['name']) > $length)){
            $h3_title = wordwrap($meta_array['name'], $length);
            $h3_title = substr($h3_title, 0, strpos($h3_title, "\n"));
            $elipsis = '&nbsp;...';
        }
        $return .= '<div class="jmayt-item-wrap"' . $this->box_string . '>';
        $return .= '<div class="jmayt-item">';
        $return .= '<div class="jmayt-video-wrap">';
        $return .= '<div class="jma-responsive-wrap" itemprop="video" itemscope itemtype="http://schema.org/VideoObject">';
        $return .= '<button class="jmayt-btn jmayt-sm" ' . $this->button_string . '>&#xe140;</button>';
        $return .= JMAYtVideo::jma_youtube_schema_html($meta_array);
        if(!$list || !$jmayt_options_array['cache_images']){// single video or image caching off
            $return .=  '<iframe src="' . $meta_array['embedURL'] . '?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>';
        }else{
            $overlay = new JMAYtOverlay(array($meta_array['standardUrl'], $meta_array['thumbnailUrl']), $id);
            $image_url = $overlay->get_url();
            $return .= '<button class="jmayt-overlay-button" data-embedid="' . $id . '"><img src="' . $image_url . '"/></button>';
            $return .=  '<div id="video' . $id . '" class="jmayt-hidden-iframe"></div>';
        }
        $return .= '</div><!--jma-responsive-wrap-->';
        $return .= '</div><!--yt-video-wrap-->';
        $return .= '<div class="jmayt-text-wrap">';
        $return .= '<h3 class="jmayt-title" ' . $this->h3_string . '>' . $h3_title . $elipsis . '</h3>';
        $return .= '</div><!--jmayt-text-wrap-->';
        $return .= '</div><!--yt-item-->';
        $return .= '</div><!--yt-item-wrap-->';
        return $return;
        }
    }

    /*
     * function markup() creates transient id, checks fortransient and calls single_html()
     * if needed
     * @global $jmayt_options_array - for cache period
     * returns video html
     *
    * */
    public function markup(){
        global $jmayt_options_array;
        $trans_id = 'jmaytvideo' . $this->id . $this->trans_atts_id;
        $return = get_transient( $trans_id );
        if(false === $return || !$jmayt_options_array['cache']) {//force reset if cache option at 0
            $return = JMAYtVideo::single_html($this->id);
            set_transient( $trans_id, $return, $jmayt_options_array['cache'] );
        }
        return $return;
    }
}
