<?php
/*
Plugin Name: Responsive YouTube Videos and Playlists with Schema
Plugin URI: https://cleansupersites.com/jma-youtube-playlists-with-schema/
Description: Makes available shortcode for embed of single videos and grids from YouTube video playlists, which include schema.org markup as recommended by google.
Version: 2.0
Author: John Antonacci
Author URI: http://cleansupersites.com
License: GPL2
*/
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Define global constants.
 *
 * @since 1.0.0
 */
// Plugin version.
if (! defined('JMAYT_VERSION')) {
    define('JMAYT_VERSION', '2.0');
}

if (! defined('JMAYT_NAME')) {
    define('JMAYT_NAME', trim(dirname(plugin_basename(__FILE__)), '/'));
}

if (! defined('JMAYT_DIR')) {
    define('JMAYT_DIR', plugin_dir_path(__FILE__));
}

if (! defined('JMAYT_URL')) {
    define('JMAYT_URL', plugin_dir_url(__FILE__));
}

/**
 * BLOCK: Profile Block.
 */
require_once(JMAYT_DIR . 'block/single/index.php');
require_once(JMAYT_DIR . 'block/list/index.php');

/*
 * function jma_yt_quicktags
 * add shortcode tags to text toolbar
 *

 * */
add_filter('widget_text', 'do_shortcode');
function jmayt_quicktags()
{
    if (wp_script_is('quicktags')) {
        ?>
        <script language="javascript" type="text/javascript">
            QTags.addButton( 'JMA_yt_wrap', 'yt_wrap', '[yt_video_wrap width="100%" alignment="none"]', '[/yt_video_wrap]' );
            QTags.addButton( 'JMA_yt_video', 'yt_video', '[yt_video video_id="yt_video_id" width="100%" alignment="none"]' );

            QTags.addButton( 'JMA_yt_grid', 'yt_grid', '[yt_grid yt_list_id="yt_list_id"]' );
        </script>
    <?php
    }
}
add_action('admin_print_footer_scripts', 'jmayt_quicktags');


wp_register_style('jmayt_bootstrap_css', plugins_url('/jmayt_bootstrap.css', __FILE__));
wp_register_script('jmayt_api', 'https://www.youtube.com/player_api', array( 'jquery' ));
wp_register_script('jmayt_js', plugins_url('/jmayt_js.js', __FILE__), array( 'jquery', 'jmayt_api' ));

function jmayt_scripts()
{
    wp_enqueue_style('jmayt_bootstrap_css');
    wp_enqueue_script('jmayt_api');
    wp_enqueue_script('jmayt_js');
    $custom_css = jmayt_styles();
    wp_add_inline_style('jmayt_bootstrap_css', $custom_css);
}
add_action('enqueue_block_editor_assets', 'jmayt_scripts');

function jmayt_template_redirect()
{
    global $jmayt_options_array;
    if (jmayt_detect_shortcode(array('yt_grid', 'yt_video', 'yt_video_wrap', 'jmayt-single/block', 'jmayt-list/block')) || $jmayt_options_array['uni']) {
        add_action('wp_enqueue_scripts', 'jmayt_scripts');
    }
}
add_action('template_redirect', 'jmayt_template_redirect');


/**
 * function jmayt_detect_shortcode Detect shortcodes in a post object,
 *  from a post id or from global $post.
 * @param string or array $needle - the shortcode(s) and block(s) to search for
 * use array for multiple values
 * @param int or object $post_item - the post to search (defaults to current)
 * @return boolean $return
 */
function jmayt_detect_shortcode($needle = '', $post_item = 0)
{
    if ($post_item) {
        if (is_object($post_item)) {
            $post = $post_item;
        } else {
            $post = get_post($post_item);
        }
    } else {
        global $post;
    }
    $pattern = get_shortcode_regex();

    preg_match_all('/'. $pattern .'/s', $post->post_content, $matches);

    //if shortcode(s) to be searched for were passed and not found $return false
    if (count($matches[2])) {
        $return = array_intersect($needle, $matches[2]);
    } elseif (has_blocks($post->post_content)) {
        foreach (parse_blocks($post->post_content) as $block) {
            $blocknames[] = $block['blockName'];
        }
        $return = array_intersect($needle, $blocknames);
    }
    return apply_filters('jmayt_detect_shortcode_result', $return, $post, $needle);
}

