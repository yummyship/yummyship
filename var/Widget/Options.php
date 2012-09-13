<?php
/**
 * 基本配置处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Options
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Widget_Options extends Byends_Widget
{
	public static $options = null;
	private static $_routingTable = array(
    	'index'        => '|^[/]?$|',
    	'index_page'   => '|^/index/([0-9]+)$|',
    	'auth'  	   => '|^/auth/([^/]+)$|',
    	'user'  	   => '|^/user/([^/]+)$|',
    	'cook'  	   => '|^/cook/([^/]+)$|',
    	'cook_page'    => '|^/cook/([^/]+)/([0-9]+)$|',
    	'likes'  	   => '|^/likes/([^/]+)$|',
    	'likes_page'   => '|^/likes/([^/]+)/([0-9]+)$|',
    	//'tag'		   => '|^/tag/([^/]+)$|',
    	//'tag_page'   => '|^/tag/([^/]+)/([0-9]+)$|',
    	'popular'      => '|^/popular$|',
    	'popular_page' => '|^/popular/([0-9]+)$|',
    	'random'       => '|^/random$|',
    	'api'      	   => '|^/api$|',
    	'apiDo'        => '|^/api/([^/]+)|',
    	'feed'         => '|^/feed$|',
    );
	
	/**
	 * 单例句柄
	 *
	 * @access private
	 * @var Widget_Options
	 */
	private static $_instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		self::$options = (object)$this->select();
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Options
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new Widget_Options();
		}
	
		return self::$_instance;
	}
	
	/**
	 * 获取配置句柄
	 * @return array
	 */
	public static function get()
	{
		if (empty(self::$options)) {
			self::getInstance();
		}
		
		return self::$options;
	}
	
	/**
	 * 获取配置项
	 * @return array
	 */
	public function select()
	{
		$options = $this->db->query(
			'SELECT  
				p.*
			FROM 
				'.BYENDS_TABLE_OPTIONS.' p	'
		);
		$temp = array();
		foreach( $options as $v ) {
			$temp[$v['name']] = $v['value'];
		}
		
		self::$_routingTable['zoom']     = '|^/'.$temp['seed'].'/([0-9]+)/zoom$|';
		self::$_routingTable['seed']     = '|^/'.$temp['seed'].'/([0-9]+)$|';
		self::$_routingTable['tag'] 	 = '|^/'.$temp['tag'].'/([^/]+)$|';
		self::$_routingTable['tag_page'] = '|^/'.$temp['tag'].'/([^/]+)/([0-9]+)$|';
		$temp['routingTable'] = self::$_routingTable;
		$temp['imageConfig'] = unserialize($temp['imageConfig']);
		$temp['imageConfig']['coverSize'] = explode('|', $temp['imageConfig']['coverSize']);
		$temp['imageConfig']['thumbSize'] = explode('|', $temp['imageConfig']['thumbSize']);
		$temp['imageConfig']['stepSize']  = explode('|', $temp['imageConfig']['stepSize']);
		//$temp['absolutePath'] = substr($temp['absolutePath'], -1) == '/' ? substr($temp['absolutePath'], 0, -1) : $temp['absolutePath'];
		return $temp;
	}
	
	/** 
	 * 添加配置
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public function insert($name, $value) 
	{
		$this->db->insertRow(
				BYENDS_TABLE_OPTIONS,
				array(
					'name'  => $name,
					'value' => $value
				)
		);
		return true;
	}
	
	/**
	 * 更新配置
	 * @param string $name
	 * @param string $value
	 * @return boolean
	 */
	public function update($name, $value) 
	{
		$this->db->updateRow(
				BYENDS_TABLE_OPTIONS,
				array( 'name'  => $name ),
				array( 'value' => $value )
		);
		return true;
	}
	
	/**
	 * 主题列表
	 * @return array
	 */
	public function themesList() 
	{
		$themes = array();
		if ($handle = opendir(BYENDS_THEMES_DIR)) {
			while (false !== ($theme = readdir($handle))) {
				if ($theme{0} <> '.' && is_dir(BYENDS_THEMES_DIR.$theme)) {
					$themes[] = $theme;
				}
			}
			closedir($handle);
		}
		
		return $themes;
	}
	
}
?>