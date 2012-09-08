<?php
/**
 * 段落处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Date
 * @copyright  Copyright (c) 2011 Byends (http://www.byends.com)
 */
class Byends_Paragraph
{
	/**
	 * 默认编码
	 *
	 * @access public
	 * @var string
	 */
	public static $charset = 'UTF-8';
	
    /**
     * 唯一id
     * 
     * @access private
     * @var integer
     */
    private static $_uniqueId = 0;
    
    /**
     * 存储的段落
     * 
     * @access private
     * @var array
     */
    private static $_blocks = array();
    
    /**
     * 作为段落看待的标签
     * 
     * (default value: 'p|code|pre|div|blockquote|form|ul|ol|dd|table|h1|h2|h3|h4|h5|h6')
     * 
     * @var string
     * @access private
     * @static
     */
    private static $_blockTag = 'p|code|pre|div|blockquote|form|ul|ol|dd|table|h1|h2|h3|h4|h5|h6';
	
    
    /**
     * 锁定的代码块
     *
     * @access private
     * @var array
     */
    private static $_lockedBlocks = array('<p></p>' => '');
    
    
    /**
     * 生成唯一的id, 为了速度考虑最多支持1万个tag的处理
     * 
     * @access private
     * @return string
     */
    private static function makeUniqueId()
    {
        return ':' . str_pad(self::$_uniqueId ++, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * 用段落方法处理换行
     * 
     * @access private
     * @param string $text
     * @return string
     */
    private static function cutByBlock($text)
    {
        $space = "( |　)";
        $text = str_replace("\r\n", "\n", trim($text));
        $text = preg_replace("/{$space}*\n{$space}*/is", "\n", $text);
        $text = preg_replace("/\n{2,}/", "</p><p>", $text);
        $text = nl2br($text);
        $text = preg_replace("/(<p>)?\s*<p:([0-9]{4})\/>\s*(<\/p>)?/s", "<p:\\2/>", $text);
        $text = preg_replace("/<p>{$space}*<\/p>/is", '', $text);
        return $text;
    }
    
    /**
     * 修复段落开头和结尾
     * 
     * @access private
     * @param string $text
     * @return string
     */
    private static function fixPragraph($text)
    {
        $text = trim($text);
        if (!preg_match("/^<(" . self::$_blockTag . ")(\s|>)/i", $text)) {
            $text = '<p>' . $text;
        }
        
        if (!preg_match("/<\/(" . self::$_blockTag . ")>$/i", $text)) {
            $text = $text . '</p>';
        }
        
        return $text;
    }
    
    /**
     * 替换段落的回调函数
     * 
     * @access public
     * @param array $matches 匹配值
     * @return string
     */
    public static function replaceBlockCallback($matches)
    {
        $tagMatch = '|' . $matches[1] . '|';
        $text = $matches[4];
    
        switch (true) {
            /** 用br处理换行 */
            case false !== strpos('|li|dd|dt|td|p|a|span|cite|strong|sup|sub|small|del|u|i|b|h1|h2|h3|h4|h5|h6|', $tagMatch):
                $text = nl2br(trim($text));
                break;
            /** 用段落处理换行 */
            case false !== strpos('|div|blockquote|form|', $tagMatch):
                $text = self::cutByBlock($text);
                if (false !== strpos($text, '</p><p>')) {
                    $text = self::fixPragraph($text);
                }
                break;
            default:
                break;
        }
        
        /** 没有段落能力的标签 */
        if (false !== strpos('|a|span|cite|strong|sup|sub|small|del|u|i|b|', $tagMatch)) {
            $key = '<b' . $matches[2] . '/>';
        } else {
            $key = '<p' . $matches[2] . '/>';
        }
        
        self::$_blocks[$key] = "<{$matches[1]}{$matches[3]}>{$text}</{$matches[1]}>";
        return $key;
    }
    
    /**
     * 文本分段函数
     * 
     * @access public
     * @param string $text 需要分段的文本
     * @return string
     */
    public static function cutParagraph($text)
    {
        /** 锁定标签 */
        $text = self::lockHTML($text);
        
        /** 重置计数器 */
        self::$_uniqueId = 0;
        self::$_blocks = array();
    
        /** 将已有的段落后面的换行处理掉 */
        $text = preg_replace(array("/<\/p>\s+<p(\s*)/is", "/\s*<br\s*\/?>\s*/is"), array("</p><p\\1", "<br />"), trim($text));
        
        /** 将所有非自闭合标签解析为唯一的字符串 */
        $foundTagCount = 0;
        $textLength = strlen($text);
        $uniqueIdList = array();
        
        if (preg_match_all("/<\/\s*([a-z0-9]+)>/is", $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $key => $match) {
                $tag = $matches[1][$key][0];
                
                $leftOffset = $match[1] - $textLength;
                $posSingle = strrpos($text, '<' . $tag . '>', $leftOffset);
                $posFix = strrpos($text, '<' . $tag . ' ', $leftOffset);
                $pos = false;
                
                switch (true) {
                    case (false !== $posSingle && false !== $posFix):
                        $pos = max($posSingle, $posFix);
                        break;
                    case false === $posSingle && false !== $posFix:
                        $pos = $posFix;
                        break;
                    case false !== $posSingle && false === $posFix:
                        $pos = $posSingle;
                        break;
                    default:
                        break;
                }
                
                if (false !== $pos) {
                    $uniqueId = self::makeUniqueId();
                    $uniqueIdList[$uniqueId] = $tag;
                    $tagLength = strlen($tag);
                    
                    $text = substr_replace($text, $uniqueId, $pos + 1 + $tagLength, 0);
                    $text = substr_replace($text, $uniqueId, $match[1] + 7 + $foundTagCount * 10 + $tagLength, 0); // 7 = 5 + 2
                    $foundTagCount ++;
                }
            }
        }
        
        foreach ($uniqueIdList as $uniqueId => $tag) {
            $text = preg_replace_callback("/<({$tag})({$uniqueId})([^>]*)>(.*)<\/\\1\\2>/is",
                array('Byends_Paragraph', 'replaceBlockCallback'), $text, 1);
        }
        
        $text = self::cutByBlock($text);
        $blocks = array_reverse(self::$_blocks);
        
        foreach ($blocks as $blockKey => $blockValue) {
            $text = str_replace($blockKey, $blockValue, $text);
        }
        
        $text = self::fixPragraph($text);
        
        /** 释放标签 */
        return self::releaseHTML($text);
    }
    
    /**
     * 生成随机字符串
     *
     * @access public
     * @param integer $length 字符串长度
     * @param string $specialChars 是否有特殊字符
     * @return string
     */
    public static function randString($length, $specialChars = false)
    {
    	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    	if ($specialChars) {
    		$chars .= '!@#$%^&*()';
    	}
    
    	$result = '';
    	$max = strlen($chars) - 1;
    	for ($i = 0; $i < $length; $i++) {
    		$result .= $chars[rand(0, $max)];
    	}
    	return $result;
    }
    
    /**
     * 对字符串进行hash加密
     *
     * @access public
     * @param string $string 需要hash的字符串
     * @param string $salt 扰码
     * @return string
     */
    public static function hash($string, $salt = NULL)
    {
    	/** 生成随机字符串 */
    	$salt = empty($salt) ? self::randString(9) : $salt;
    	$length = strlen($string);
    	$hash = '';
    	$last = ord($string[$length - 1]);
    	$pos = 0;
    
    	/** 判断扰码长度 */
    	if (strlen($salt) != 9) {
    		/** 如果不是9直接返回 */
    		return;
    	}
    
    	while ($pos < $length) {
    		$asc = ord($string[$pos]);
    		$last = ($last * ord($salt[($last % $asc) % 9]) + $asc) % 95 + 32;
    		$hash .= chr($last);
    		$pos ++;
    	}
    
    	return '$T$' . $salt . md5($hash);
    }
    
    /**
     * 判断hash值是否相等
     *
     * @access public
     * @param string $from 源字符串
     * @param string $to 目标字符串
     * @return boolean
     */
    public static function hashValidate($from, $to)
    {
    	if ('$T$' == substr($to, 0, 3)) {
    		$salt = substr($to, 3, 9);
    		return self::hash($from, $salt) == $to;
    	} else {
    		return md5($from) == $to;
    	}
    }

    /**
     * 锁定标签回调函数
     *
     * @access private
     * @param array $matches 匹配的值
     * @return string
     */
    public static function __lockHTML(array $matches)
    {
    	$guid = '<code>' . uniqid(time()) . '</code>';
    	self::$_lockedBlocks[$guid] = $matches[0];
    	return $guid;
    }
    
    /**
     * 生成缩略名
     *
     * @access public
     * @param string $str 需要生成缩略名的字符串
     * @param string $default 默认的缩略名
     * @param integer $maxLength 缩略名最大长度
     * @return string
     */
    public static function slugName($str, $default = NULL, $maxLength = 200)
    {
    	$str = str_replace(array("'", ":", "\\", "/", '"'), "", $str);
    	$str = str_replace(array("+", ",", ' ', '，', ' ', ".", "?", "=", "&", "!", "<", ">", "(", ")", "[", "]", "{", "}"), "-", $str);
    	$str = trim($str, '-');
    	$str = empty($str) ? $default : $str;
    
    	return function_exists('mb_get_info') ? mb_strimwidth($str, 0, 128, '', self::$charset) : substr($str, 0, $maxLength);
    }
    
    /**
     * 抽取多维数组的某个元素,组成一个新数组,使这个数组变成一个扁平数组
     * 使用方法:
     * <code>
     * <?php
     * $fruit = array(array('apple' => 2, 'banana' => 3), array('apple' => 10, 'banana' => 12));
     * $banana = Byends_Paragraph::arrayFlatten($fruit, 'banana');
     * print_r($banana);
     * //outputs: array(0 => 3, 1 => 12);
     * ?>
     * </code>
     *
     * @access public
     * @param array $value 被处理的数组
     * @param string $key 需要抽取的键值
     * @return array
     */
    public static function arrayFlatten(array $value, $key)
    {
    	$result = array();
    
    	if ($value) {
    		foreach ($value as $inval) {
    			if (is_array($inval) && isset($inval[$key])) {
    				$result[] = $inval[$key];
    			} else {
    				break;
    			}
    		}
    	}
    
    	return $result;
    }
    
    /**
     * 递归去掉数组反斜线
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public static function stripslashesDeep($value)
    {
    	return is_array($value) ? array_map(array('Byends_Paragraph', 'stripslashesDeep'), $value) : stripslashes($value);
    }
    
    /**
     * 递归去掉数组多余空格
     *
     * @access public
     * @param mixed $value
     * @return mixed
     */
    public static function trimDeep($value)
    {
    	return is_array($value) ? array_map(array('Byends_Paragraph', 'trimDeep'), $value) : trim($value);
    }
    
    /**
     * 去掉html中的分段
     *
     * @access public
     * @param string $html 输入串
     * @return string
     */
    public static function removeParagraph($html)
    {
    	/** 锁定标签 */
    	$html = self::lockHTML($html);
    	$html = str_replace(array("\r", "\n"), '', $html);
    
    	$html = trim(preg_replace(
    			array("/\s*<p>(.*?)<\/p>\s*/is", "/\s*<br\s*\/>\s*/is",
    					"/\s*<(div|blockquote|pre|code|script|table|fieldset|ol|ul|dl|h[1-6])([^>]*)>/is",
    					"/<\/(div|blockquote|pre|code|script|table|fieldset|ol|ul|dl|h[1-6])>\s*/is", "/\s*<\!--more-->\s*/is"),
    			array("\n\\1\n", "\n", "\n\n<\\1\\2>", "</\\1>\n\n", "\n\n<!--more-->\n\n"),
    			$html));
    
    	return trim(self::releaseHTML($html));
    }
    
    /**
     * 根据parse_url的结果重新组合url
     *
     * @access public
     * @param array $params 解析后的参数
     * @return string
     */
    public static function buildUrl($params)
    {
    	return (isset($params['scheme']) ? $params['scheme'] . '://' : NULL)
    	. (isset($params['user']) ? $params['user'] . (isset($params['pass']) ? ':' . $params['pass'] : NULL) . '@' : NULL)
    	. (isset($params['host']) ? $params['host'] : NULL)
    	. (isset($params['port']) ? ':' . $params['port'] : NULL)
    	. (isset($params['path']) ? $params['path'] : NULL)
    	. (isset($params['query']) ? '?' . $params['query'] : NULL)
    	. (isset($params['fragment']) ? '#' . $params['fragment'] : NULL);
    }
    
    /**
     * 根据count数目来输出字符
     * <code>
     * echo splitByCount(20, 10, 20, 30, 40, 50);
     * </code>
     *
     * @access public
     * @return string
     */
    public static function splitByCount($count)
    {
    	$sizes = func_get_args();
    	array_shift($sizes);
    
    	foreach ($sizes as $size) {
    		if ($count < $size) {
    			return $size;
    		}
    	}
    
    	return 0;
    }
    
    /**
     * 自闭合html修复函数
     * 使用方法:
     * <code>
     * $input = '这是一段被截断的html文本<a href="#"';
     * echo Typecho_Common::fixHtml($input);
     * //output: 这是一段被截断的html文本
     * </code>
     *
     * @access public
     * @param string $string 需要修复处理的字符串
     * @return string
     */
    public static function fixHtml($string)
    {
    	//关闭自闭合标签
    	$startPos = strrpos($string, "<");
    
    	if (false == $startPos) {
    		return $string;
    	}
    
    	$trimString = substr($string, $startPos);
    
    	if (false === strpos($trimString, ">")) {
    		$string = substr($string, 0, $startPos);
    	}
    
    	//非自闭合html标签列表
    	preg_match_all("/<([_0-9a-zA-Z-\:]+)\s*([^>]*)>/is", $string, $startTags);
    	preg_match_all("/<\/([_0-9a-zA-Z-\:]+)>/is", $string, $closeTags);
    
    	if (!empty($startTags[1]) && is_array($startTags[1])) {
    		krsort($startTags[1]);
    		$closeTagsIsArray = is_array($closeTags[1]);
    		foreach ($startTags[1] as $key => $tag) {
    			$attrLength = strlen($startTags[2][$key]);
    			if ($attrLength > 0 && "/" == trim($startTags[2][$key][$attrLength - 1])) {
    				continue;
    			}
    			if (!empty($closeTags[1]) && $closeTagsIsArray) {
    				if (false !== ($index = array_search($tag, $closeTags[1]))) {
    					unset($closeTags[1][$index]);
    					continue;
    				}
    			}
    			$string .= "</{$tag}>";
    		}
    	}
    
    	return preg_replace("/\<br\s*\/\>\s*\<\/p\>/is", '</p>', $string);
    }
    
    /**
     * 去掉字符串中的html标签
     * 使用方法:
     * <code>
     * $input = '<a href="http://test/test.php" title="example">hello</a>';
     * $output = Typecho_Common::stripTags($input, <a href="">);
     * echo $output;
     * //display: '<a href="http://test/test.php">hello</a>'
     * </code>
     *
     * @access public
     * @param string $string 需要处理的字符串
     * @param string $allowableTags 需要忽略的html标签
     * @return string
     */
    public static function stripTags($html, $allowableTags = NULL)
    {
    	if (!empty($allowableTags) && preg_match_all("/\<([a-z]+)([^>]*)\>/is", $allowableTags, $tags)) {
    		self::$_allowableTags = '|' . implode('|', $tags[1]) . '|';
    
    		if (in_array('code', $tags[1])) {
    			$html = self::lockHTML($html);
    		}
    
    		$normalizeTags = '<' . implode('><', $tags[1]) . '>';
    		$html = strip_tags($html, $normalizeTags);
    		$attributes = array_map('trim', $tags[2]);
    
    		$allowableAttributes = array();
    		foreach ($attributes as $key => $val) {
    			$allowableAttributes[$tags[1][$key]] = array_keys(self::__parseAtttrs($val));
    		}
    
    		self::$_allowableAttributes = $allowableAttributes;
    
    		$len = strlen($html);
    		$tag = '';
    		$attrs = '';
    		$pos = -1;
    		$quote = '';
    		$start = 0;
    
    		for ($i = 0;  $i < $len; $i ++) {
    			if ('<' == $html[$i] && -1 == $pos) {
    				$start = $i;
    				$pos = 0;
    			} else if (0 == $pos && '/' == $html[$i] && empty($tag)) {
    				$pos = -1;
    			} else if (0 == $pos && ctype_alpha($html[$i])) {
    				$tag .= $html[$i];
    			} else if (0 == $pos && ctype_space($html[$i])) {
    				$pos = 1;
    			} else if (1 == $pos && (!empty($quote) || '>' != $html[$i])) {
    				if (empty($quote) && ('"' == $html[$i] || "'" == $html[$i])) {
    					$quote = $html[$i];
    				} else if (!empty($quote) && $quote == $html[$i]) {
    					$quote = '';
    				}
    
    				$attrs .= $html[$i];
    			} else if (-1 != $pos && empty($quote) && '>' == $html[$i]) {
    				$out = self::__tagFilter($tag, $attrs);
    				$outLen = strlen($out);
    				$nextStart = $start + $outLen;
    
    				$tag = '';
    				$attrs = '';
    				$html = substr_replace($html, $out, $start, $i - $start + 1);
    				$len  = strlen($html);
    				$i = $nextStart - 1;
    
    				$pos = -1;
    			}
    		}
    
    		$html = preg_replace_callback("/<\/([_0-9a-z-]+)>/is", array('Typecho_Common', '__closeTagFilter'), $html);
    		$html = self::releaseHTML($html);
    	} else {
    		$html = strip_tags($html);
    	}
    
    	//去掉注释
    	return preg_replace("/<\!\-\-[^>]*\-\->/s", '', $html);
    }
    
    /**
     * 过滤用于搜索的字符串
     *
     * @access public
     * @param string $query 搜索字符串
     * @return string
     */
    public static function filterSearchQuery($query)
    {
    	return str_replace(array('%', '?', '*', '/', '{', '}'), '', $query);
    }
    
    /**
     * 将url中的非法字符串过滤
     *
     * @access private
     * @param string $string 需要过滤的url
     * @return string
     */
    public static function safeUrl($url)
    {
    	//~ 针对location的xss过滤, 因为其特殊性无法使用removeXSS函数
    	//~ fix issue 66
    	$params = parse_url(str_replace(array("\r", "\n"), '', $url));
    
    	/** 禁止非法的协议跳转 */
    	if (isset($params['scheme'])) {
    		if (!in_array($params['scheme'], array('http', 'https'))) {
    			return;
    		}
    	}
    
    	/** 过滤解析串 */
    	$params = array_map(array('Byends_Paragraph', '__removeUrlXss'), $params);
    	return self::buildUrl($params);
    }
    
    /**
     * 将url中的非法xss去掉时的数组回调过滤函数
     *
     * @access private
     * @param string $string 需要过滤的字符串
     * @return string
     */
    public static function __removeUrlXss($string)
    {
    	$string = str_replace(array('%0d', '%0a'), '', strip_tags($string));
    	return preg_replace(array(
    			"/\(\s*(\"|')/i",           //函数开头
    			"/(\"|')\s*\)/i",           //函数结尾
    	), '', $string);
    }
    
    /**
     * 处理XSS跨站攻击的过滤函数
     *
     * @author kallahar@kallahar.com
     * @link http://kallahar.com/smallprojects/php_xss_filter_function.php
     * @access public
     * @param string $val 需要处理的字符串
     * @return string
     */
    public static function removeXSS($val)
    {
    	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    	// this prevents some character re-spacing such as <java\0script>
    	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    	$val = preg_replace('/([\x00-\x08]|[\x0b-\x0c]|[\x0e-\x19])/', '', $val);
    
    	// straight replacements, the user should never need these since they're normal characters
    	// this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    	$search = 'abcdefghijklmnopqrstuvwxyz';
    	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$search .= '1234567890!@#$%^&*()';
    	$search .= '~`";:?+/={}[]-_|\'\\';
    
    	for ($i = 0; $i < strlen($search); $i++) {
    		// ;? matches the ;, which is optional
    		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
    
    		// &#x0040 @ search for the hex values
    		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
    		// &#00064 @ 0{0,7} matches '0' zero to seven times
    		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
    	}
    
    	// now the only remaining whitespace attacks are \t, \n, and \r
    	$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    	$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    	$ra = array_merge($ra1, $ra2);
    
    	$found = true; // keep replacing as long as the previous round replaced something
    	while ($found == true) {
    		$val_before = $val;
    		for ($i = 0; $i < sizeof($ra); $i++) {
    			$pattern = '/';
    			for ($j = 0; $j < strlen($ra[$i]); $j++) {
    				if ($j > 0) {
    					$pattern .= '(';
    					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
    					$pattern .= '|';
    					$pattern .= '|(&#0{0,8}([9|10|13]);)';
    					$pattern .= ')*';
    				}
    				$pattern .= $ra[$i][$j];
    			}
    			$pattern .= '/i';
    			$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
    			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
    
    			if ($val_before == $val) {
    				// no replacements were made, so exit the loop
    				$found = false;
    			}
    		}
    	}
    
    	return $val;
    }
    
    
    /**
     * 锁定标签
     *
     * @access public
     * @param string $html 输入串
     * @return string
     */
    public static function lockHTML($html)
    {
    	return preg_replace_callback("/<(code|pre|script)[^>]*>.*?<\/\\1>/is", array('Byends_Paragraph', '__lockHTML'), $html);
    }
    
    /**
     * 释放标签
     *
     * @access public
     * @param string $html 输入串
     * @return string
     */
    public static function releaseHTML($html)
    {
    	$html = trim(str_replace(array_keys(self::$_lockedBlocks), array_values(self::$_lockedBlocks), $html));
    	self::$_lockedBlocks = array('<p></p>' => '');
    	return $html;
    }
    
    /**
     * 截取字符串
     * @param string $str
     * @param integer $start
     * @param integer $length
     * @param string $trim
     * @return string
     */
    public static function subStr($str, $start, $length, $trim = "...")
    {
    	if (function_exists('mb_get_info')) {
    		$iLength = mb_strlen($str, self::$charset);
    		$str = mb_substr($str, $start, $length, self::$charset);
    		return ($length < $iLength - $start) ? $str . $trim : $str;
    	} else {
    		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info);
    		$str = join("", array_slice($info[0], $start, $length));
    		return ($length < (sizeof($info[0]) - $start)) ? $str . $trim : $str;
    	}
    }
    
    /**
     * 宽字符串截字函数
     *
     * @access public
     * @param string $str 需要截取的字符串
     * @param integer $start 开始截取的位置
     * @param integer $length 需要截取的长度
     * @param string $trim 截取后的截断标示符
     * @return string
     */
    public static function bigSubStr($str, $start, $length, $trim = "...") {
    	if (function_exists ( 'mb_get_info' )) {
    		$iLength = mb_strlen ( $str, 'UTF-8' );
    		$str = mb_substr ( $str, $start, $length, 'UTF-8' );
    		return ($length < $iLength - $start) ? $str . $trim : $str;
    	} else {
    		preg_match_all ( "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info );
    		$str = join ( "", array_slice ( $info [0], $start, $length ) );
    		return ($length < (sizeof ( $info [0] ) - $start)) ? $str . $trim : $str;
    	}
    }
    
    /**
     * 获取宽字符串长度函数
     *
     * @access public
     * @param string $str 需要获取长度的字符串
     * @return integer
     */
    public static function strLen($str)
    {
    	if (function_exists('mb_get_info')) {
    		return mb_strlen($str, self::$charset);
    	} else {
    		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info);
    		return sizeof($info[0]);
    	}
    }
    
    /**
     * 过滤HTML标签
     * strip the text from an html document
     * @param string $document
     */
    public static function stripBrief($document)
    {
    
    	// I didn't use preg eval (//e) since that is only available in PHP 4.0.
    	// so, list your entities one by one here. I included some of the
    	// more common ones.
    
    	$search = array("'<script[^>]*?>.*?</script>'si",	// strip out javascript
    			"'<style[^>]*?>.*?</style>'si",	    // strip out style
    			"/<\!\-\-[^>]*\-\->/s",				// strip out remark
    			"'<[\/\!]*?[^<>]*?>'si",			// strip out html tags
    			"'([\r\n])[\s]+'",					// strip out white space
    			"'&(quot|#34|#034|#x22);'i",		// replace html entities
    			"'&(amp|#38|#038|#x26);'i",			// added hexadecimal values
    			"'&(lt|#60|#060|#x3c);'i",
    			"'&(gt|#62|#062|#x3e);'i",
    			"'&(nbsp|#160|#xa0);'i",
    			"'&(iexcl|#161);'i",
    			"'&(cent|#162);'i",
    			"'&(pound|#163);'i",
    			"'&(copy|#169);'i",
    			"'&(reg|#174);'i",
    			"'&(deg|#176);'i",
    			"'&(#39|#039|#x27);'",
    			"'&(euro|#8364);'i",				// europe
    			"'&a(uml|UML);'",					// german
    			"'&o(uml|UML);'",
    			"'&u(uml|UML);'",
    			"'&A(uml|UML);'",
    			"'&O(uml|UML);'",
    			"'&U(uml|UML);'",
    			"'&szlig;'i",
    	);
    	$replace = array(	"",
    			"",
    			"",
    			"",
    			"\\1",
    			"\"",
    			"&",
    			"<",
    			">",
    			" ",
    			chr(161),
    			chr(162),
    			chr(163),
    			chr(169),
    			chr(174),
    			chr(176),
    			chr(39),
    			chr(128),
    			"�",
    			"�",
    			"�",
    			"�",
    			"�",
    			"�",
    			"�",
    	);
    		
    	$text = preg_replace($search,$replace,$document);
    
    	return $text;
    }
    
    /**
     * 将路径转化为链接
     *
     * @access public
     * @param string $path 路径
     * @param string $prefix 前缀
     * @return string
     */
    public static function url($path, $prefix)
    {
    	$path = (0 === strpos($path, './')) ? substr($path, 2) : $path;
    	return rtrim($prefix, '/') . '/' . str_replace('//', '/', ltrim($path, '/'));
    }
    
    /**
     * 获取 真实 IP 地址
     *
     * @access public
     * @return string
     */
    public static function getIP() {
    	if (isset ( $_SERVER ["HTTP_X_FORWARDED_FOR"] )) {
    		return $_SERVER ["HTTP_X_FORWARDED_FOR"];
    	} elseif (isset ( $_SERVER ["HTTP_X_REAL_IP"] )) {
    		return $_SERVER ["HTTP_X_REAL_IP"];
    	}
    	return $_SERVER ["REMOTE_ADDR"];
    }
    
    /**
     * 编码转换,返回指定的字符
     *
     * @access public
     * @param int $num
     * @return string
     */
    public static function code2utf($num) {
    	if ($num < 128)
    		return chr ( $num );
    	if ($num < 2048)
    		return chr ( ($num >> 6) + 192 ) . chr ( ($num & 63) + 128 );
    	if ($num < 65536)
    		return chr ( ($num >> 12) + 224 ) . chr ( (($num >> 6) & 63) + 128 ) . chr ( ($num & 63) + 128 );
    	if ($num < 2097152)
    		return chr ( ($num >> 18) + 240 ) . chr ( (($num >> 12) & 63) + 128 ) . chr ( (($num >> 6) & 63) + 128 ) . chr ( ($num & 63) + 128 );
    	return '';
    }
    
    /**
     * js escape php 实现
     * @param $string           the sting want to be escaped
     * @param $in_encoding
     * @param $out_encoding
     */
    public static function escape($string, $in_encoding = 'UTF-8', $out_encoding = 'UCS-2') {
    	$return = '';
    	if (function_exists('mb_get_info')) {
    		for($x = 0; $x < mb_strlen ( $string, $in_encoding ); $x ++) {
    			$str = mb_substr ( $string, $x, 1, $in_encoding );
    			if (strlen ( $str ) > 1) { // 多字节字符
    				$return .= '%u' . strtoupper ( bin2hex ( mb_convert_encoding ( $str, $out_encoding, $in_encoding ) ) );
    			} else {
    				$return .= '%' . strtoupper ( bin2hex ( $str ) );
    			}
    		}
    	}
    	return $return;
    }
    
    /**
     * 解释JS escape 字符串
     *
     * @access public
     * @param string $str
     * @return string
     */
    public static function unescape($str) {
    	$decodedStr = '';
    	$pos = 0;
    	$len = strlen ( $str );
    	while ( $pos < $len ) {
    		$charAt = substr ( $str, $pos, 1 );
    		if ($charAt == '%') {
    			$pos ++;
    			$charAt = substr ( $str, $pos, 1 );
    			if ($charAt == 'u') {
    				//unicode character
    				$pos ++;
    				$unicodeHexVal = substr ( $str, $pos, 4 );
    				$unicode = hexdec ( $unicodeHexVal );
    				$decodedStr .= iconv ( "UTF-8", 'gb18030', self::code2utf ( $unicode ) );
    				$pos += 4;
    			} else {
    				//escaped ascii character
    				$hexVal = substr ( $str, $pos, 2 );
    				$decodedStr .= chr ( hexdec ( $hexVal ) );
    				$pos += 2;
    			}
    		} else {
    			$decodedStr .= $charAt;
    			$pos ++;
    		}
    	}
    	return $decodedStr;
    }
    
    /**
     * 读取文件中的各行内容
     * @param $string $fileName
     * @return array
     */
    public static function getLinesFromFile($fileName) {
	    if (!$fileHandle = fopen($fileName, 'r')) {
	        return;
	    }
	 
	    $lines = array();
	    while (false !== $line = fgets($fileHandle)) {
	        $lines[] = $line;
	    }
	 
	    fclose($fileHandle);
	 
	    return $lines;
	}
}
