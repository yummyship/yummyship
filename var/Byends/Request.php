<?php
/**
 * 服务器请求处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Request
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Byends_Request
{
    /**
     * 内部参数
     *
     * @access private
     * @var array
     */
    private $_params = array();

    /**
     * 路径信息
     *
     * @access private
     * @var string
     */
    private $_pathInfo = null;

    /**
     * 服务端参数
     *
     * @access private
     * @var array
     */
    private $_server = array();

    /**
     * 客户端ip地址
     *
     * @access private
     * @var string
     */
    private $_ip = null;

    /**
     * 客户端字符串
     *
     * @access private
     * @var string
     */
    private $_agent = null;

    /**
     * 来源页
     *
     * @access private
     * @var string
     */
    private $_referer = null;

    /**
     * 单例句柄
     *
     * @access private
     * @var Byends_Request
     */
    private static $_instance = null;

    /**
     * 当前过滤器
     *
     * @access private
     * @var array
     */
    private $_filter = array();

    /**
     * 支持的过滤器列表
     *
     * @access private
     * @var string
     */
    private static $_supportFilters = array(
        'int'        => 'intval',
        'integer'    => 'intval',
    	'stripTags'  => array('Byends_Paragraph', 'stripTags'),
    	'stripBrief' => array('Byends_Paragraph', 'stripBrief'),
        'search'     => array('Byends_Paragraph', 'filterSearchQuery'),
        'xss'        => array('Byends_Paragraph', 'removeXSS'),
        'url'        => array('Byends_Paragraph', 'safeUrl')
    );
    
    private static $_routingTable = array();

    /**
     * 获取单例句柄
     *
     * @access public
     * @return Typecho_Request
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
        	self::$_instance = new Byends_Request();
        }
        
        return self::$_instance;
    }
    
    /**
     * 设置路由
     * @param array $routing
     * @return boolean
     */
    public function setRouting($routing)
    {
    	if (is_array($routing)) {
    		foreach ($routing as $k => $v) {
    			self::$_routingTable[$k] = $v;
    		}
    		return true;
    	}
    	
    	return false;
    }
    
    /**
     * 匹配路由
     * @param string $pathInfo
     * @param string $action
     * @return boolean
     */
    public function match($pathInfo, $action = null)
    {
    	if ($action) {
	    	if (preg_match(self::$_routingTable[$action], $pathInfo, $matches)) {
	    		return $matches;
	    	}
    	}
    	else {
    		foreach (self::$_routingTable as $k => $v) {
    			if (preg_match($v, $pathInfo, $matches)) {
    				$matches[0] = $k;
    				return $matches;
    			}
    		}
    	}
    	return false;
    }

    /**
     * 应用过滤器
     *
     * @access private
     * @param mixed $value
     * @return void
     */
    private function _applyFilter($value)
    {
        if ($this->_filter) {
            foreach ($this->_filter as $filter) {
                $value = is_array($value) ? Byends_Paragraph::callbackDeep($filter, $value) :
                call_user_func($filter, $value);
            }
        }
		
        $this->_filter = array();
        return $value;
    }

    /**
     * 设置过滤器
     *
     * @access public
     * @param mixed $filter 过滤器名称
     * @return Typecho_Widget_Request
     */
    public function filter()
    {
        $filters = func_get_args();
		
        foreach ($filters as $filter) {
            $this->_filter[] = is_string($filter) && isset(self::$_supportFilters[$filter])
            ? self::$_supportFilters[$filter] : $filter;
        }
		
        return $this;
    }

    /**
     * 获取实际传递参数(magic)
     *
     * @access public
     * @param string $key 指定参数
     * @return void
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * 判断参数是否存在
     *
     * @access public
     * @param string $key 指定参数
     * @return void
     */
    public function __isset($key)
    {
        return isset($_GET[$key])
        || isset($_POST[$key])
        || isset($_COOKIE[$key])
        || $this->isSetParam($key);
    }

    /**
     * 获取实际传递参数
     *
     * @access public
     * @param string $key 指定参数
     * @param mixed $default 默认参数 (default: null)
     * @return void
     */
    public function get($key, $default = null)
    {
        $value = $default;

        switch (true) {
            case isset($this->_params[$key]):
                $value = $this->_params[$key];
                break;
            case isset($_GET[$key]):
                $value = $_GET[$key];
                break;
            case isset($_POST[$key]):
                $value = $_POST[$key];
                break;
            case isset($_COOKIE[$key]):
                $value = $_COOKIE[$key];
                break;
            default:
                $value = $default;
                break;
        }

        $value = is_array($value) || strlen($value) > 0 ? $value : $default;
        return $this->_filter ? $this->_applyFilter($value) : $value;
    }

    /**
     * 从参数列表指定的值中获取http传递参数
     *
     * @access public
     * @param mixed $parameter 指定的参数
     * @return array
     */
    public function from($params)
    {
        $result = array();
        $args = is_array($params) ? $params : func_get_args();
		$tempFilter = $this->_filter;
		
        foreach ($args as $arg) {
            $result[$arg] = $this->get($arg);
            $this->_filter = $tempFilter;
        }
		
        return $result;
    }

    /**
     * 获取指定的http传递参数
     *
     * @access public
     * @param string $key 指定的参数
     * @param mixed $default 默认的参数
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        $value = isset($this->_params[$key]) ? $this->_params[$key] : $default;
        $value = is_array($value) || strlen($value) > 0 ? $value : $default;
        return $this->_filter ? $this->_applyFilter($value) : $value;
    }

    /**
     * 设置http传递参数
     *
     * @access public
     * @param string $name 指定的参数
     * @param mixed $value 参数值
     * @return void
     */
    public function setParam($name, $value)
    {
        $this->_params[$name] = $value;
    }

    /**
     * 删除参数
     *
     * @access public
     * @param string $name 指定的参数
     * @return void
     */
    public function unSetParam($name)
    {
        unset($this->_params[$name]);
    }

    /**
     * 参数是否存在
     *
     * @access public
     * @param string $key 指定的参数
     * @return boolean
     */
    public function isSetParam($key)
    {
        return isset($this->_params[$key]);
    }

    /**
     * 设置多个参数
     *
     * @access public
     * @param mixed $params 参数列表
     * @return void
     */
    public function setParams($params)
    {
        //处理字符串
        if (!is_array($params)) {
            parse_str($params, $out);
            $params = $out;
        }

        $this->_params = array_merge($this->_params, $params);
    }

    /**
     * 根据当前uri构造指定参数的uri
     *
     * @access public
     * @param mixed $parameter 指定的参数
     * @return string
     */
    public function makeUriByRequest($parameter = null)
    {
        /** 初始化地址 */
        $scheme = $this->isSecure() ? 'https' : 'http';
        $requestUri = strtolower($scheme) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $parts = parse_url($requestUri);

        /** 初始化参数 */
        if (is_string($parameter)) {
            parse_str($parameter, $args);
        } else if (is_array($parameter)) {
            $args = $parameter;
        } else {
            return $requestUri;
        }

        /** 构造query */
        if (isset($parts['query'])) {
            parse_str($parts['query'], $currentArgs);
            $args = array_merge($currentArgs, $args);
        }
        $parts['query'] = http_build_query($args);


        /** 返回地址 */
        return Byends_Paragraph::buildUrl($parts);
    }

    /**
     * 获取当前pathinfo
     *
     * @access public
     * @param string $inputEncoding 输入编码
     * @param string $outputEncoding 输出编码
     * @return string
     */
    public function getPathInfo($inputEncoding = null, $outputEncoding = null)
    {
        /** 缓存信息 */
        if (null !== $this->_pathInfo) {
            return $this->_pathInfo;
        }

        //参考Zend Framework对pahtinfo的处理, 更好的兼容性
        $pathInfo = null;

        //处理requestUri
        $requestUri = null;

        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
            $requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            isset($_SERVER['IIS_WasUrlRewritten'])
            && $_SERVER['IIS_WasUrlRewritten'] == '1'
            && isset($_SERVER['UNENCODED_URL'])
            && $_SERVER['UNENCODED_URL'] != ''
            ) {
            $requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            if (isset($_SERVER['HTTP_HOST']) && strstr($requestUri, $_SERVER['HTTP_HOST'])) {
                $parts       = @parse_url($requestUri);

                if (false !== $parts) {
                    $requestUri  = (empty($parts['path']) ? '' : $parts['path'])
                                 . ((empty($parts['query'])) ? '' : '?' . $parts['query']);
                }
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
            $requestUri = $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            return $this->_pathInfo = '/';
        }

        //处理baseUrl
        $filename = (isset($_SERVER['SCRIPT_FILENAME'])) ? basename($_SERVER['SCRIPT_FILENAME']) : '';

        if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['SCRIPT_NAME'];
        } elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $filename) {
            $baseUrl = $_SERVER['PHP_SELF'];
        } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
            $baseUrl = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path    = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
            $file    = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
            $segs    = explode('/', trim($file, '/'));
            $segs    = array_reverse($segs);
            $index   = 0;
            $last    = count($segs);
            $baseUrl = '';
            do {
                $seg     = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
        }

        // Does the baseUrl have anything in common with the request_uri?
        $finalBaseUrl = null;

        if (0 === strpos($requestUri, $baseUrl)) {
            // full $baseUrl matches
            $finalBaseUrl = $baseUrl;
        } else if (0 === strpos($requestUri, dirname($baseUrl))) {
            // directory portion of $baseUrl matches
            $finalBaseUrl = rtrim(dirname($baseUrl), '/');
        } else if (!strpos($requestUri, basename($baseUrl))) {
            // no match whatsoever; set it blank
            $finalBaseUrl = '';
        } else if ((strlen($requestUri) >= strlen($baseUrl))
            && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0)))
        {
            // If using mod_rewrite or ISAPI_Rewrite strip the script filename
            // out of baseUrl. $pos !== 0 makes sure it is not matching a value
            // from PATH_INFO or QUERY_STRING
            $baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
        }

        $finalBaseUrl = (null === $finalBaseUrl) ? rtrim($baseUrl, '/') : $finalBaseUrl;

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ((null !== $finalBaseUrl)
            && (false === ($pathInfo = substr($requestUri, strlen($finalBaseUrl)))))
        {
            // If substr() returns false then PATH_INFO is set to an empty string
            $pathInfo = '/';
        } elseif (null === $finalBaseUrl) {
            $pathInfo = $requestUri;
        }

        if (!empty($pathInfo)) {
            //针对iis的utf8编码做强制转换
            //参考http://docs.moodle.org/ja/%E5%A4%9A%E8%A8%80%E8%AA%9E%E5%AF%BE%E5%BF%9C%EF%BC%9A%E3%82%B5%E3%83%BC%E3%83%90%E3%81%AE%E8%A8%AD%E5%AE%9A
            if (!empty($inputEncoding) && !empty($outputEncoding) &&
            (stripos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false
            || stripos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false)) {
                if (function_exists('mb_convert_encoding')) {
                    $pathInfo = mb_convert_encoding($pathInfo, $outputEncoding, $inputEncoding);
                } else if (function_exists('iconv')) {
                    $pathInfo = iconv($inputEncoding, $outputEncoding, $pathInfo);
                }
            }
        } else {
            $pathInfo = '/';
        }

        // fix issue 456
        return ($this->_pathInfo = '/' . ltrim(urldecode($pathInfo), '/'));
    }

    /**
     * 设置服务端参数
     *
     * @access public
     * @param string $name 参数名称
     * @param mixed $value 参数值
     * @return void
     */
    public function setServer($name, $value = null)
    {
        if (null == $value) {
            if (isset($_SERVER[$name])) {
                $value = $_SERVER[$name];
            } else if (isset($_ENV[$name])) {
                $value = $_ENV[$name];
            }
        }

        $this->_server[$name] = $value;
    }

    /**
     * 获取环境变量
     *
     * @access public
     * @param string $name 获取环境变量名
     * @return string
     */
    public function getServer($name)
    {
        if (!isset($this->_server[$name])) {
            $this->setServer($name);
        }

        return $this->_server[$name];
    }

    /**
     * 设置ip地址
     *
     * @access public
     * @param unknown $ip
     * @return unknown
     */
    public function setIp($ip = null)
    {
        switch (true) {
            case null !== $this->getServer('HTTP_X_FORWARDED_FOR'):
                list($this->_ip) = array_map('trim', explode(',', $this->getServer('HTTP_X_FORWARDED_FOR')));
                return;
            case null !== $this->getServer('HTTP_CLIENT_IP'):
                $this->_ip = $this->getServer('HTTP_CLIENT_IP');
                return;
            case null !== $this->getServer('REMOTE_ADDR'):
                $this->_ip = $this->getServer('REMOTE_ADDR');
                return;
            default:
                break;
        }

        $this->_ip = 'unknown';
    }

    /**
     * 获取ip地址
     *
     * @access public
     * @return string
     */
    public function getIp()
    {
        if (null === $this->_ip) {
            $this->setIp();
        }

        return $this->_ip;
    }

    /**
     * 设置客户端
     *
     * @access public
     * @param string $agent 客户端字符串
     * @return void
     */
    public function setAgent($agent = null)
    {
        $this->_agent = (null === $agent) ? $this->getServer('HTTP_USER_AGENT') : $agent;
    }

    /**
     * 获取客户端
     *
     * @access public
     * @return void
     */
    public function getAgent()
    {
        if (null === $this->_agent) {
            $this->setAgent();
        }

        return $this->_agent;
    }

    /**
     * 设置来源页
     *
     * @access public
     * @param string $referer 客户端字符串
     * @return void
     */
    public function setReferer($referer = null)
    {
        $this->_referer = (null === $referer) ? $this->getServer('HTTP_REFERER') : $referer;
    }

    /**
     * 获取客户端
     *
     * @access public
     * @return void
     */
    public function getReferer()
    {
        if (null === $this->_referer) {
            $this->setReferer();
        }

        return $this->_referer;
    }

    /**
     * 判断是否为get方法
     *
     * @access public
     * @return boolean
     */
    public function isGet()
    {
        return 'GET' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * 判断是否为post方法
     *
     * @access public
     * @return boolean
     */
    public function isPost()
    {
        return 'POST' == $this->getServer('REQUEST_METHOD');
    }
    
    /**
     * 判断是否为request 方法 ( get 或者 post 至少有一种)
     *
     * @access public
     * @return boolean
     */
    public function isRequest()
    {
    	return 'GET' == $this->getServer('REQUEST_METHOD') || 'POST' == $this->getServer('REQUEST_METHOD');
    }
    
    /**
     * 判断是否设置指定 key 的 get 方式
     *
     * @access public
     * @return boolean
     */
    public function isSetGet($key)
    {
    	return isset($_GET[$key]);
    }
    
    /**
     * 判断是否设置指定 key 的 post 方式
     *
     * @access public
     * @return boolean
     */
    public function isSetPost($key)
    {
    	return isset($_POST[$key]);
    }
    
    /**
     * 判断是否设置指定 key 的 request 方法 ( get 或者 post 至少有一种)
     *
     * @access public
     * @return boolean
     */
    public function isSetRequest($key)
    {
    	return isset($_GET[$key]) || isset($_POST[$key]);;
    }

    /**
     * 判断是否为put方法
     *
     * @access public
     * @return boolean
     */
    public function isPut()
    {
        return 'PUT' == $this->getServer('REQUEST_METHOD');
    }

    /**
     * 判断是否为https
     *
     * @access public
     * @return boolean
     */
    public function isSecure()
    {
        return 'on' == $this->getServer('HTTPS') || 443 == $this->getServer('SERVER_PORT');
    }

    /**
     * 判断是否为ajax
     *
     * @access public
     * @return boolean
     */
    public function isAjax()
    {
        return 'XMLHttpRequest' == $this->getServer('HTTP_X_REQUESTED_WITH');
    }

    /**
     * 判断是否为flash
     *
     * @access public
     * @return boolean
     */
    public function isFlash()
    {
        return 'Shockwave Flash' == $this->getServer('USER_AGENT');
    }

    /**
     * 判断输入是否满足要求
     *
     * @access public
     * @param mixed $query 条件
     * @return boolean
     */
    public function is($query)
    {
        $validated = false;

        /** 解析串 */
        if (is_string($query)) {
            parse_str($query, $params);
        } else if (is_array($query)) {
            $params = $query;
        }

        /** 验证串 */
        if ($params) {
            $validated = true;
            foreach ($params as $key => $val) {
                if (empty($val)) {
                    $validated = $this->__isSet($key);
                } else {
                    $validated = ($this->get($key) == $val);
                }

                if (!$validated) {
                    break;
                }
            }
        }

        return $validated;
    }
    
    public function url($path, $prefix)
    {
    	$path = (0 === strpos($path, './')) ? substr($path, 2) : $path;
    	return rtrim($prefix, '/') . '/' . str_replace('//', '/', ltrim($path, '/'));
    }
}