//helper function for jmayt_styles()
function jmayt_output($inputs)
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
            $pairs[] = array(
                'property' => $x[0],
                'value' => $x[1]
            );
        }
        $add = array($input[0] => $pairs);
        $output = array_merge_recursive($output, $add);
    }
    return $output;
}

//helper function for jmayt_styles()
// media queries in format max(or min)-$width@$selector, .....
// so we explode around @, then around - (first checking to see if @ symbol is present)
function jmayt_build_css($css_values)
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
$jmayt_db_option = 'jmayt_options_array';
$jmayt_options_array = get_option($jmayt_db_option);
$jmayt_api_code = $jmayt_options_array['api'];

spl_autoload_register('jma_yt_autoloader');
function jma_yt_autoloader($class_name)
{
    if (false !== strpos($class_name, 'JMAYt')) {
        $classes_dir = realpath(plugin_dir_path(__FILE__));
        $class_file = $class_name . '.php';
        require_once $classes_dir . DIRECTORY_SEPARATOR . $class_file;
    }
}

function jmayt_clear_cache()
{
    global $wpdb;
    global $jmayt_db_option;
    $jmayt_options_array = get_option($jmayt_db_option);
    $plugin_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_jmaytvideo%' OR option_name LIKE '_transient_timeout_jmaytvideo%'");
    foreach ($plugin_options as $option) {
        delete_option($option->option_name);
    }
    if ($jmayt_options_array['cache_images']) {
        jmayt_on_activation_wc();
    } else {
        $files = glob(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'overlays' . DIRECTORY_SEPARATOR . '*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file);
            } // delete file
        }
        jmayt_on_deactivation_dc();
    }
}
add_action('update_option_' . $jmayt_db_option, 'jmayt_clear_cache');

function jmayt_clear_function()
{
    global $wpdb;
    $files = glob(realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'overlays' . DIRECTORY_SEPARATOR . '*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file);
        } // delete file
    }
    $plugin_options = $wpdb->get_results("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_jmaytvideo%' OR option_name LIKE '_transient_timeout_jmaytvideo%'");
    foreach ($plugin_options as $option) {
        delete_option($option->option_name);
    }
    die(header('Location:' . admin_url('options-general.php?page=jmayt_settings')));
}
add_action('admin_post_jmayt_clear_function', 'jmayt_clear_function');

/**
 * Build settings fields
 * @return array Fields to be displayed on settings page
 */
