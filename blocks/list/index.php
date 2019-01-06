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
 function JMA_yt_list_block()
 {
     if (! function_exists('register_block_type')) {
         // Gutenberg is not active.
         return;
     }

     // Scripts.
     wp_register_script(
        'jma-yt-list-block-script', // Handle.
        plugins_url('block.min.js', __FILE__), // Block.js: We register the block here.
        array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor' ), // Dependencies, defined above.
        filemtime(plugin_dir_path(__FILE__) . 'block.min.js'),
        true
    );

     // Here we actually register the block with WP, again using our namespacing.
     // We also specify the editor script to be used in the Gutenberg interface.
     register_block_type('jmayt-list/block', array(
        'attributes'      => array(
            'id' => array(
                'type' => 'string',
            ),
            'className' => array(
                'type' => 'string',
            ),
            'query_max' => array(
                'type' => 'string',
            ),
            'query_offset' => array(
                'type' => 'string',
            ),
            'item_font_length' => array(
                'type' => 'string',
            ),
            'item_gutter' => array(
                'type' => 'string',
            ),
            'item_spacing' => array(
                'type' => 'string',
            ),
            'lg_cols' => array(
                'type' => 'string',
            ),
            'md_cols' => array(
                'type' => 'string',
            ),
            'sm_cols' => array(
                'type' => 'string',
            ),
            'xs_cols' => array(
                'type' => 'string',
            ),
        ),
        'editor_script' => 'jma-yt-list-block-script',
        'render_callback' => 'jma_yt_grid',
    ));
 } // End function JMA_yt_block().

 // Hook: Editor assets.
 add_action('init', 'JMA_yt_list_block');
