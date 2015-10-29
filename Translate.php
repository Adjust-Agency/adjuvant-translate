<?php namespace Adjuvant;

class Translate {
	
	public $insert 				= true;
	public $language 			= "fr";
	
	private $languages			= array('fr', 'nl');
	private $trads				= array();
	
	public function __construct(){
		$this->load();
	}
	
	private function load(){
		$trads = \DB::select('SELECT * FROM translations');
		foreach($trads as $trad){
			foreach($this->languages as $lang){
				if(empty($this->trads)) $this->trads[$lang] = array();
				if(!empty($trad->$lang)) $this->trads[$lang][$trad->key] = $trad->$lang;
			}
		}				
	}
	
	private function insertTrad($key, $lang){
		$row = \DB::table('translations')->select('id')->where('key','=', $key)->first();
		if(empty($row->id)){
			\DB::table('translations')->insert(array(
				'key'	=> $key,
				$lang 	=> $key
			));
		}		
	}
	
	public function _($key, $lang = null){
			
		if(is_null($lang)) $lang = $this->language;
		if($this->insert) $this->insertTrad($key, $lang);		
		
		return !empty($this->trads[$lang][$key]) ? $this->trads[$lang][$key] : $key;
	}
	
}