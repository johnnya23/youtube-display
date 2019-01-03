<?php
class JMAYtStyles
{
    public function __construct()
    {
    }

    //helper function for jmayt_styles()
    protected static function output($inputs)
    {
        $output = array();
        foreach ($inputs as $input) {
            $numArgs = count($input);
            if ($numArgs < 2) {
                return;
            }	//bounces input if no property => value pairs are present
            $pairs = array();
            for ($i = 1; $i < $numArgs; $i++) {
                $x = $input[$i];
                if (count($x)) {
                    $pairs[] = array(
                    'property' => $x[0],
                    'value' => $x[1]
                );
                }
            }
            $add = array($input[0] => $pairs);
            $output = array_merge_recursive($output, $add);
        }
        return $output;
    }

    //helper function for jmayt_styles()
    // media queries in format max(or min)-$width@$selector, .....
    // so we explode around @, then around - (first checking to see if @ symbol is present)
    protected static function build_css($css_values)
    {
        $return = ' {}';
        foreach ($css_values as  $k => $css_value) {
            $has_media_query = (strpos($k, '@'));
            if ($has_media_query) {
                $exploded = explode('@', $k);
                $media_query_array = explode('-', $exploded[0]);
                $k = $exploded[1];

                $return .= '@media (' . $media_query_array[0] . '-width:' . $media_query_array[1] . "px) {\n";
            }
            $return .= $k . "{\n";
            foreach ($css_value as $value) {
                if ($value['value']) {
                    $return .= $value['property'] . ': ' . $value['value'] . ";\n";
                }
            }
            $return .= "}\n";
            if ($has_media_query) {
                $return .= "}\n";
            }
        }
        return $return;
    }

