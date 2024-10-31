<?php
class cmcmuxcss_front{
	var $idxObj=null;
	var $cfName='';
	var $cfReady=false;
	var $myUrl='';
	var $isHome=false;
	var $isProdDtl=0;
	
	public function initMe(&$idx){
		$this->idxObj = $idx;
		
		add_action('wp_loaded', array($this, 'buffer_start'));
		add_action('shutdown',  array($this, 'buffer_end'  ));
		add_action('plugins_loaded', array($this,'pluginsLoaded'));
		
		add_action( 'woocommerce_before_single_product', array($this,'beforeSingleProdWoo'));
		
		$this->setCfName();		
	}
	
	public function buffer_start() { ob_start(array($this, 'modifContentFe')); }
	public function buffer_end() { 
		@ob_end_flush(); 
		
		$this->backToCmux();
	}

	function beforeSingleProdWoo() {
		global $post; 
		$this->isProdDtl=$post->ID;
		//header('prod---id:'.$this->isProdDtl);
	}	

	public function pluginsLoaded(){		
		$this->handleCssRequest();
		$this->clearCache();
	}
	
	private function rrmdir($dir,$n=1){
	   if (is_dir($dir)) { 
		//die($dir);
		 $objects = scandir($dir);
		 foreach ($objects as $object) { 
		   if ($object != "." && $object != "..") { 
			 if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
			   $this->rrmdir($dir. DIRECTORY_SEPARATOR .$object);
			 else
			   unlink($dir. DIRECTORY_SEPARATOR .$object); 
		   } 
		 }
		 if ($n==1) rmdir($dir); 
	   }
	}
	
	private function countInProc(){
		$dir = $this->cachePath .'*.inproc';
		$files = glob($dir);
		if ($files){
			return count($files);
		}
		return 0;
	}
	
	private function backToCmux(){
		$cmux_wpr = (int) @$_GET['cmux_wpr'];		if ($cmux_wpr!=1) return;
		$u= sanitize_text_field( @$_SERVER['HTTP_REFERER'] );
		if ($u!=''){
			header('location: '.$u);
		}
	}
	
	private function clearCache(){
		$cmuxcc = (int) @$_GET['cmuxcc'];		if ($cmuxcc!=1) return;
		
		$this->rrmdir($this->cachePath,0);
		
		$u = get_site_url() .'/wp-admin/plugins.php'; //die($u);
		$rf= sanitize_text_field(@$_SERVER['HTTP_REFERER']);		if ($rf!='') $u=$rf;
		
		if ($this->idxObj->isWpRocket){
			$nonce = wp_create_nonce('purge_cache_all');
			$u = trim(get_site_url(),'/') .'/wp-admin/admin-post.php?action=purge_cache&type=all&cmux_wpr=1&_wpnonce='.$nonce;
		}
		header('Location: '.$u);
		die();
	}
	
