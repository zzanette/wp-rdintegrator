<?php
 if ( ! defined( 'ABSPATH' ) ) exit;
class rd_station_integrate_settings extends rdstation_config{

	var $settings;

	public function __construct(){
		add_options_page(__('Rd Station Integrate settings','rd-station-integrate'),__('Rd Station Integrate','rd-station-integrate'),'manage_options','rd-station-integrate',array($this,'settings'));
		add_action('admin_enqueue_scripts',array($this,'enqueue_admin_scripts'));
		$this->settings=$this->get(); 
	}

	function enqueue_admin_scripts($hook){
		if ( 'settings_page_rd-station-integrate' != $hook ) {
        	return;
    	}
    	wp_enqueue_style( 'rd_station_integrate_admin_style', plugin_dir_url( __FILE__ ) . '../assets/css/admin.css' );
    	wp_enqueue_script( 'rd_station_integrate_admin_style', plugin_dir_url( __FILE__ ) . '../assets/js/admin.js',array('jquery'),'1.0',true);
	}

	function settings(){
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
		$this->settings_tabs($tab);
		$this->$tab();
	}

	function settings_tabs( $current = 'general' ) {
	    $tabs = array( 
	    		'general' => __('General','rd-station-integrate'),
	    		);
	    echo '<div id="icon-themes" class="icon32"><br></div>';
	    echo '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
	        echo "<a class='nav-tab$class' href='?page=rd-station-integrate&tab=$tab'>$name</a>";

	    }
	    echo '</h2>';
	    if(isset($_POST['save'])){
	    	$this->save();
	    }
	}

	function general(){
		echo '<h3>Rd Station Integrate Settings</h3>';
	
		$settings=array(
				array(
					'label' => 'Identificação da Conversão',
					'name' =>'identifier',
					'type' => 'text',
					'std'=> '',
					'desc' => 'Identificação da origem da conversão que irá para o RD'
				),
				array(
					'label' => 'Token Publico',
					'name' =>'public_token',
					'type' => 'text',
					'std' => '',
					'desc' => 'Token de integração público'
				),
				array(
					'label' => 'Token Privado',
					'name' =>'private_token',
					'type' => 'text',
					'std'=> '',
					'desc' => 'Token de integração privado'
				),
			);

		$this->generate_form('general',$settings);
	}



	function generate_form($tab,$settings=array()){
		echo '<form method="post">
				<table class="form-table">';
		wp_nonce_field('save_settings','_wpnonce');   
		echo '<ul class="save-settings">';

		foreach($settings as $setting ){
			echo '<tr valign="top">';
			switch($setting['type']){
				case 'textarea': 
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><textarea name="'.$setting['name'].'">'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'</textarea>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'select':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><select name="'.$setting['name'].'" class="chzn-select">';
					foreach($setting['options'] as $key=>$option){
						echo '<option value="'.$key.'" '.(isset($this->settings[$setting['name']])?selected($key,$this->settings[$setting['name']]):'').'>'.$option.'</option>';
					}
					echo '</select>';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'checkbox':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="checkbox" name="'.$setting['name'].'" '.(isset($this->settings[$setting['name']])?'CHECKED':'').' />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'number':
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="number" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:'').'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
				case 'hidden':
					echo '<input type="hidden" name="'.$setting['name'].'" value="1"/>';
				break;
				default:
					echo '<th scope="row" class="titledesc">'.$setting['label'].'</th>';
					echo '<td class="forminp"><input type="text" name="'.$setting['name'].'" value="'.(isset($this->settings[$setting['name']])?$this->settings[$setting['name']]:(isset($setting['std'])?$setting['std']:'')).'" />';
					echo '<span>'.$setting['desc'].'</span></td>';
				break;
			}
			
			echo '</tr>';
		}
		echo '</tbody>
		</table>';
		echo '<input type="submit" name="save" value="Save Settings" class="button button-primary" /></form>';
	}


	function save(){
		$none = $_POST['save_settings'];
		if ( !isset($_POST['save']) || !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'],'save_settings') ){
		    echo '<h1>Security check Failed. Contact Administrator.</h1>';
		    die();
		}
		unset($_POST['_wpnonce']);
		unset($_POST['_wp_http_referer']);
		unset($_POST['save']);

		foreach($_POST as $key => $value){
			$this->settings[$key]=$value;
		}

		$this->put($this->settings);
	}
}

add_action('admin_menu','init_rd_station_settings_settings',100);
function init_rd_station_settings_settings(){
	new rd_station_integrate_settings;	
}
