<?php
class cmcmuxcss_admin{
	var $idxObj=null;
	
	public function initMe(&$idx){
		$this->idxObj = $idx;

		$nm = 'plugin_action_links_'.$this->idxObj->plugName.'/index.php';	//1
		add_filter( $nm, array($this,'admin_links') );						//2

		add_action( 'admin_menu', array($this,'admin_menu') );				//3
		add_action( 'admin_enqueue_scripts', array($this,'load_scripts_admin') );	//4
		
		add_action( 'admin_init', array($this,'register_settings') );		//5
	}

	public function admin_menu() {
		add_options_page($this->idxObj->plugNameLongEng, $this->idxObj->plugNameShortEng, 'manage_options'
				, $this->idxObj->plugName, array($this,'show_settings_form') );
	} 
	
	public function register_settings(){
		register_setting( $this->idxObj->param_pfx.'adm_settings_group', $this->idxObj->param_pfx
				.'adm_settings' );
	}
	
	public function admin_links( $links ){
		$links[]= '<a href="'. get_admin_url(null, 'options-general.php?page='
						. $this->idxObj->plugName ) .'">'.'Settings'.'</a>';

		$links[]= '<a href="'. get_site_url() .'/?cmuxcc=1">'.'Clear Cache'.'</a>';
		
		$tmp = $this->idxObj->objHelper->getQuotaLink();
		$links[]=$tmp[0];
						
		//array_merge($links,$a);
		return $links;
	}	

	function load_scripts_admin($hook) {
		if ( 'settings_page_'.$this->idxObj->plugName != $hook ) {
			return;
		}

		//wp_enqueue_style( 'wp-color-picker' );

		//wp_enqueue_script( 'cmux-admin-js', $this->idxObj->pluginUrlPath . 'admin/main.js'
		//		, array('wp-color-picker'), false, true );
	}
	
	public function echoEsc($str){
		//x echo $str;
		$opsArr = ['type'  => [],'name' => [],'id' => [],'placeholder' => [],'class' => [],'value' => []
					,'title'  => [],'style'  => [],'checked'  => [],'disabled'  => []];
		$opsArr2= ['id' => [],'class' => [],'title'  => [],'style'  => []];
		
		$allowed_html = [
			'a'      	=> ['href'  => [],'title' => [],'style' => []],
			'br'     	=> [],
			'label'     => $opsArr2,
			'table'     => $opsArr2,
			'th' 		=> $opsArr2,
			'tr' 		=> $opsArr2,
			'td' 		=> $opsArr2,
			'textarea' 	=> $opsArr,
			'input' 	=> $opsArr,
			'select'	=> $opsArr,
			'option' 	=> $opsArr,
			'div' 		=> $opsArr2,
			'span' 		=> $opsArr2,
			'fieldset' 	=> $opsArr2,
			'h2' 		=> $opsArr2,
			'h3' 		=> $opsArr2,
			'p' 		=> $opsArr2,
		];
		echo wp_kses( $str, $allowed_html );
	}
	
	public function show_settings_form(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$oh = $this->idxObj->objHelper;
		$st = $this->idxObj->param_pfx . 'adm_settings';
		$options = $this->idxObj->paramData;//get_option( $st );
		
		$this->echoEsc ( $oh->settingsHeader($this->idxObj->plugNameShortEng.' - Settings'
				,$this->idxObj->plugNameDscrEng) );
			
		?>
		<form method="post" action="options.php" enctype="multipart/form-data" >
			<?php settings_fields($st.'_group'); ?>
			
			<table class="form-table">
				<?php 
					$field_ops = array();	
					$field_ops['href'] = get_site_url() .'/?cmuxcc=1';
					$field_ops['capt'] = 'Clear Cache';
					
					$this->echoEsc ($oh->showField2('','',null,null,null,'link',$field_ops) );
					
					//
					$field_ops = array();	$tmp=$oh->getQuotaLink();
					$field_ops['html']=$tmp[0];
					
					$qDesc = 'If you see a red colored "No Quota" status here, it means your free account quota has been expired. The pages optimized earlier are saved in the plugin cache and will continue to work with the optimized css. The pages which are not yet optimized, will not get the optimized and reduced css and continue to work with your full css. Click the link to get more quota.';
					//if ($tmp[1]==1) $qDesc='';
					
					$this->echoEsc ($oh->showField2('service_quota','Quota Status',$qDesc,null,null,'html',$field_ops) );
					
					$this->echoEsc ($oh->showField2('css_extra_selectors','Extra selectors to include'
						,'Sometimes, when this plugin removes unused CSS, it may also remove some important selectors like classes for hover effects, hidden menus, hidden side options divs etc. If you are not seeing some important element on screen after the CSS removal operation, you may need to include your missing selectors here. After saving this form, clear the cache and check your pages again. You may need to load your page twice to see the effect.'
						,null,@$options['css_extra_selectors'],'textarea',null,'',' placeholder="hover,menu,hidden,sticky,open,close,show" ') );					

					$this->echoEsc ($oh->showField2('fallback_mode','Fallback Mode'
						,'Fallback mode is slower. Only use it if you see some error in page layouts.',null,@$options['fallback_mode'],'yesno') );
					
					$this->echoEsc ($oh->showSubmit());
				?>
			</table>
		</form>
		</div>
		<?php
	}
}


