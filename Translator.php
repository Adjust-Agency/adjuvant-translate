<?php namespace Adjuvant\Translate;
	
use Illuminate\Database\Capsule\Manager as Database;

class Translator {
	
	public $insert 				= true;
	public $language 			= "fr";
	public $table				= "translations";
	
	protected $languages			= array('fr', 'nl');
	protected $trads				= array();
	protected $config				= array(
		'driver' => 'mysql',
		'charset'   => 'utf8',
		'collation' => 'utf8_unicode_ci',
		'prefix'    => ''
	);
	
	protected $db					= null; 
	
	private $loaded	= false;
	
	public function __construct() {
		$this->checkTable();
	}
	
	private function checkTable() {
		$this->checkDatabase();
		$exists = $this->db->getConnection()->select('SHOW TABLES LIKE  "' . $this->table . '"');
		if(empty($exists)){
			$languages = $this->languages;
			Database::schema()->create($this->table, function($table) use($languages){
				$table->increments('id');
				$table->text('key');
				foreach($languages as $lang){
					$table->text($lang);
				}
			});
		}
	}
	
	private function checkEnv()
	{
		if( empty($this->config['username'] ) ) {
			
			if( !empty($_ENV['DB_USERNAME']) ){
			    $this->config['driver']    = $_ENV['DB_CONNECTION'];
			    $this->config['host']      = $_ENV['DB_HOST'];
			    $this->config['database']  = $_ENV['DB_DATABASE'];
			    $this->config['username']  = $_ENV['DB_USERNAME'];
			    $this->config['password']  = $_ENV['DB_PASSWORD'];
			}
			
		}
		
		
	}
	
	private function checkDatabase()
	{
		$this->checkEnv();
		if( is_null( $this->db ) ) {
			$this->db = new Database();
			$this->db->addConnection($this->config);
			$this->db->setAsGlobal();			
		}
	}
	
	private function load()
	{
		
		if( !$this->loaded ) {
		
			$this->checkDatabase();		
			
			$trads = $this->db->table($this->table)->get();
			foreach($trads as $trad){
				$trad = (object) $trad;
				foreach($this->languages as $lang){
					if(empty($this->trads)) $this->trads[$lang] = array();
					if(!empty($trad->$lang)) $this->trads[$lang][$trad->key] = $trad->$lang;
				}
			}
			$this->loaded = true;
		}			
	}
	
	private function insertTrad($key, $lang)
	{
		
		$this->load();
		
		$row = (object) $this->db->table($this->table)->select('id')->where('key','=', $key)->first();
		if(empty($row->id)){
			$this->db->table($this->table)->insert(array(
				'key'	=> $key,
				$lang 	=> $key
			));
		}	
	}
	
	public function setConfig($config){
		foreach($config as $k => $v){
			$this->config[$k] = $v;
		}
	}
	
	public function _($key, $lang = null)
	{
			
		$this->load();
		
		if(is_null($lang)) $lang = $this->language;
		if($this->insert) $this->insertTrad($key, $lang);		
			
		return !empty($this->trads[$lang][$key]) ? $this->trads[$lang][$key] : $key;
	}
	
}