$col_array = array( 0 => 'inherit', 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6);
$xs_col = array(  1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6);
$settings = array(
    /*
     * start of a new section
     * */

    'setup' => array(
        'title'					=> __('Setup', 'jmayt_textdomain'),
        'description'			=> __('Setup options.', 'jmayt_textdomain'),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'api',
                'label'			=> __('YouTube Api value', 'jmayt_textdomain'),
                'description'	=> __('Api credentials for youtube <a target="_blank" href="https://console.developers.google.com/projectselector/apis/dashboard?organizationId=0">here</a>.', 'jmayt_textdomain'),
                'type'			=> 'text',
                'default'		=> ''
            ),
            array(
                'id' 			=> 'cache',
                'label'			=> __('Cache Time', 'jmayt_textdomain'),
                'description'	=> __('Frequency of checks back to YouTube for info. Larger number for quicker page loads and to avoid hitting YouTube Api limits (3600 = 1 hr or 0 for testing only).', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> '3600'
            ),
            array(
                'id' 			=> 'uni',
                'label'			=> __('Universal Mode', 'jmayt_textdomain'),
                'description'	=> __('Load plugin script on all pages - not just ones where the plugin shortcode is detected. This is necessary if shortcode is used in a sidebar (widget area) or may be necessary with some themes or page builders', 'jmayt_textdomain'),
                'type'			=> 'radio',
                'options'		=> array( 0 => 'Limited' , 1 => 'Universal'),
                'default'		=> 0
            ),
            array(
                'id' 			=> 'dev',
                'label'			=> __('Dev Mode', 'jmayt_textdomain'),
                'description'	=> __('Dev may allow plugin to function on Windows localhost (Use Production in production for security)', 'jmayt_textdomain'),
                'type'			=> 'radio',
                'options'		=> array( 0 => 'Production' , 1 => 'Dev'),
                'default'		=> 0
            ),
            array(
                'id' 			=> 'cache_images',
                'label'			=> __('Cache Images for lists', 'jmayt_textdomain'),
                'description'	=> __('<span style="color:red">This option pulls thumbnail images from YouTube and stores them in the plugin for faster display of long lists. ACTIVATING THIS OPTION CAUSES THE PLUGIN TO TRY TO REWRITE .HTACCESS TO INCREASE MAX PAGE EXECUTION TIME TO 5 MINUTES. The first time a page with a large list loads the plugin will copy the YouTube thumbnail images dynamically. This means the first page load will be very slow. Thereafter the page will load thumbnails from the plugin folder (much faster). THIS OPTION MAY NOT WORK CORRECTLY DEPENDING ON YOUR HOSTING ENVIRONMENT (you can always switch back to conventional loading)</span>', 'jmayt_textdomain'),
                'type'			=> 'radio',
                'options'		=> array( 0 => 'Don\'t cache', 1 => 'Cache images'),
                'default'		=> 0
            ),
            array(
                'id' 			=> 'clear_images',
                'label'			=> __('Clear Images for lists', 'jmayt_textdomain'),
                'description'	=> __('Clear all images. This is the only way to get renewed images from YouTube. After clearing (or toggling the cache option above) you may want to load pages with long YouTube Lists as the first load will take a long time.', 'jmayt_textdomain'),
                'type'			=> 'submit'/* submit is one time only hardcoded placehoder */
            )
        )
    ),
    /*
     * start of a new section
     * */
    'display' => array(
        'title'					=> __('YouTube Display Options', 'jmayt_textdomain'),
        'description'			=> __('These are some default display settings (they can be overridden with shortcode parameters which are shown in parens)', 'jmayt_textdomain'),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'item_font_color',
                'label'			=> __('Font color for YouTube item titles', 'jmayt_textdomain'),
                'description'	=> __('Null your theme\'s title color (item_font_color)', 'jmayt_textdomain'),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_font_size',
                'label'			=> __('Font size for YouTube item titles', 'jmayt_textdomain'),
                'description'	=> __('0 your theme\'s title size (item_font_size)', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_font_alignment',
                'label'			=> __('Font alignment for YouTube item titles', 'jmayt_textdomain'),
                'description'	=> __('(item_font_alignment)', 'jmayt_textdomain'),
                'type'			=> 'radio',
                'options'		=> array( 'left' => 'left' , 'center' => 'center', 'right' => 'right'),
                'default'		=> 'left'
            ),
            array(
                'id' 			=> 'item_font_length',
                'label'			=> __('The maximun number of characters for YouTube item titles', 'jmayt_textdomain'),
                'description'	=> __('0 for whole title (item_font_length)', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_bg',
                'label'			=> __('Background color for YouTube items', 'jmayt_textdomain'),
                'description'	=> __('Null for no bg (item_bg)', 'jmayt_textdomain'),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'item_border',
                'label'			=> __('Border color for YouTube items', 'jmayt_textdomain'),
                'description'	=> __('Null for no border (item_border)', 'jmayt_textdomain'),
                'type'			=> 'color',
                'default'		=> 0
            ),
            array(
                'id' 			=> 'button_font',
                'class'         => 'picker',
                'label'			=> __('Button arrow color', 'jmayt_textdomain'),
                'description'	=> __('for expansion buttons on upper left of YouTube items (button_font)', 'jmayt_textdomain'),
                'type'			=> 'color',
                'default'		=> '#21759B'
            ),
            array(
                'id' 			=> 'button_bg',
                'label'			=> __('Button background color', 'jmayt_textdomain'),
                'description'	=> __('for expansion buttons on upper leftof YouTube items (button_bg)', 'jmayt_textdomain'),
                'type'			=> 'color',
                'default'		=> '#cbe0e9'
            )
        )
    ),

     /*
     * start of a new section
     * */
    'grid' => array(
        'title'					=> __('YouTube Grid Options', 'jmayt_textdomain'),
        'description'			=> __('These are some grid specific settings (they can be overridden with shortcode parameters which are shown in parens)', 'jmayt_textdomain'),

        /*
         * fields for this section section
         * */
        'fields'				=> array(
            array(
                'id' 			=> 'query_max',
                'label'			=> __('The maximun number of entries to show in the grid', 'jmayt_textdomain'),
                'description'	=> __('0 for all (query_max) - (query_offset) in for shortcode offset', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> 50
            ),
            array(
                'id' 			=> 'item_gutter',
                'label'			=> __('Grid horizontal spacing', 'jmayt_textdomain'),
                'description'	=> __('in px between YouTube grid items - best results even number between 0 and 30 (item_gutter)', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> '30'
            ),
            array(
                'id' 			=> 'item_spacing',
                'label'			=> __('Grid vertical spacing', 'jmayt_textdomain'),
                'description'	=> __('in px between YouTube grid items (item_spacing)', 'jmayt_textdomain'),
                'type'			=> 'number',
                'default'		=> '15'
            ),
            array(
                'id' 			=> 'lg_cols',
                'label'			=> __('Large device columns (lg_cols)', 'jmayt_textdomain'),
                'description'	=> __('For window width 1200+ px (inherit uses value from setting below).', 'jmayt_textdomain'),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> 0
            ),
            array(
                'id' 			=> 'md_cols',
                'label'			=> __('Medium device columns (md_cols)', 'jmayt_textdomain'),
                'description'	=> __('For window width 992+ px (inherit uses value from setting below).', 'jmayt_textdomain'),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> 0
            ),
            array(
                'id' 			=> 'sm_cols',
                'label'			=> __('Small device columns (sm_cols)', 'jmayt_textdomain'),
                'description'	=> __('For window width 768+ px (inherit uses value from setting below).', 'jmayt_textdomain'),
                'type'			=> 'select',
                'options'		=> $col_array,
                'default'		=> '3'
            ),
            array(
                'id' 			=> 'xs_cols',
                'label'			=> __('Extra small device columns (xs_cols)', 'jmayt_textdomain'),
                'description'	=> __('For window width -768 px.', 'jmayt_textdomain'),
                'type'			=> 'select',
                'options'		=> $xs_col,
                'default'		=> '2'
            )
        )
    )
);



if (is_admin()) {
    $jma_settings_page = new JMAYtSettings(
        array(
            'base' => 'jmayt',
            'title' => 'YouTube Playlists with Schema',
            'db_option' => $jmayt_db_option,
            'settings' => $settings)
        );
}

/**
 * function jmayt_styles add the plugin specific styles
 * @return $css the css string
 */
function jmayt_styles()
{
    global $jmayt_options_array;
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

    $jmayt_values = jmayt_output($jmayt_styles);
    /* create html output from  $jma_css_values */


    $jmayt_css = jmayt_build_css($jmayt_values);
    $css = '
.jmayt-outer, .jmayt-outer * {
-webkit-box-sizing: border-box;
-moz-box-sizing: border-box;
box-sizing: border-box;
}
.jmayt-outer p, .jmayt-outer br, .jmayt-list-wrap p, .jmayt-list-wrap br {
    display: none;
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
}/*
.jmayt-video-wrap.jmayt-fixed .jma-responsive-wrap {
    padding-bottom: 45%;
    width: 80%;
}*/
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

function jmayt_sanitize_array($inputs)
{
    if (is_array($inputs) && count($inputs)) {
        foreach ($inputs as $i => $input) {
            $i = sanitize_text_field($i);
            $input = sanitize_text_field($input);
            $output[$i] =  $input;
        }
    } else {
        $output = array();
    }
    return $output;
}

/**
 * function jma_yt_grid shortcode for the grid
 * @param array $atts - the shortcode attributes
 * @return the shortcode string
 */
function jma_yt_grid($atts)
{
    global $jmayt_options_array;
    $jmayt_api_code = $jmayt_options_array['api'];
    $atts = jmayt_sanitize_array($atts);
    if (!isset($atts['query_max'])) {
        $atts['query_max'] = 0;
    }
    if (!$atts['yt_list_id']) {
        return 'Enter a list id';
    }
    if (isset($atts['className'])) {
        $atts['class'] = $atts['className'];
    }

    $you_tube_list = new JMAYtList($atts['yt_list_id'], $jmayt_api_code);
    //processing plugin options - form array of column atts and set defaults
    foreach ($jmayt_options_array as $i => $option) {
        if ((strpos($i, '_cols') !== false) && $option) {
            $i = str_replace('_cols', '', $i);
            $has_break .= ' has-' . $i;
            $responsive_cols[$i] = $option;
        }
    }
    $count = 0;
    $style = $you_tube_list->process_display_atts($atts);
    foreach ($atts as $index => $att) {
        if (strpos($index, '_cols') !== false) {
            //processing shortcode attributes - clear defaults the first time we find a _cols attribute
            if (!$count) {
                $responsive_cols = array();
                $has_break = '';
            }
            $count++;
            $index = str_replace('_cols', '', $index);
            $has_break .= ' has-' . $index;
            $responsive_cols[$index] = $att;
        }
    }
    $max = 10000;
    $offset = 0;
    if ($jmayt_options_array['query_max'] > 0) {
        $max = $jmayt_options_array['query_max'];
    }

    if (isset($atts['query_max']) && $atts['query_max'] > 0) {
        $max = $atts['query_max'];
    }

    if (isset($atts['query_offset'])) {
        $offset = $atts['query_offset'];
    }

    ob_start();
    $attributes = array(
        'id' => $atts['id'],
        'class' => $atts['class'] . $has_break . ' jmayt-list-wrap clearfix',
        'style' => $style['gutter'] . $style['display'] . $atts['style']
    );
    echo '<div ';
    foreach ($attributes as $name => $attribute) {//build opening div ala html shortcode
        if ($attribute) {// check to make sure the attribute exists
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $you_tube_list->markup($responsive_cols, $offset, $max);
    echo '</div><!--yt-list-wrap-->';
    $x = ob_get_contents();
    ob_end_clean();

    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_grid', 'jma_yt_grid');



/**
 * get YouTube video ID from URL
 *
 * @param string $url
 * @return string YouTube video id or FALSE if none found.
 */
function jmayt_id_from_url($url)
{
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        $id = $match[1];
        return $id;
    }
    return false;
}

/**
 * function jma_yt_video_wrap_html shortcode for the grid
 * @param array $atts - the shortcode attributes
 * @param string $video_id - the video id (either previously extracted from $atts or from content
 * (depending on whether its the wrap shortcode)
 * @return the shortcode string
 */
function jma_yt_video_wrap_html($atts, $video_id)
{
    global $jmayt_api_code;
    $atts = jmayt_sanitize_array($atts);
    $yt_video = new JMAYtVideo(sanitize_text_field($video_id), $jmayt_api_code);
    $style = $yt_video->process_display_atts($atts);
    $attributes = array(
        'id' => $atts['id'],
        'class' => $atts['class'] . ' jmayt-outer jmayt-single-item clearfix',
        'style' => $style['display'] . $atts['style']
    );
    echo '<div ';
    foreach ($attributes as $name => $attribute) {
        if ($attribute) {
            echo $name . '="' . $attribute . '" ';
        }
    }
    echo '>';
    echo $yt_video->markup();
    echo '</div><!--jmayt-item-wrap-->';
}

/**
 * @param $atts
 * @uses jma_yt_video_wrap_html
 * @return mixed
 */
function jma_yt_video($atts)
{
    $video_id = $atts['video_id'];
    if (!$video_id) {
        return 'please enter a video id';
    }
    if (isset($atts['className'])) {
        $atts['class'] = $atts['className'];
    }
    ob_start();
    jma_yt_video_wrap_html($atts, $video_id);
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video', 'jma_yt_video');

/**
 * @param $atts
 * @param null $content
 * @uses jma_yt_video_wrap_html
 * @return mixed
 */
function jma_yt_video_wrap($atts, $content = null)
{
    $video_id = jmayt_id_from_url($content);
    ob_start();
    jma_yt_video_wrap_html($atts, $video_id);
    $x = ob_get_contents();
    ob_end_clean();
    return str_replace("\r\n", '', $x);
}
add_shortcode('yt_video_wrap', 'jma_yt_video_wrap');



function jmayt_on_activation_wc()
{
    $jmayttxt = "
	# WP Maximum Execution Time Exceeded
	<IfModule mod_php5.c>
		php_value max_execution_time 300
	</IfModule>";

    $htaccess = get_home_path().'.htaccess';
    $contents = @file_get_contents($htaccess);
    if (!strpos($htaccess, $jmayttxt)) {
        file_put_contents($htaccess, $contents.$jmayttxt);
    }
}



/* On deactivation delete code (dc) from htaccess file */

function jmayt_on_deactivation_dc()
{
    $jmayttxt = "
	# WP Maximum Execution Time Exceeded
	<IfModule mod_php5.c>
		php_value max_execution_time 300
	</IfModule>";

    $htaccess = get_home_path().'.htaccess';
    $contents = @file_get_contents($htaccess);
    file_put_contents($htaccess, str_replace($jmayttxt, '', $contents));
}


//register_activation_hook(   __FILE__, 'jmayt_on_activation_wc' );

register_deactivation_hook(__FILE__, 'jmayt_on_deactivation_dc');
