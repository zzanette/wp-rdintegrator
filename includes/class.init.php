<?php
 if ( ! defined( 'ABSPATH' ) ) exit;
class init_rd_station_integrate extends rdstation_config{

	var $settings;

	public function __construct(){
		$this->settings = $this->get();
		new RD_station_lead;

	}	


}

add_action('init','initialise_rd_station_integrate');
function initialise_rd_station_integrate(){
	new init_rd_station_integrate;	
}