	private function handleCssRequest(){		
		$crtcmux = (int) @$_GET['crtcmux'];		if ($crtcmux!=1) return;

		if (substr($this->myUrl,0,7)=='http://'){
			$ux = str_replace('http://','https://',$this->myUrl);
			header('Location: '.$ux);
			die();
		}

		$qtFn = $this->idxObj->plugPath . '/cache/nqt.txt'; 
		if (file_exists($qtFn)) @unlink($qtFn);
		
		//if ($this->countInProc()>=4) $this->dieG('-50:cccntex');
		
		$ism=(int) @$_REQUEST['ism'];

		if ($this->cfName=='') $this->dieG('-100:nocf');
		
		$bn = $this->pageMd5;
		
		$tm = @filemtime($this->cfName);
		if ($ism==1) $tm = @filemtime($this->cfNameMob);
			
		if ($tm>0){
			//if ($tm+86400 > time()) $this->dieG('-200:cchvld:'. $bn); 
			$this->dieG('-200:cchvld:'. $bn); 
		}
		
		if ( file_exists($this->inProc) ){
			if ( (@filemtime($this->inProc) + 180) < time() ){
				@unlink($this->inProc);
			}else{
				//$this->dieG('-250:cchiproc:'. $bn); 
			}
		}

		//file_put_contents($this->inProc,'1');
				
		$u = $this->myUrl;	$uO=$u;
		
		$sign='&'; $slash='';
		if (strpos($u,'?')===false){
			$slash='/';	$sign='?';
		}
		//if ($this->idxObj->isWpRocket) $u.= $slash. $sign.'nowprocket';
		
		$host = @$_SERVER['HTTP_HOST']; 

		$exs = trim($this->idxObj->params->get('css_extra_selectors',''));
		$exs = str_replace(',','|',$exs);
		$exs = str_replace(' ','',$exs);
		if ($exs!='') $exs='&exs='.$exs;
		
		$tmx = str_replace('.','-',microtime(true));
		$url = 'http://api.everlive.net/?gmcmux=99&tmx='. $tmx .'&ism='.$ism. $exs. '&clh='.$host.'&u='. urlencode( $u );

		//x $this->removeWprocketCache();
		
		set_time_limit(600);
		
		$wpRes = wp_remote_get( $url, [ 'sslverify' => false ] );
		
		$responseHeaders = $wpRes['headers'];
		$css			 = $wpRes['body'];
		
		$css = trim($css);
		
		if ($css==''){
			@unlink($this->inProc);
			//var_dump($responseHeaders);
			$err = @$responseHeaders['cmx-cmuxerr'];
			
			if (substr($err,0,5)=='-200|'){
				file_put_contents($qtFn,'1');
			}
			$this->dieG('-300:ncss:'.$err);
		}
		
		//$css=str_replace(array("\n","\t"),array("",""),$css); 
		
		//$css2 = explode('~~~!!@@~~~',$css);
		//file_put_contents($this->cfName,	$css2[0]);
		//file_put_contents($this->cfNameMob,	$css2[1]);
		if ($ism==0){
			file_put_contents($this->cfName,	$css);
		}else{
			file_put_contents($this->cfNameMob,	$css);
		}
		
		$this->removeWprocketCache();
		@unlink($this->inProc);
		
		if ($this->idxObj->isWpRocket){
			header('Location: '.$uO); 
		}
		$this->dieG('1');
	}
	
	private function removeWprocketCache(){
		if (!$this->idxObj->isWpRocket) return;
		
		$host = @$_SERVER['HTTP_HOST'];
		$cfPath = __DIR__ . '/../../cache/wp-rocket/'. $host .'/';
		$wprCfName = str_replace($this->protocol .$host,'',$this->myUrl);
		$wprCfName = $cfPath . trim($wprCfName,'/').'/';
		
		$arr = array('index-https.html','index-https.html_gzip','index-mobile-https.html','index-mobile-https.html_gzip');
		foreach($arr as $a){
			$fn = $wprCfName . $a;	$fn = str_replace('//','/',$fn);
			
			@unlink($fn);
		}
		//die();
	}
	
	private function burstCache(){
		header("Cache-Control: max-age=0",true);
		header("Cache-Control: private, no-cache, no-store, must-revalidate",true);
		header("Expires: Mon, 22 May 2000 22:41:29 GMT",true);
		header("Content-Type: text/html; charset=utf-8",true);
		header("Pragma: no-cache",true);
	}
	
	private function dieG($str=''){
		$this->burstCache();
		header('cmux__crtres: '.$str);
		
		@ob_end_flush();flush();
		
		echo esc_html($str);
		die();		exit();
	}
	
	public function modifContentFe($content){
		//x if (!$this->cfReady) return $content;
		$tmp = (int) @$_SERVER['HTTP_CMX_PPCMUX']; //var_dump($_SERVER); die('1');
		if ($tmp==1){
			$js = $this->pollCrtCmuxJs();
			$content = str_replace('</body>',$js.'</body>',$content); 				
			return $content;
		}
		
		$uid = get_current_user_id();		if ($uid>0) return $content;
		
		$this->debugje('modif_Page: 100');
		
		$content = $this->modifyCss($content); 
		
		return $content;		
	}
	