    /**
     * function jmayt_styles add the plugin specific styles
     * @return $css the css string
     */
    public static function styles($jmayt_options_array)
    {
        //global $jmayt_options_array;
        $item_gutter = floor($jmayt_options_array['item_gutter'] / 2);
        // FORMAT FOR INPUT
        // $jmayt_styles[] = array($selector, array($property, $value)[,array($property, $value)...])

        //in format above format media queries  i.e. max-768@$selector, ...
        // $jmayt_styles[] = array(max(or min)-$width@$selector, array($property, $value)[,array($property, $value)...])
        $jmayt_styles[10] = array('div.jmayt-list-wrap',
            array('clear', 'both'),
            array('margin-left', -$item_gutter . 'px'),
            array('margin-right', -$item_gutter . 'px'),
        );
        $jmayt_styles[20] = array('div.jmayt-item-wrap',
            array('position', 'relative')
        );
        $jmayt_styles[30] = array('div.jmayt-list-item',
            array('min-height', '1px'),
            array('padding-left', $item_gutter . 'px'),
            array('padding-right', $item_gutter . 'px'),
            array('margin-bottom', $jmayt_options_array['item_spacing'] . 'px'),
        );
        if ($jmayt_options_array['item_border'] || $jmayt_options_array['item_bg']) {
            $border_array = $jmayt_options_array['item_border'] ? array('border', 'solid 2px ' . $jmayt_options_array['item_border']) :
                array();
            $bg_array = $jmayt_options_array['item_bg'] ? array('background', $jmayt_options_array['item_bg']) : array();
            $jmayt_styles[50] = array('div.jmayt-item-wrap',
                $border_array,
                $bg_array
            );
        }
        $font_size = $lg_font_size = $jmayt_options_array['item_font_size'];
        if ($font_size) {
            $font_size = ceil($font_size * 0.7);
        }
        $font_size_str = $jmayt_options_array['item_font_size'] ? array('font-size', $font_size . 'px')
            : array();
        $lg_font_size_str = $jmayt_options_array['item_font_size'] ? array('font-size', $lg_font_size . 'px')
            : array();
        $jmayt_styles[60] = array('.jmayt-item h3.jmayt-title',
            array('padding', '10px'),
            array('margin', ' 0'),
            array('line-height', '120%'),
            array('color', $jmayt_options_array['item_font_color']),
            array('text-align', $jmayt_options_array['item_font_alignment']),
            $font_size_str
        );
        $jmayt_styles[70] = array('.jmayt-item h3.jmayt-title:first-line',
            $lg_font_size_str
        );
        $jmayt_styles[80] = array('button.jmayt-btn, button.jmayt-btn:focus',
            array('position', 'absolute'),
            array('z-index', '10'),
            array('top', ' 0'),
            array('left', ' 0'),
            array('padding', '7px 10px'),
            array('font-size', '24px'),
            array('font-family', 'Glyphicons Halflings'),
            array('color', $jmayt_options_array['button_font']),
            array('background', $jmayt_options_array['button_bg']),
            array('border', 'solid 1px ' . $jmayt_options_array['button_font']),
            array('cursor', 'pointer'),
            array('-webkit-transition', 'all .2s'),
            array('transition', 'all .2s'),
        );
        $jmayt_styles[90] = array('button.jmayt-btn:hover',
            array('color', $jmayt_options_array['button_bg']),
            array('background', $jmayt_options_array['button_font']),
        );

        $jmayt_values = JMAYtStyles::output($jmayt_styles);
        /* create html output from  $jma_css_values */


        $jmayt_css = JMAYtStyles::build_css($jmayt_values);
        $css = '
    .jmayt-outer, .jmayt-outer * {
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    }
    .jmayt-outer p, .jmayt-outer br, .jmayt-list-wrap p, .jmayt-list-wrap br {
        margin:0;
        padding:0;
    }
    .doink-wrap p {
        display: block!important;
    }

    .jmayt-col-xs-020{width:20%}@media (min-width:768px){.jmayt-col-sm-020{width:20%}}@media (min-width:992px){.jmayt-col-md-020{width:20%}}@media (min-width:1200px){.jmayt-col-lg-020{width:20%}}
    .clearfix:before, .clearfix:after {
        zoom:1;
        display: table;
        content: "";
    }
    .clearfix:after {
        clear: both
    }
    .jmayt-video-wrap .jma-responsive-wrap iframe,
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button{
    	position: absolute;
    	top: 0;
    	left: 0;
    	width: 100%;
    	height: 100%;
    }
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button {
        z-index:9;
        display: block;
        padding: 0;
    	top: -16.66%;
    	height: 133.33%;
    	border-width: 0;
    }
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button.jmayt-ready:after {
        z-index:12;
        background: rgba(0,0,0,0.7);
        content: "";
    	position: absolute;
        height: 30px;
        width: 40px;
        display: block;
    	top: 50%;
    	left: 50%;
    	transform: translate(-50%, -50%);
    	border-radius: 8px;
    }
    @media(max-width: 992px){
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button.jmayt-ready:after {
        background: rgba(238,0,0,0.8);
    }}
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button:hover.jmayt-ready:after {
        background: rgba(238,0,0,0.6);
    }
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button.jmayt-ready:before {
        z-index:14;
        content: "";
    	position: absolute;
    	top: 50%;
    	left: 50%;
    	transform: translate(-50%, -50%);
    	width: 0;
        height: 0;
        border-style: solid;
        border-width: 5px 0 5px 12px;
        border-color: transparent transparent transparent #ffffff;
    }
    .jmayt-video-wrap .jma-responsive-wrap .jmayt-overlay-button img {
        width: 100%;
    }
    .jmayt-video-wrap {
        padding-bottom: 56.25%;
        position: relative;
        z-index: 1;
    }
    .jmayt-text-wrap {
        position: relative;
    }
    .jmayt-list-wrap, .jmayt-single-item {
        margin-bottom: 20px
    }
    .jmayt-list-wrap .jmayt-text-wrap h3.jmayt-title {
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
        width: 100%;
    }
    .jmayt-video-wrap .jma-responsive-wrap {
    	padding-bottom: 56.25%;
    	overflow: hidden;
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
        width: 100%;
        transition: all 0.3s;
    }
    .jmayt-fixed {
        background: rgba(0,0,0,0.8);
        position: absolute;
        top: 0;
        left: 0;
    }
    .jmayt-list-wrap .xs-break {
        clear: both
    }
    @media(max-width: 767px){
        button.jmayt-btn, button.jmayt-btn:focus {
            font-size: 30px;
        }
    }
    @media(min-width: 767px){
        .has-sm .xs-break {
            clear: none
        }
        .jmayt-list-wrap .sm-break {
            clear: both
        }
    }
    @media(min-width: 991px){
        .has-md .sm-break, .has-md .xs-break {
            clear: none
        }
        .jmayt-list-wrap .md-break {
            clear: both
        }
    }
    @media(min-width: 1200px){
        .has-lg .md-break, .has-lg .sm-break, .has-lg .xs-break {
            clear: none
        }
        .jmayt-list-wrap .lg-break {
            clear: both
        }
    }
    }' . $jmayt_css;
        return $css;
    }
}
