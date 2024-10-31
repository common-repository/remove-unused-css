<?php
/**
Plugin Name: Remove Unused CSS
Plugin URI: http://codingmall.com/wordpress/
Description: This plugin removes unused css from your website pages automatically.
Author: CodingMall.com
Author URI: https://codingmall.com/wordpress/
Version: 1.1.6
License: GPLv2 (or later)
Text Domain: cm-cmuxcss
Domain Path: /languages/
**/

/*  Copyright CodingMall.com  (email : support@codingmall.com)
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See our terms for details.
*/

if ( ! defined( 'ABSPATH' ) ) { 
    exit;
} // Exit if accessed directly

$obj = new cmcmuxcss_idx(); 
$obj->initMe();


class cmcmuxcss_idx{
	public function initMe(){
		$this->plugNameShortEng= 'Remove Unused CSS';
		$this->plugNameLongEng = 'Remove Unused CSS for WordPress and WooCommerce';
		$this->plugNameDscrEng = 'This plugin automatically removes unused CSS from your website pages.';
		
		$this->plugName 		= 'remove-unused-css';
		$this->param_pfx		= 'cmuxcss_';

		$this->plugPath 		= plugin_dir_path( __FILE__ );
		$this->pluginUrlPath	= plugin_dir_url( __FILE__ );
		
		$st = $this->param_pfx . 'adm_settings';
		$this->paramData 		= get_option( $st );
		//var_dump($this->paramData['is_back_color']); die();
		
		$this->params			= new cmcmuxcss_params($this);		
		
		$this->isWpRocket=false;
		//x if (file_exists(ABSPATH . '/wp-content/cache/wp-rocket/')){
		$tmp = get_option('active_plugins');	//var_dump($tmp);die();
		foreach($tmp as $t){
			if (stripos($t,'wp-rocket.php')!==false){ 
				$this->isWpRocket=true;
			}
		}

		require_once ($this->plugPath . 'helpers/helper.php');
		$this->objHelper = new cmcmuxcss_GeneHelper($this);
		
		$this->initAFE();
	}
	
	private function initAFE(){
		if (is_admin()){
			require_once( __DIR__ ."/admin.php" );
			$objAdm = new cmcmuxcss_admin();
			$objAdm->initMe($this);
			$this->objAdm = $objAdm;
			
		}else{
			require_once( __DIR__ ."/front.php" );
			$objF = new cmcmuxcss_front();
			$objF->initMe($this);
			$this->objF = $objF;
		}		
	}
}


class cmcmuxcss_params{
	public $idxObj=null;
	
	public function __construct(& $idxObj){
		$this->idxObj = $idxObj;
	}
	
	public function get($name, $defa){
		$v = @$this->idxObj->paramData[$name];
		if ($v=='') $v=$defa;
		
		return $v;
	}
}

