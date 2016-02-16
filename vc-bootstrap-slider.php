<?php
/*
Plugin Name: Visual Composer Bootstrap Carousel
Plugin URI: http://www.rafelsanso.com/
Description: Extend Visual Composer with new gallery. Require bootstrap JS
Version: 0.1
Author: Rafel Sansó
Author URI: http://www.rafelsanso.com
License: GPLv2 or later
*/

/*
This example/starter plugin can be used to speed up Visual Composer plugins creation process.
More information can be found here: http://kb.wpbakery.com/index.php?title=Category:Visual_Composer
*/

// don't load directly
if (!defined('ABSPATH')) die('-1');

class VCExtendAddonClass {
    function __construct() {
        // We safely integrate with VC with this hook
        add_action( 'init', array( $this, 'integrateWithVC' ) );

        // Use this when creating a shortcode addon
        add_shortcode( 'bcarousel', array( $this, 'bootstrapCarousel' ) );

        // Register CSS and JS
        add_action( 'wp_enqueue_scripts', array( $this, 'loadCssAndJs' ) );
    }

    public function integrateWithVC() {
        // Check if Visual Composer is installed
        if ( ! defined( 'WPB_VC_VERSION' ) ) {
            // Display notice that Visual Compser is required
            add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
            return;
        }

        /*
        Add your Visual Composer logic here.
        Lets call vc_map function to "register" our custom shortcode within Visual Composer interface.

        More info: http://kb.wpbakery.com/index.php?title=Vc_map
        */
        vc_map( array(
            "name" => __("Bootstrap Carousel", 'vc_extend'),
            "description" => __("Create new Bootstrap's carousel", 'vc_extend'),
            "base" => "bcarousel",
            "class" => "",
            "controls" => "full",
            "icon" => plugins_url('assets/bcarousel-icon.png', __FILE__), // or css class name which you can reffer in your css file later. Example: "vc_extend_my_class"
            "category" => __('Content', 'js_composer'),
            //'admin_enqueue_js' => array(plugins_url('assets/vc_extend.js', __FILE__)), // This will load js file in the VC backend editor
            //'admin_enqueue_css' => array(plugins_url('assets/vc_extend_admin.css', __FILE__)), // This will load css file in the VC backend editor
            "params" => array(
              array(
                "type" => "textfield",
                "heading" => __("Unique identifier:", "vc_cqcarousel_cq"),
                "param_name" => "bctitle",
                "value" => "",
                "description" => __("Leave empty if only one carousel is inserted in the current page", "vc_cqcarousel_cq")
              ),
              array(
                "type" => "attach_images",
                "heading" => __("Images:", "vc_cqcarousel_cq"),
                "param_name" => "images",
                "value" => "",
                "description" => __("Select images from media library.", "vc_cqcarousel_cq")
              )
            )
        ) );
    }

    /*
    Shortcode logic how it should be rendered
    */
    public function bootstrapCarousel( $atts, $content = null ) {
      extract( shortcode_atts( array(
        'bctitle' => 'bcarousel',
        'images' => 'something'
      ), $atts ) );
      $imagesarr = explode(',', $images);

      $args = array(
       'post_type' => 'attachment',
       'numberposts' => -1,
       'post_status' => null,
       'include' => $imagesarr,
       'order' => 'ASC',
       'orderby'   => 'post__in',
       'suppress_filters' => true
      );

      $attachments = get_posts( $args );

      // randomize ID
      if ($bctitle=='') {
        $bctitle .= 'bvc' . rand(1,9);
      }

      $output = '
        <div id="' . $bctitle . '" class="carousel slide" data-ride="carousel">
        <!-- Wrapper for slides -->
          <div class="carousel-inner" role="listbox">';

       if ( $attachments ) {
          $count = 0;
          foreach ( $attachments as $attachment ) {
            $active = '';
            if ($count++ == 0)
            {
              $active = 'active';
            }
            $output .=
              '<div class="item ' . $active . '">
                ' . wp_get_attachment_image( $attachment->ID, 'large' ) . '
                <div class="carousel-caption">
                  ' . '&nbsp;' . '
                </div>
              </div>';
              // apply_filters( 'the_title', $attachment->post_title )
            }
       }

      $output .= '
        </div>
        <!-- Controls -->
        <div class="bcarousel-controls">
          <a class="" href="#' . $bctitle . '" role="button" data-slide="prev">
            <img class="bcarousel-left" alt="Left" src="' . plugins_url('assets/left-icon.png', __FILE__) . '">
          </a>
          <a class="" href="#' . $bctitle . '" role="button" data-slide="next">
            <img class="bcarousel-right" alt="Right" src="' . plugins_url('assets/right-icon.png', __FILE__) . '">
          </a>
        </div>
      </div>';

      return $output;
    }

    /*
    Load plugin css and javascript files which you may need on front end of your site
    */
    public function loadCssAndJs() {
      wp_register_style( 'vc_extend_style', plugins_url('assets/vc_extend.css', __FILE__) );
      wp_enqueue_style( 'vc_extend_style' );

      // If you need any javascript files on front end, here is how you can load them.
      //wp_enqueue_script( 'vc_extend_js', plugins_url('assets/vc_extend.js', __FILE__), array('jquery') );
    }

    /*
    Show notice if your plugin is activated but Visual Composer is not
    */
    public function showVcVersionNotice() {
        $plugin_data = get_plugin_data(__FILE__);
        echo '
        <div class="updated">
          <p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/vcomposer" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend'), $plugin_data['Name']).'</p>
        </div>';
    }
}
// Finally initialize code
new VCExtendAddonClass();