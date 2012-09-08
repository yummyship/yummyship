<?php
/**
 * 提示框组件
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Notice
 * @copyright  Copyright (c) 2011 Byends (http://www.byends.com)
 */

class Byends_Notice
{
	/**
	 * 数据堆栈每一行
	 *
	 * @access protected
	 * @var array
	 */
	protected $row = array();
	
	/**
	 * 数据堆栈
	 *
	 * @access public
	 * @var array
	 */
	public $stack = array();
	
    /**
     * 提示类型
     *
     * @access public
     * @var string
     */
    public $noticeType = 'notice';

    /**
     * 提示高亮
     *
     * @access public
     * @var string
     */
    public $highlight;

    public $gmtTimeStamp = 0;
	public $timeStamp = 0;
    
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        if (NULL !== Byends_Cookie::get('__byends_notice')) {
            $this->noticeType = Byends_Cookie::get('__byends_notice_type');
            $this->push(Byends_Cookie::get('__byends_notice'));
            Byends_Cookie::delete('__byends_notice', BYENDS_BASE_URL);
            Byends_Cookie::delete('__byends_notice_type', BYENDS_BASE_URL);
        }

        if (NULL !== Byends_Cookie::get('__byends_notice_highlight')) {
            $this->highlight = Byends_Cookie::get('__byends_notice_highlight');
            Byends_Cookie::delete('__byends_notice_highlight', BYENDS_BASE_URL);
        }
        
        $this->gmtTimeStamp = Byends_Date::gmtTime();
        $this->timeStamp = Byends_Date::timeStamp($this->gmtTimeStamp);
    }

    /**
     * 将每一行的值压入堆栈
     *
     * @param array $value 每一行的值
     * @return array
     */
    public function push(array $value)
    {
    	//将行数据按顺序置位
    	$this->row = $value;
    
    	$this->stack[] = $value;
    	return $value;
    }
    
    /**
     * 返回堆栈是否为空
     *
     * @return boolean
     */
    public function have()
    {
    	return !empty($this->stack);
    }
    
    /**
     * 输出提示类型
     *
     * @access public
     * @return void
     */
    public function noticeType()
    {
        echo $this->noticeType;
    }

    /**
     * 列表显示所有提示内容
     *
     * @access public
     * @param string $tag 列表html标签
     * @return void
     */
    public function lists($tag = 'li')
    {
        foreach ($this->row as $row) {
            echo "<$tag>" . $row . "</$tag>";
        }
    }

    /**
     * 显示相应提示字段
     *
     * @access public
     * @param string $name 字段名称
     * @param string $format 字段格式
     * @return void
     */
    public function display($name, $format = '%s')
    {
        echo empty($this->row[$name]) ? NULL :
        ((false === strpos($format, '%s')) ? $format : sprintf($format, $this->row[$name]));
    }

    /**
     * 高亮相关元素
     *
     * @access public
     * @param string $theId 需要高亮元素的id
     * @return void
     */
    public function highlight($theId)
    {
        $this->highlight = $theId;
        Byends_Cookie::set('__byends_notice_highlight', $theId, $this->timeStamp + 86400, BYENDS_BASE_URL);
    }

    /**
     * 获取高亮的id
     *
     * @access public
     * @return integer
     */
    public function getHighlightId()
    {
        return preg_match("/[0-9]+/", $this->highlight, $matches) ? $matches[0] : 0;
    }

    /**
     * 设定堆栈每一行的值
     *
     * @param string $name 值对应的键值
     * @param mixed $name 相应的值
     * @param string $type 提示类型
     * @return array
     */
    public function set($name, $value = NULL, $type = 'notice')
    {
        $notice = array();

        if (is_array($name)) {
            foreach ($name as $key => $row) {
                $notice[$key] = $row;
            }
        } else {
            if (empty($value)) {
                $notice[] = $name;
            } else {
                $notice[$name] = $value;
            }
        }

        $this->noticeType = $type;
        $this->push($notice);

        Byends_Cookie::set('__byends_notice', $notice,$this->timeStamp + 86400, BYENDS_BASE_URL);
        Byends_Cookie::set('__byends_notice_type', $type, $this->timeStamp + 86400, BYENDS_BASE_URL);
    }
}
