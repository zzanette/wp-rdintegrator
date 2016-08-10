<?php
 if ( ! defined( 'ABSPATH' ) ) exit;

	class RD_station_lead extends rdstation_config{
		var $settings = null;

		private $data;
		public $ignore_fields = array();
		public $redirect_success = null;
		public $redirect_error = null;
		public $msg;

	  	private $api_url = "https://www.rdstation.com.br/api/1.3/conversions";

		public function __construct(){
			$this->settings = $this->get();
			
			add_action( 'bp_core_activated_user', array($this,'rd_create_lead_on_bp_activated'), 10, 3 );
			//add_action( 'bp_core_signup_user', array($this,'rd_create_lead_on_bp_signup'), 10, 5);
			
			add_action('profile_update', array($this,'rd_create_lead_without_activation'), 10, 1);
			
			add_action( 'woocommerce_order_status_completed', array($this,'rd_mark_seal_on_order_completed'), 10, 1 );

			
		}

		public function ignore_fields(array $fields){
		    foreach ($this->data as $field => $value) {
		      if(in_array($field, $fields)){
		        unset($this->data[$field]);
		      }
		    }
	  	}

		public function canSaveLead($data){
			$required_fields = array('email', 'token_rdstation');
			foreach ($required_fields as $field) {
			  if(empty($data[$field]) || is_null($data[$field])){
			  	$this->msg = 'Erro: O campo '.$field.' não foi preenchido ou é invalido';
			    return false;
			  }
			}
			return true;
		}

		function createLead() {
			$data_array = $this->data;
			$data_array['token_rdstation'] = $this->settings['public_token'];

			if(empty($data_array["c_utmz"])){
			  $data_array["c_utmz"] = $_COOKIE["__utmz"];
			}

			if(empty($data_array["traffic_source"])){
			  $data_array["traffic_source"] = $_COOKIE["__trf_src"];
			}

			if(empty($data_array["client_id"]) && !empty($_COOKIE["rdtrk"])) {
			  $data_array["client_id"] = json_decode($_COOKIE["rdtrk"])->{'id'};
			}

			if($this->canSaveLead($data_array)){
		        $args = array(
			        'timeout' => 10,
			        'headers' => array('Content-Type' => 'application/json'),
			        'body' => json_encode($data_array)
			    );



			    $response = wp_safe_remote_post( $this->api_url, $args );

			    if (is_wp_error($response)){
			       wp_die('Erro ao enviar o formulário: '. $response);
			       unset($data_array);
			    }
			}
			else{
			  wp_die($this->msg);
			}
		}

		function markSeal(){
			$data_array = $this->data;
			$data_array['token_rdstation'] = $this->settings['private_token'];

			$api_private_url = 'https://www.rdstation.com.br/api/1.2/services/'.$data_array['token_rdstation'].'/generic';

			if($this->canSaveLead($data_array)){
				$args = array(
			        'timeout' => 20,
			        'headers' => array('Content-Type' => 'application/json'),
			        'body' => json_encode($data_array)
			    );

			    $response = wp_safe_remote_post( $api_private_url, $args );

			    if (is_wp_error($response)){
			       wp_die('Erro ao enviar o formulário: '. $response);
			       unset($data_array);
			    }
			}else{
			  wp_die($this->msg);
			}

		}


		function set_data_user_lead_by($by_field, $user_id){

			$user = get_user_by('id', $user_id);
			$this->data['email'] = $user->user_email;
			$this->data['chave-ativacao'] = get_user_meta($user_id, 'activation_key', true);
			$this->data['identificador'] = $this->settings['identifier'];

			switch ($by_field) {
				
				case 'id':
					
			        $this->data['nome'] = $user->display_name;	        

					break;

				case 'buddypress':

			        $this->data['identificador'] = 'Cadastro Becode pre-ativacao';
			        $this->data['nome'] = $user->display_name;	   
					
					break;

				case 'woocommerce':
					
			        $this->data['nome'] = $user->first_name . " " . $user->last_name;	

					break;
				
				default:
					return false;
					break;
			}


		}


		function is_not_user_lead($user_id){
			return update_user_meta( $user_id, 'is_rd_integrate_lead', '1', '1' );
		}


		function rd_create_lead_on_bp_activated( $user_id, $key, $user ) {

			if($this->is_not_user_lead($user_id)){
				$this->set_data_user_lead_by('id', $user_id);
		        // Ignorando campos desnecessários
		        //$rdstation->ignore_fields = array('campo1', 'campo2', 'campo3');

		        // Criando os leads
		        $this->createLead();
	    	}
		}

		function rd_create_lead_on_bp_signup($user_id){
			
			$this->set_data_user_lead_by('buddypress', $user_id);
	        // Criando os leads
	        $this->createLead();	    	
		}

		function rd_create_lead_without_activation($user_id){

			$user = get_user_by('id',$user_id);
			if(empty($user->user_activation_key) && $this->is_not_user_lead($user_id) && !empty($user->user_url)){

					$this->set_data_user_lead_by('id', $user_id);
			        // Ignorando campos desnecessários
			        //$rdstation->ignore_fields = array('campo1', 'campo2', 'campo3');

			        // Criando os leads
			        $this->createLead();
	    	}
		}
		function rd_mark_seal_on_order_completed($order_id){
			 $order = new WC_Order( $order_id );
			 $this->data['status'] = 'won';
			 $this->data['email'] = $order->billing_email;
			 $this->data['value'] = $order->get_total();

			 //Mark the seal
			 $this->markSeal();

		}


	}

