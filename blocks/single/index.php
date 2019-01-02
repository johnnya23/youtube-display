<?php
/**
 * BLOCK: Profile
 *
 * Gutenberg Custom Youtube List Box
 *
 * @since   2.0
 * @package JMA
 */



 defined('ABSPATH') || exit;

 /**
  * Enqueue the block's assets for the editor.
  *
  * `wp-blocks`: Includes block type registration and related functions.
  * `wp-element`: Includes the WordPress Element abstraction for describing the structure of your blocks.
  * `wp-i18n`: To internationalize the block's text.
  *
  * @since 1.0.0
  */
 function JMA_yt_block()
 {
     if (! function_exists('register_block_type')) {
         // Gutenberg is not active.
         return;
     }

     // Scripts.
     wp_register_script(
        'jma-yt-block-script', // Handle.
        plugins_url('block.min.js', __FILE__), // Block.js: We register the block here.
        array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ), // Dependencies, defined above.
        filemtime(plugin_dir_path(__FILE__) . 'block.js'),
        true
    );

     // Here we actually register the block with WP, again using our namespacing.
     // We also specify the editor script to be used in the Gutenberg interface.
     register_block_type('jmayt-single/block', array(
        'attributes'      => array(
            'video_id' => array(
                'type' => 'string',
            ),
            'alignment' => array(
                'type' => 'string',
            ),
            'width' => array(
                'type' => 'string',
            ),
            'start' => array(
                'type' => 'string',
            ),
            'className' => array(
                'type' => 'string',
            ),
        ),
        'editor_script' => 'jma-yt-block-script',
        'render_callback' => 'jma_yt_video',
    ));
 } // End function JMA_yt_block().

 // Hook: Editor assets.
 add_action('init', 'JMA_yt_block');