	private function cssLoadingJs(){
		$js='
		 <script>
			var cmux__glo_toutDone=0;
		
			window.addEventListener("load", function(event) {
				setTimeout(function(){ 
					cmux__spdr_loadRest();
				},1);
			});
			
			function cmux__spdr_loadRest(){
				if (cmux__glo_toutDone==1) return;
				
				cmux__glo_toutDone=1;
				var scp=document.querySelectorAll("[cmux__data_href]"); var lg=scp.length;
				var bSw=0;
				for(i=0;i<lg;i++){
					var u=scp[i].getAttribute("cmux__data_href");
					scp[i].href = u;
				}
			}
		 </script>
		';
		return $js;
	}
	
	private function getOnlyUsedCssUrl(){
		if (wp_is_mobile()){
			$css = $this->cfNameMobUrl;
		}else{
			$css = $this->cfNameUrl;
		}
		return $css;
	}
	
	private function getUsedCss(){
		if (wp_is_mobile()){
			$css = @file_get_contents($this->cfNameMob);
		}else{
			$css = @file_get_contents($this->cfName);
		}
		return $css;
	}
	
	private function makeDir($path){
		if (!file_exists($path)) @mkdir($path);
	}
	
	private function setCfName(){
		$path = __DIR__ . '/cache/';				$pathUrl  = $this->idxObj->pluginUrlPath . 'cache/';
		$path2= ABSPATH . 'wp-content/cache/';		$pathUrl2 =  trim(get_option('siteUrl'),'/') . '/wp-content/cache/';
		//die($path2);
		if (file_exists($path2)){
			$path 	 = $path2 	 . $this->idxObj->plugName .'/';	$this->makeDir($path);
			$pathUrl = $pathUrl2 . $this->idxObj->plugName .'/';
		}
		
/* 		$themeName = wp_get_theme()->get( 'Name' );
		$themeName = str_replace(' ','_',$themeName);
		$themeName = esc_html($themeName);
		
		$path 	  .= $themeName . '/'; 						$this->makeDir($path);
		$pathUrl.= urlencode($themeName) . '/'; */
		
		$this->cachePath = $path;
		
		$tmp = $this->thisUrl();	//die($tmp.'!');		
		$this->myUrl = $tmp;	$this->infoHeader('u',$tmp);
		
		$tmp = md5($tmp);		$this->infoHeader('mcd',$tmp);	
		$this->pageMd5 = $tmp;	
		
		$siteUrl = get_option('siteurl');
		if ($siteUrl==$this->myUrl) $this->isHome=true;
		//if ($this->isHome) die($siteUrl);
		
		$this->debugje('setCf_Name: $this->thisUrl='.$this->thisUrl().' $tmp='.$tmp); 
		
		$fn = $path . $tmp . '.css';
		$this->cfName = $fn;
		$this->cfNameMob = $path . $tmp . '.mob.css';
		
		$this->cfNameUrl 	= $pathUrl . $tmp . '.css';
		$this->cfNameMobUrl = $pathUrl . $tmp . '.mob.css';
		
		if (wp_is_mobile()){
			$fn = $this->cfNameMob;
		}else{
			$fn = $this->cfName;
		}
		
		//
		$ism=(int) @$_REQUEST['ism'];
		$this->inProc = $this->cfName . '.inproc';
		if ($ism==1){
			$this->inProc = $this->cfNameMob . '.inproc';
		}
				
		//
		if (file_exists($fn)){
			$sz1 = @filesize($fn); 		 
			if ($sz1>0){
				$sz2 = @filesize($fn);
				if ($sz1==$sz2){
					//$this->dieHard($fn.'~');
					$this->cfReady = true;	$this->debugje('setCf_Name: $fn='.$fn);
				}
			}
		}
	}

