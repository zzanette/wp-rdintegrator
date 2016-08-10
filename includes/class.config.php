<?php
 if ( ! defined( 'ABSPATH' ) ) exit;
abstract class rdstation_config{

  var $version = '1.5';
  var $option = 'rd_station_integrate';
  var $security_key = 'rd_station_security';
  /*var $social_options = array(
    'facebook',
    'google',
    'twitter',
    'linkedin'
    );*/
  function get_version(){
    return $this->version;
  }
  /*
  function get_social_options(){
    return $social_options;
  }
  */
  function get(){
    return get_option($this->option);
  }

  function put($value){
    update_option($this->option,$value);
  }

  function get_current_url(){
      global $post;
    if ( is_front_page() ) :
      $page_url = home_url();
      else :
      $page_url = 'http';
    if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" )
      $page_url .= "s";
        $page_url .= "://";
        if ( $_SERVER["SERVER_PORT"] != "80" )
      $page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        else
      $page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
      endif;
      
    return esc_url( $page_url );
  }
  

  /** 
   * recursively create a long directory path
   */
  function createPath($path) {
      if (is_dir($path)) return true;
      $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
      $return = createPath($prev_path);
      return ($return && is_writable($prev_path)) ? mkdir($path) : false;
  }

}