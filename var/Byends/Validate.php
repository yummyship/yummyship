<?php
/**
 * 验证类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Validate
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */

/** 载入api支持 */
require_once 'Byends/Paragraph.php';

/**
 * <code>
 * $test = "hello";
 * $Validation  = new Byends_Validate();
 * $Validation->form($test, array("alpha" => "不是字符");
 * var_dump($Validation->getErrorMsg());
 * </code>
 */
class Byends_Validate
{
    /**
     * 内部数据
     *
     * @access private
     * @var array
     */
    private $_data;

    /**
     * 当前验证指针
     *
     * @access private
     * @var string
     */
    private $_key;

    /**
     * 验证规则数组
     *
     * @access private
     * @var array
     */
    private $_rules = array();

    /**
     * 中断模式,一旦出现验证错误即抛出而不再继续执行
     *
     * @access private
     * @var boolean
     */
    private $_break = false;

    /**
     * 增加验证规则
     *
     * @access public
     * @param string $key 数值键值
     * @param string $rule 规则名称
     * @param string $message 错误字符串
     * @return Byends_Validation
     */
    public function addRule($key, $rule, $message)
    {
        if (func_num_args() <= 3) {
            $this->_rules[$key][] = array($rule, $message);
        } else {
            $params = func_get_args();
            $params = array_splice($params, 3);
            $this->_rules[$key][] = array_merge(array($rule, $message), $params);
        }

        return $this;
    }
    
    /**
     * 删除验证规则
     * @param string $key 数值键值
     * @return Byends_Validation
     */    
    public function deleteRule($key)
    {
    	if (array_key_exists($key, $this->_rules)) {
    		unset($this->_rules[$key]);
    	}
    	return $this;
    }
    
    /**
     * 设置为中断模式
     *
     * @access public
     * @return void
     */
    public function setBreak()
    {
        $this->_break = true;
    }

    /**
     * Run the Validator
     * This function does all the work.
     *
     * @access	public
     * @param   array $data 需要验证的数据
     * @param   array $rules 验证数据遵循的规则
     * @return	array
     * @throws  Byends_Validate_Exception
     */
    public function run(array $data, $rules = null)
    {
        $result = array();
        $this->_data = $data;
        $rules = empty($rules) ? $this->_rules : $rules;

        // Cycle through the rules and test for errors
        foreach ($rules as $key => $rules) {
            $this->_key = $key;
            $data[$key] = (0 == strlen($data[$key])) ? null : $data[$key];

            foreach ($rules as $params) {
                $method = $params[0];

                if ('required' != $method && 'confirm' != $method && 0 == strlen($data[$key])) {
                    continue;
                }

                $message = $params[1];
                $params[1] = $data[$key];
                $params = array_slice($params, 1);

                if (!call_user_func_array(is_array($method) ? $method : array($this, $method), $params)) {
                    $result[$key] = $message;
                    break;
                }
            }

            /** 开启中断 */
            if ($this->_break && $result) {
                break;
            }
        }

        return $result;
    }

    /**
     * 最小长度
     *
     * @access public
     * @param string $str 待处理的字符串
     * @param integer $length 最小长度
     * @return boolean
     */
    public function minLength($str, $length)
    {
        return (Byends_Paragraph::strLen($str) >= $length);
    }

    /**
     * 验证输入是否一致
     *
     * @access public
     * @param string $str 待处理的字符串
     * @param string $key 需要一致性检查的键值
     * @return boolean
     */
    public function confirm($str, $key)
    {
        return !empty($this->_data[$key]) ? ($str == $this->_data[$key]) : empty($str);
    }

    /**
     * 是否为空
     *
     * @access public
     * @param string $str 待处理的字符串
     * @return boolean
     */
    public function required($str)
    {
        return !empty($this->_data[$this->_key]);
    }

    /**
     * 枚举类型判断
     *
     * @access public
     * @param string $str 待处理的字符串
     * @param array $params 枚举值
     * @return unknown
     */
    public function enum($str, array $params)
    {
        return in_array($str, $params);
    }

    /**
     * Max Length
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function maxLength($str, $length)
    {
        return (Byends_Paragraph::strLen($str) < $length);
    }

    /**
     * Valid Email
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function email($str)
    {
        return preg_match("/^[^@\s<&>]+@([-a-z0-9]+\.)+[a-z]{2,}$/i", $str);
    }

    /**
     * 验证是否为网址
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public function url($str)
    {
        $parts = @parse_url($str);
        if (!$parts) {
            return false;
        }

        return isset($parts['scheme']) &&
        in_array($parts['scheme'], array('http', 'https', 'ftp')) &&
        !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', $str);
    }

    /**
     * Alpha
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function alpha($str)
    {
        return preg_match("/^([a-z])+$/i", $str) ? true : false;
    }

    /**
     * Alpha-numeric
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function alphaNumeric($str)
    {
        return preg_match("/^([a-z0-9])+$/i", $str);
    }

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function alphaDash($str)
    {
        return preg_match("/^([_a-z0-9-])+$/i", $str) ? true : false;
    }

    /**
     * 对xss字符串的检测
     *
     * @access public
     * @param string $str
     * @return boolean
     */
    public function xssCheck($str)
    {
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';

        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // &#x0040 @ search for the hex values
            $str = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $str); // with a ;
            // &#00064 @ 0{0,7} matches '0' zero to seven times
            $str = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $str); // with a ;
        }

        return !preg_match('/(\(|\)|\\\|"|<|>|[\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', $str);
    }

    /**
     * Numeric
     *
     * @access public
     * @param integer
     * @return boolean
     */
    public function isFloat($str)
    {
        return ereg("^[0-9\.]+$", $str);
    }

    /**
     * Is Numeric
     *
     * @access public
     * @param string
     * @return boolean
     */
    public function isInteger($str)
    {
        return is_numeric($str);
    }
}
