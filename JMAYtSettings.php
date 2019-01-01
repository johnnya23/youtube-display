<?php

if (! defined('ABSPATH')) {
    exit;
}

class JMAYtSettings
{
    private $dir;
    private $file;
    private $assets_url;
    private $settings_base;
    private $settings;
    private $db_option;
    private $page_title;
    private $page_desc;
    private $text_domain;

    public function __construct($args)
    {
        $defaults = array(
            'base' => 'jma_plugin',
            'text_domain' => 'jma_plugin',
            'title' => 'Cool Plugin',
            'settings' => array()
        );
        $args = wp_parse_args($args, $defaults);
        $this->file =  __FILE__ ;
        $this->dir = dirname($this->file);
        $this->assets_url = esc_url(trailingslashit(plugins_url('/', $this->file)));
        $this->settings_base = $args['base'] . '_';
        $this->db_option = $args['db_option']? $args['db_option']:$this->settings_base . 'options_array';
        $this->page_title = $args['title'];
        $this->settings = $args['settings'];
        $this->text_domain = $args['text_domain']? $args['text_domain']: $this->settings_base . 'text_domain';

        // Initialise settings
        add_action('admin_init', array( $this, 'init' ));

        // Register plugin settings
        add_action('admin_init', array( $this, 'register_settings' ));

        // Add settings page to menu
        add_action('admin_menu', array( $this, 'add_menu_item' ));

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename($this->file), array( $this, 'add_settings_link' ));
    }

    /**
     * Initialise settings
     * @return void
     */
    public function init()
    {
        $this->settings = $this->settings_fields();
    }

    /**
     * Add settings page to admin menu
     * @return void
     */
    public function add_menu_item()
    {
        $page = add_options_page(__($this->page_title, $this->text_domain), __($this->page_title, $this->text_domain), 'manage_options', $this->settings_base . 'settings', array( $this, 'settings_page' ));
        add_action('admin_print_styles-' . $page, array( $this, 'settings_assets' ));
    }

    /**
     * Load settings JS & CSS
     * @return void
     */
    public function settings_assets()
    {
        wp_enqueue_style('spectrum_style', plugin_dir_url(__FILE__) . '/spectrum.css');
        wp_enqueue_script('spectrum_script', plugin_dir_url(__FILE__) . '/spectrum.js');

        // We're including the WP media scripts here because they're needed for the image upload field
        // If you're not including an image upload then you can leave this function call out
        wp_enqueue_media();

        wp_register_script($this->settings_base . 'admin-js', $this->assets_url . 'settings.js', array( 'spectrum_script', 'jquery' ), '1.0.0');
        wp_enqueue_script($this->settings_base . 'admin-js');
    }

    /**
     * Add settings link to plugin list table
     * @param  array $links Existing links
     * @return array 		Modified links
     */
    public function add_settings_link($links)
    {
        $settings_link = '<a href="options-general.php?page=' . $this->settings_base . 'settings">' . __('Settings', $this->text_domain) . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

    /**
     * Build settings fields
     * @return array Fields to be displayed on settings page
     */
    private function settings_fields()
    {
        $this->settings = apply_filters($this->settings_base . 'settings_fields', $this->settings);

        return $this->settings;
    }

    /**
     * Register plugin settings
     * @return void
     */
    public function register_settings()
    {
        if (is_array($this->settings)) {
            $option_name = $this->db_option;

            foreach ($this->settings as $section => $data) {

                // Add section to page
                add_settings_section($section, $data['title'], array( $this, 'settings_section' ), $this->settings_base . 'settings');

                foreach ($data['fields'] as $field) {

                    // Validation callback for field
                    $validation = '';
                    if (isset($field['callback'])) {
                        $validation = $field['callback'];
                    }

                    // Register field

                    register_setting($this->settings_base . 'settings', $option_name, $validation);

                    // Add field to page
                    add_settings_field($field['id'], $field['label'], array( $this, 'display_field' ), $this->settings_base . 'settings', $section, array( 'field' => $field ));
                }
            }
        }
    }

    public function settings_section($section)
    {
        $html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
        echo $html;
    }

    /**
     * Generate HTML for displaying fields
     * @param  array $args Field data
     * @return void
     */
    public function display_field($args)
    {
        $field = $args['field'];

        $html = '';
        $option = null;
        $option_array_name = $field['id'];
        $option_name = $this->db_option . '[' . $field['id'] . ']';
        $option_array = get_option($this->db_option);
        if (is_array($option_array)) {
            $option = $option_array[$option_array_name];
        }

        $data = '';
        if (isset($field['default'])) {
            $data = $field['default'];
            if ($option !== null) {
                $data = $option;
            }
        }
        $placeholder = array_key_exists('placeholder', $field)? $field['placeholder']:'';

        switch ($field['type']) {
            case 'text':
            case 'password':
            case 'number':
                $style = esc_attr($field['id']) == 'api'? ' style="width: 350px; max-width: 100%" ': '';
                $html .= '<input id="' . esc_attr($field['id']) . '"' . $style . 'type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($placeholder) . '" value="' . $data . '"/>' . "\n";
                break;
            case 'submit':/* THIS IS JUST HARDCODED TO ENABLE BUTTON PLACEMENT WITHIN FORM */
                $html .= '<p><input type="submit" name="submit" id="submit" class="button button-primary" value="Clear All Images (THIS IS NOT A SAVE BUTTON)"
form="jmaty_clear"  /></p>';
                break;

            case 'text_secret':
                $html .= '<input id="' . esc_attr($field['id']) . '" type="text" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($placeholder) . '" value=""/>' . "\n";
                break;

            case 'textarea':
                $html .= '<textarea id="' . esc_attr($field['id']) . '" rows="5" cols="50" name="' . esc_attr($option_name) . '" placeholder="' . esc_attr($placeholder) . '">' . $data . '</textarea><br/>'. "\n";
                break;

            case 'checkbox':
                $checked = '';
                if ($option && 'on' == $option) {
                    $checked = 'checked="checked"';
                }
                $html .= '<input id="' . esc_attr($field['id']) . '" type="' . $field['type'] . '" name="' . esc_attr($option_name) . '" ' . $checked . '/>' . "\n";
                break;

            case 'checkbox_multi':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if (in_array($k, $data)) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="checkbox" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '[]" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
                }
                break;

            case 'radio':
                foreach ($field['options'] as $k => $v) {
                    $checked = false;
                    if ($k == $data) {
                        $checked = true;
                    }
                    $html .= '<label for="' . esc_attr($field['id'] . '_' . $k) . '"><input type="radio" ' . checked($checked, true, false) . ' name="' . esc_attr($option_name) . '" value="' . esc_attr($k) . '" id="' . esc_attr($field['id'] . '_' . $k) . '" /> ' . $v . '</label> ';
                }
                break;

            case 'select':
                $html .= '<select name="' . esc_attr($option_name) . '" id="' . esc_attr($field['id']) . '">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if ($k == $data) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '">' . $v . '</option>';
                }
                $html .= '</select> ';
                break;

            case 'select_multi':
                $html .= '<select name="' . esc_attr($option_name) . '[]" id="' . esc_attr($field['id']) . '" multiple="multiple">';
                foreach ($field['options'] as $k => $v) {
                    $selected = false;
                    if (in_array($k, $data)) {
                        $selected = true;
                    }
                    $html .= '<option ' . selected($selected, true, false) . ' value="' . esc_attr($k) . '" />' . $v . '</label> ';
                }
                $html .= '</select> ';
                break;

            case 'image':
                $image_thumb = '';
                if ($data) {
                    $image_thumb = wp_get_attachment_thumb_url($data);
                }
                $html .= '<img id="' . $option_array_name . '_preview" class="image_preview" src="' . $image_thumb . '" /><br/>' . "\n";
                $html .= '<input id="' . $option_array_name . '_button" type="button" data-uploader_title="' . __('Upload an image', $this->text_domain) . '" data-uploader_button_text="' . __('Use image', $this->text_domain) . '" class="image_upload_button button" value="'. __('Upload new image', $this->text_domain) . '" />' . "\n";
                $html .= '<input id="' . $option_array_name . '_delete" type="button" class="image_delete_button button" value="'. __('Remove image', $this->text_domain) . '" />' . "\n";
                $html .= '<input id="' . $option_array_name . '" class="image_data_field" type="hidden" name="' . $option_name . '" value="' . $data . '"/><br/>' . "\n";
                break;

            case 'color':
                $html .= '<div class="color-picker" style="position:relative;">';
                $html .= '<input type="text" name=" ' . esc_attr__($option_name) . ' " class="eyecon-picker" value="' . esc_html($data) . '" />';
                $html .= '</div>';
                break;

        }

        switch ($field['type']) {

            case 'checkbox_multi':
            case 'radio':
            case 'select_multi':
                $html .= '<br/><span class="description">' . $field['description'] . '</span>';
                break;

            default:
                $html .= '<label for="' . esc_attr($field['id']) . '"><span class="description">' . $field['description'] . '</span></label>' . "\n";
                break;
        }

        echo $html;
    }

    /**
     * Validate individual settings field
     * @param  string $data Inputted value
     * @return string       Validated value
     */
    public function validate_field($data)
    {
        if ($data && strlen($data) > 0 && $data != '') {
            $data = urlencode(strtolower(str_replace(' ', '-', $data)));
        }
        return $data;
    }

    /**
     * Load settings page content
     * @return void
     */
    public function settings_page()
    {

        // Build page HTML
        $html = '<div class="wrap" id="' . $this->settings_base . 'settings">' . "\n";
        $html .= '<h2>' . __($this->page_title . ' Settings', $this->text_domain) . '</h2>' . "\n";
        $html .= '<form id="jmaty_clear" action="' . admin_url("admin-post.php") . '">';
        $html .= '<input type="hidden" name="action" value="jmayt_clear_function">';
        $html .= '</form>';

        $html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

        $html .= '<div class="clear"></div>' . "\n";

        // Get settings fields
        ob_start();
        settings_fields($this->settings_base . 'settings');
        do_settings_sections($this->settings_base . 'settings');
        $html .= ob_get_clean();

        $html .= '<p class="submit">' . "\n";
        $html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr(__('Save Settings', $this->text_domain)) . '" />' . "\n";
        $html .= '</p>' . "\n";
        $html .= '</form>' . "\n";
        $html .= '</div>' . "\n";

        echo $html;
    }
}