	function thisUrl(){
		if (isset($_SERVER['HTTPS']) &&
			($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
			isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
			$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			$protocol = 'https://';	
		} else {
			$protocol = 'http://';
		}
		$this->protocol = $protocol;
		
		$u = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$u = str_replace(array('?debugje=2','&debugje=2','?crtcmux=1','&crtcmux=1','/?nowprocket','&nowprocket','&ism=1')
									,array('','','','','','','')
									,$u);
									
		$u = trim($u,'/'); //die($u.'#');	
		//file_put_contents( __DIR__ . 'logg.txt',$u);
		//$this->dieHard($u.'~');
		return $u;
	}

	private function pollCrtCmuxJs(){
		$js = '<script>
				(function(){
					try{
						var u=window.location.href;
						if (u.includes("crtcmux=1")) return;	//console.log(u+"!");
						u=new URL(u);
						if (u.searchParams.get("nowprocket")=="") return;
						u.searchParams.set("crtcmux", 1);
						fetch(u); 
						u.searchParams.set("ism", 1);
						fetch(u); 
					}catch(e){}
				})();
			   </script>
				';
		return $js;
	}

	private function modifyCss($h){		
		$origH = $h;
		
		if ($this->cfReady){	
			$scArr = $this->getThisTagOnly($h,'<link ','</link>','/>');
			//file_put_contents( __DIR__ . '/ffff.txt',print_r($scArr,true)); die();
			
			for($i=0;$i<count($scArr);$i++){
				$sc=$scArr[$i];		//if (stripos($sc,'stylesheet')===false) continue;
				if ($sc=='') break;
				//return htmlentities( $sc );
				if ( stripos($sc,'https://fonts.googleapis.com')!==false ) continue;

				//x $origH = str_replace($sc,'',$origH);
				$scT = str_replace(' href',' cmux__data_href',$sc);
				$origH = str_replace($sc,$scT,$origH);				
			}
		
			$u 	 = $this->getOnlyUsedCssUrl();
			$css = '';
			$css.= '<link href="'. $u .'" rel="preload"  as="style" />'."\n";
			$css.= '<link rel="stylesheet" href="'. $u 
						.'" onerror="this.onerror=null;document.body.style.display=\'none\';window.location=window.location;" />';
			
			$src = '<meta charset=';
			$origH = str_replace($src,$css.$src,$origH);
			
			//$css = '<link rel="stylesheet" href="'. $u .'" />'; 
			//$origH = str_replace('</body>',$css. "\n" .'</body>',$origH);
			
			$fbo = $this->idxObj->params->get('fallback_mode',0);
			if ($fbo==1){
				$js = $this->cssLoadingJs();
				$origH = str_replace('</body>',$js.'</body>',$origH);
			}
		}
		
		//if ( $this->isHome || $this->isProdDtl>0 ){
			$js = $this->pollCrtCmuxJs();
			$origH = str_replace('</body>',$js.'</body>',$origH);
		//}
		
		$h= $origH;
		
		return $h;
	}	

	private function dieHard($str){
		header('cmx-die-hard:'.$str); die();		
	}
	
	private function infoHeader($key,$str){
		header('cmx-dbgif-'.$key.':'.$str);	
	}

	private function getThisTagOnly($h,$start,$end1,$end2='',$chk='stylesheet'){
		$tmp = explode($start,$h);	
		$scripts = array();
		for($i=1;$i<count($tmp);$i++){ 
			$thisTag = $tmp[$i];	//$this->dieHard('!'.$thisTag.'!');
			$end = $end1;
			if ($end2!=''){
				$p1 = stripos($thisTag,$end1);	if ($p1===false)$p1=-1;
				$p2 = stripos($thisTag,$end2);	if ($p2===false)$p2=-1;
				
				if ($p1==-1 && $p2>-1){
					$end=$end2;
				}
			}
			$t = explode($end,$thisTag);
			if (stripos($t[0],$chk)!==false){
				$scripts[]= $start.$t[0].$end;
			}
		}
		//$str=print_r($scripts,true);
		
		return $scripts;
	}

	private function debugje($str){
		$debugje	= (int) @$_GET['debugje'];
		if ($debugje!=1 && $debugje!=2) $debugje=0;
			
		if ($debugje==1){ 
			echo esc_html($str).'<hr/>';
			
		}
		if ($debugje==2) $this->addToLog($str);
	}


	function addToLog($str){	
		$pn = $this->idxObj->plugName;
		$fh = fopen( __DIR__ . "/log.txt", 'a');// or die("can't open file");
		$str = "\n".date('Y-m-d H:i:s')."\n".$str."\n------------------------------------------------------\n";
		fwrite($fh, $str);
		fclose($fh);	
	}
}


