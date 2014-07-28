<?php
/**
 * @package   movable-content-editor
 * @author    Peter Elmered <peter@elmered.com>
 * @license   GPL-2.0+
 * @link      http://extendwp.com
 * @copyright 2013 Peter Elmered
 *
 * @wordpress-plugin
 * Plugin Name: Movable Content Editor
 * Plugin URI:  
 * Description: Move the content WYSIWYG editor as a meta box.
 * Version:     0.1.2
 * Author:      Peter Elmered
 * Author URI:  http://elmered.com
 * Text Domain: woocommerce-pricefiles
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

class Movable_Content_Editor
{
    private $plugin_slug = 'movable-content-editor';
    private $plugin_path = null;
    private $plugin_url = null;
    
    private $plugin_options = array();

    /**
     * Instance of this class.
     *
     * @since    0.1.0
     * @var      object
     */
    protected static $instance = null;

    const VERSION = '0.1.2';

    function __construct()
    {
        
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugins_url('', __FILE__) . '/';

        $this->plugin_options = get_option($this->plugin_slug . '_options', array());

        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));

        add_action('current_screen', array($this, 'init'));

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

        add_action('admin_menu', array($this, 'add_plugin_menu'));
        add_action('admin_init', array($this, 'initialize_admin_options'));
        
    }
    
    function init()
    {
        $screen = get_current_screen();
        
        $selected_post_types = $this->get_selected_post_types();
        
        if($screen->base == 'post' && in_array($screen->post_type, $selected_post_types))
        {
            add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 0);

            // Load admin style sheet and JavaScript.
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_editor_styles'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_editor_scripts'));
        }
        else if( $screen->base == 'settings_page_movable-content-editor' )
        {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_options_styles'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_options_scripts'));
        }
    }
    
    function action_links($links)
    {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page='.$this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>',
            //'<a href="http://extendwp.com/">' . __( 'Docs', $this->plugin_slug ) . '</a>',
            //'<a href="http://extendwp.com/">' . __('Info & Support', $this->plugin_slug) . '</a>',
        );

        return array_merge($plugin_links, $links);
    }

    /**
     * Returns a singleton instance
     * 
     * @return Object instance
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }
    
    function add_meta_boxes()
    {
        foreach($this->plugin_options['use_on_post_type'] AS $type)
        {
            if( isset($this->plugin_options['editor_headers_for_post_types'][$type]) && !empty($this->plugin_options['editor_headers_for_post_types'][$type]) )
            {
                $box_title = __($this->plugin_options['editor_headers_for_post_types'][$type]);
            }
            else
            {
                $box_title = __('Post Content', $this->plugin_slug);
            }
            
            add_meta_box(
                //'product_long_description', 
                //__('Product long description', $this->plugin_slug), 
                'custom_post_content_box', 
                $box_title, 
                array($this, 'empty_box'), 
                $type, 'normal', 'high'
            );
        }
                
    }

    //Render empty box (callback from add_meta_box())
    function empty_box($post){}
    
    
    /**
     * Load the plugin text domain for translation.
     *
     * @since    0.1.0
     */
    public function load_plugin_textdomain()
    {
        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue styles and scrips for admin pages
     */
    function enqueue_admin_editor_styles()
    {
        wp_enqueue_style($this->plugin_slug . '-admin-editor', $this->plugin_url . 'assets/admin.css', array(), self::VERSION);
    }
    function enqueue_admin_editor_scripts()
    {
        wp_enqueue_script($this->plugin_slug . '-admin-editor', $this->plugin_url . 'assets/admin.js', array('jquery'), self::VERSION);
        
        $options = array( 
            'editorId' => 'custom_post_content_box',
        );
        wp_localize_script( $this->plugin_slug . '-admin-editor', 'movableContentEditorOptions', $options );
    }
    
    function enqueue_admin_options_styles()
    {
        wp_enqueue_style($this->plugin_slug . '-admin-options', $this->plugin_url . 'assets/admin-options.css', array(), self::VERSION);
    }
    function enqueue_admin_options_scripts()
    {
        wp_enqueue_script($this->plugin_slug . '-admin-options', $this->plugin_url . 'assets/admin-options.js', array('jquery'), self::VERSION);
    }
    
    
    /**
     * ADMIN PAGES
     */
    function add_plugin_menu()
    {
        add_submenu_page(
            'options-general.php', __('Movable Content Editor', $this->plugin_slug), __('Movable Content Editor', $this->plugin_slug), 'manage_options', $this->plugin_slug, array($this, 'display_settings_page')
        );
    }
    
    function display_settings_page()
    {
        echo '<form method="post" action="options.php">';
        
        settings_fields($this->plugin_slug . '_options');
        do_settings_sections($this->plugin_slug . '_options_section');

        submit_button();
        
        echo '</form>';
        
        do_settings_sections($this->plugin_slug . '_donate_section');
    }
    
    function get_selected_post_types()
    {
        return (empty($this->plugin_options['use_on_post_type']) ? array() : $this->plugin_options['use_on_post_type'] );
    }

    function initialize_admin_options()
    {
        register_setting(
            $this->plugin_slug . '_options', $this->plugin_slug . '_options', array($this, 'validate_input')
        );
        
        add_settings_section(
            $this->plugin_slug . '_options', 
            __('Movable content editor options', $this->plugin_slug), 
            array($this, 'section_header_callback'), 
            $this->plugin_slug . '_options_section'
        );
        
        add_settings_field(
            'use_on_post_type', 
            __('Use on', $this->plugin_slug), 
            array($this, 'use_on_post_types_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'description' => __('Select the the post types that you want to use this plugin on.', $this->plugin_slug),
            )
        );
        
        add_settings_field(
            'editor_headers_for_post_types', 
            __('Editor headers', $this->plugin_slug), 
            array($this, 'editor_headers_for_post_types_callback'), 
            $this->plugin_slug . '_options_section', 
            $this->plugin_slug . '_options', 
            array(
                'description' => sprintf(__('Specify header text for content editor box. Leave empty for default (%s)', $this->plugin_slug), __('Post Content', $this->plugin_slug)),
            )
        );
        
        add_settings_section(
            $this->plugin_slug . '_donate', 
            __('Donate', $this->plugin_slug), 
            array($this, 'donation_button'), 
            $this->plugin_slug . '_donate_section'
        );
        
        
    }
    
    function section_header_callback()
    {
        
    }
    
    function use_on_post_types_callback($args)
    {
        global $_wp_post_type_features;
        
        $post_types = get_post_types();
        
        $selected_post_types = $this->get_selected_post_types();
        
        unset($post_types['nav_menu_item']);
        
        if ($post_types)
        {
            echo '<div id="movable-content-editor-specify-post-types">';
            foreach ($post_types as $slug => $name) {
                //Only show the post types that have content editor
                if(isset($_wp_post_type_features[$slug]['editor']) && $_wp_post_type_features[$slug]['editor'] == 1)
                {
                    echo '<label class="post-type"> ';
                    echo '<span>' . ucfirst(esc_html($name)) . '</span>';
                    echo '<input type="checkbox" name="' . $this->plugin_slug . '_options[use_on_post_type][]" value="'.$slug.'"' . (in_array($slug, $selected_post_types) ? 'checked="checked"' : '') . '/>';
                    echo '</label>';
                }
            }
            echo '</div>';
        }
        
        echo '<p style="clear:both">' . $args['description'] . '</p>';
    }
    
    
    function editor_headers_for_post_types_callback($args)
    {
        echo '<p style="clear:both">' . $args['description'] . '</p>';
        
        echo '<div id="movable-content-editor-specify-titles">';
        
        if(isset($this->plugin_options['editor_headers_for_post_types']) && !empty($this->plugin_options['editor_headers_for_post_types']))
        {
            foreach( $this->plugin_options['editor_headers_for_post_types'] AS $post_type => $title )
            {
                echo '<label class="post-type"> <span>'.ucfirst($post_type).'</span>';
                echo '<input type="text" name="movable-content-editor_options[editor_headers_for_post_types]['.$post_type.']" value="'.$title.'">';
                echo '</label>';
            }
        }
        
        echo '</div>';
        
        print_r($this->plugin_options);
    }
    
    function donation_button()
    {
        ?>
        <p><?php _e('If you like or find this plugin useful, please consider donating something.'); ?></p>

        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
        <input type="hidden" name="cmd" value="_s-xclick">
        <input type="hidden" name="hosted_button_id" value="VTTTUBC92YU4E">
        <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
        <img alt="" border="0" src="https://www.paypalobjects.com/sv_SE/i/scr/pixel.gif" width="1" height="1">
        </form>
        <?php
    }
    
    function validate_input($input) 
    {
        if (!is_array($input))
            return false;

        $output = $input;

        //Apply filter_input on all values
        array_walk_recursive($output, array($this, 'filter_input'));

        // Return the array processing any additional functions filtered by this action
        return apply_filters($this->plugin_slug . '_validate_input', $output, $input);
    }

    function filter_input(&$input) 
    {
        $input = strip_tags(stripslashes($input));
    }
}

if(is_admin())
{
    Movable_Content_Editor::get_instance();
}
