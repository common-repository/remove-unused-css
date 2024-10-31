<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if (!class_exists('cmcmuxcss_GeneHelper')){
  class cmcmuxcss_GeneHelper{
	public $idxObj=null;
	
	public function __construct(& $idxObj){
		$this->idxObj = $idxObj;
	}
	
	public function getQuotaLink(){
		$qt = '<span style="color:green">Quota Availble</span>'; 
		$r=1;
		if (file_exists($this->idxObj->plugPath . '/cache/nqt.txt')){
			$qt = '<span style="color:red">No Quota</span>';
			$r=0;
		}
		
		$h= '<a href="https://codingmall.com/wordpress/209-remove-unused-css">'.$qt.'</a>';
		return array($h,$r);
	}
	
	public function getThisPageUrl(){
		global $wp;

		$parts = parse_url( home_url() );
		$u = "{$parts['scheme']}://{$parts['host']}" . add_query_arg( NULL, NULL );
		
		return $u;
	}
	
	public function getRootUrl(){
		$u = get_site_url();
		$u = trim($u,'/').'/';
		return $u;
	}

	public function getReqVar($var,$defa,$method=''){
		if (isset($_REQUEST[$var])){
			return (int) @$_REQUEST[$var];
		}else{
			return $defa;
		}
	}
		
	public function settingsHeader($mainHead,$desc,$head2='Settings'){
		$h='<div  class="wrap">
				<h2>'.$mainHead.'</h2>
				<p class="description">'.$desc.'</p>
				<h3>'.$head2.'</h3>
			';
		return $h;
	}
	
	public function showSubmit(){
		ob_start(); submit_button(); $b = ob_get_clean();
		$h = '<tr valign="top" align="left">
				<td colspan="2">'.$b.'</td>
				</tr>
			';
		return $h;
	}

	public function showField($name,$caption,$desc,$options,$defaValue,$type='text',$field_ops=array(),$extra=''){
		$exSt='';
		if ($type=='yesno'){
			$fh = $this->showRadioYesNo($name,$desc,$options,$defaValue,$field_ops); 
			$exSt='vertical-align:top;';
			
		}elseif ($type=='color'){
			$fh = $this->colorField($name,$options,$defaValue,$field_ops);
			
		}elseif ($type=='text'){
			$fh = '<input name="'.$name.'" id="'.$name.'" value="'.$defaValue.'" title="'.$desc.'" '.$extra.'>
					<span class="description">'.$desc.'</span>';
		}
		
		$h = '
		<tr>
			<th scope="row" style="'.$exSt.'">'.$this->rowLabel($name,$caption).'</th>
			<td></td>
			<td>'.$fh.'</td>
		</tr>
			';
		return $h;
	}
	
	public function showField2($name,$caption,$desc,$options,$defaValue,$type='text',$field_ops=array(),$extra='',$extra2=''){
		$exSt='';
		$fname = $this->getFieldName($name);
		
		if ($type=='yesno'){
			$fh = $this->showRadioYesNo($name,$desc,$options,$defaValue,$field_ops);
			$exSt='vertical-align:top;';
			
		}elseif ($type=='color'){
			$fh = $this->colorField($name,$options,$defaValue,$field_ops);
			
		}elseif ($type=='text'){
			$fh = '<input name="'.$fname.'" id="'.$name.'" value="'.$defaValue.'" title="'.$desc.'" '.$extra.'>
					<span class="description">'.$desc.'</span>';
		}elseif ($type=='list'){
			if ($extra=='') $extra='style="width: 200px;"';
			$extra .= $extra2;
			$fh = $this->selectList($options, $fname, 'value', 'text', $defaValue,$extra);
			
		}elseif ($type=='textarea'){
			if ($extra=='') $extra='style="width: 100%;height: 100px;"';
			$fh = '<textarea name="'.$fname.'" id="'.$name.'" title="'.$desc.'" '.$extra. $extra2.'>'
					.stripslashes( $defaValue ) .'</textarea>
					<div><span class="description">'.$desc.'</span></div>';
		}elseif ($type=='link'){
			$fh = '<a href="'.$field_ops['href'].'" title="'.$desc.'" >'.$field_ops['capt'].'</a>';
			
		}elseif ($type=='html'){
			$fh = $field_ops['html'].'
					<div><span class="description">'.$desc.'</span></div>
					';
		}
		
		$h = '
		<tr id="trow_'.$name.'">
			<th scope="row" style="width: 200px;'.$exSt.'">'.$this->rowLabel($name,$caption).'</th>
			<td></td>
			<td>'.$fh.'</td>
		</tr>
			';
		return $h;
	}	

	public function selectList($items, $fieldname, $valField, $descField, $selected_value,$extraAttrib=''){
		$h='<select name="'.$fieldname.'" id="'.$fieldname.'" '.$extraAttrib.'>';
		for($i=0;$i<count($items);$i++){
			$r=@$items[$i]; $sel='';	if (@$r->hidden==1) continue;
			if (@$r->$valField == $selected_value) $sel='selected="selected" ';
			$h.='<option value="'.@$r->$valField.'" '.$sel.'>'.@$r->$descField.'</option>';
		}
		$h.='</select>';
		
		return $h;
	}
	
	public function colorField($name,$options,$defaValue='#e23434',$field_ops){
		$fname = $this->getFieldName($name);

		$v = @$options[$name];
		if ($v=='') $v = $defaValue;
		
		$h = '<input type="text" name="'.$fname.'" id="'.$fname.'" 
				value="'.$v.'" placeholder="'.$defaValue.'" class="color-field">
				';
		
		return $h;
	}

	public function rowLabel($name,$caption){
		$h = '<label for="'.$name.'">'.$caption.'</label>';
		return $h;
	}
	
	public function showRadioYesNo($name,$desc,$options,$defaValue,$field_ops){
		$lbl 		= array('Yes','No');
		$lblTitle 	= array('Yes','No');
		$value	 	= array('1','0');
		
		//var_dump($field_ops);
		
		if ( @count(@$field_ops['lbl'])      >0 ){
			$lbl 		= @$field_ops['lbl'];
			$lblTitle 	= $lbl;
		}
		if ( @count(@$field_ops['lblTitle']) >0 ) 	$lblTitle 	= @$field_ops['lblTitle'];
		if ( @count(@$field_ops['value'])    >0 ) 	$value		= @$field_ops['value'];
		
		$desc		= '<p class="description">'.$desc.'</p>';
		$h = $this->showRadios($lbl,$lblTitle,$name,$value,$desc,$options,$defaValue,$field_ops); 	
		return $h;
	}
	
	public function getFieldName($name){
		$fname = $this->idxObj->param_pfx . 'adm_settings['.$name.']';
		
		return $fname;
	}
	
	public function showRadios($lbl,$lblTitle,$name,$value,$desc,$options,$defaValue,$field_ops){
		$h = '
			<fieldset>
			';
		for($i=0;$i<count($lbl);$i++){
			$chk = '';
			$cv = @$options[$name]; //die($cv.'!');
			if ($cv==null || $cv=='') $cv=$defaValue;
			
			if ( $value[$i]==$cv ) $chk = ' checked="checked"';
			
			$fname = $this->getFieldName($name);
			
			$style = ''; $dis='';
			if (@$field_ops['disabled'][$i]==1){
				$style .= ' opacity:0.6;';
				$dis	= ' disabled="disabled" ';
			}
			
			$h.='
				<label title="'.$lblTitle[$i].'" style="'.$style.'" ><input type="radio" name="'.$fname.'" 
						id="'.$fname.'"
						'.$dis.' value="'.$value[$i].'"
					'.$chk.'>'.$lbl[$i].'</label>
				<br>';
		}
		$h.='</fieldset>
			<p class="description">'.$desc.'</p>
			';
		return $h;
	}
  }//class
}//if check