<?php
/**
 * 日期处理类
 *
 * @author BYENDS (byends@gmail.com)
 * @package Byends_Date
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Byends_Date
{
    /**
     * 期望时区偏移
     *
     * @access public
     * @var integer
     */
    public static $timezoneOffset = 0;

    /**
     * 服务器时区偏移
     *
     * @access public
     * @var integer
     */
    public static $serverTimezoneOffset = 0;

    /**
     * 当前的GMT时间戳
     *
     * @access public
     * @var integer
     */
    public static $gmtTimeStamp;

    /** 时区 */
    public static $timezoneList = array(
            "0"         => '格林威治(子午线)标准时间 (GMT)',
            "3600"      => '中欧标准时间 阿姆斯特丹,荷兰,法国 (GMT +1)',
            "7200"      => '东欧标准时间 布加勒斯特,塞浦路斯,希腊 (GMT +2)',
            "10800"     => '莫斯科时间 伊拉克,埃塞俄比亚,马达加斯加 (GMT +3)',
            "14400"     => '第比利斯时间 阿曼,毛里塔尼亚,留尼汪岛 (GMT +4)',
            "18000"     => '新德里时间 巴基斯坦,马尔代夫 (GMT +5)',
            "21600"     => '科伦坡时间 孟加拉 (GMT +6)',
            "25200"     => '曼谷雅加达 柬埔寨,苏门答腊,老挝 (GMT +7)',
            "28800"     => '北京时间 香港,新加坡,越南 (GMT +8)',
            "32400"     => '东京平壤时间 西伊里安,摩鹿加群岛 (GMT +9)',
            "36000"     => '悉尼关岛时间 塔斯马尼亚岛,新几内亚 (GMT +10)',
            "39600"     => '所罗门群岛 库页岛 (GMT +11)',
            "43200"     => '惠灵顿时间 新西兰,斐济群岛 (GMT +12)',
            "-3600"     => '佛德尔群岛 亚速尔群岛,葡属几内亚 (GMT -1)',
            "-7200"     => '大西洋中部时间 格陵兰 (GMT -2)',
            "-10800"    => '布宜诺斯艾利斯 乌拉圭,法属圭亚那 (GMT -3)',
            "-14400"    => '智利巴西 委内瑞拉,玻利维亚 (GMT -4)',
            "-18000"    => '纽约渥太华 古巴,哥伦比亚,牙买加 (GMT -5)',
            "-21600"    => '墨西哥城时间 洪都拉斯,危地马拉,哥斯达黎加 (GMT -6)',
            "-25200"    => '美国丹佛时间 (GMT -7)',
            "-28800"    => '美国旧金山时间 (GMT -8)',
            "-32400"    => '阿拉斯加时间 (GMT -9)',
            "-36000"    => '夏威夷群岛 (GMT -10)',
            "-39600"    => '东萨摩亚群岛 (GMT -11)',
            "-43200"    => '艾尼威托克岛 (GMT -12)'
	);
    
    /**
     * 可以被直接转换的时间戳
     *
     * @access public
     * @var integer
     */
    public $timeStamp = 0;

    /**
     * 构造函数,初始化参数
     *
     * @access public
     * @param integer $gmtTime GMT时间戳
     * @return void
     */
    public function __construct($gmtTime)
    {
        $this->timeStamp = $gmtTime + (self::$timezoneOffset - self::$serverTimezoneOffset);
    }

    /**
     * 设置当前期望的时区偏移
     *
     * @access public
     * @param integer $offset
     * @return void
     */
    public static function setTimezoneOffset($offset)
    {
        self::$timezoneOffset = $offset;
        self::$serverTimezoneOffset = idate('Z');
    }

    /**
     * 获取可以被直接转换的时间戳
     * @param unknown_type $gmtTime
     */
    public static function timeStamp($gmtTime)
    {
    	return $gmtTime + (self::$timezoneOffset - self::$serverTimezoneOffset);
    }

    /**
     * 获取GMT时间
     *
     * @access public
     * @return integer
     */
    public static function gmtTime($create = null)
    {
    	$gmtTime = self::$gmtTimeStamp ? self::$gmtTimeStamp : (self::$gmtTimeStamp = @gmmktime());
    	$gmtTime = $create ? $create - self::$timezoneOffset + self::$serverTimezoneOffset : $gmtTime;
        return $gmtTime;
    }
    
    /**
     * 词义化时间
     *
     * @access public
     * @param string $from 起始时间
     * @param string $now 终止时间
     * @return string
     */
    public static function dateWord($from, $now, $lang)
    {
    	if ($lang == 'en') {
    		return self::dateWordEn($from, $now);
    	}
    	$between = $now - $from;
    	
    	$second = 1;
    	$minute = 60*$second;
    	$hour = 60*$minute;
    	$day = 24*$hour;
    	$week = 7*$day;
    	$month = 30*$day;
    	$year = 365*$day;
    	
    	if ($between >= $year) {
    		$year = floor($between / $year);
    		return sprintf('%d年前', $year);
    	}
    	
    	if ($between >= $month) {
    		$month = floor($between / $month);
    		return sprintf('%d月前', $month);
    	}
    	
    	if ($between >= $week) {
    		$week = floor($between / $week);
    		return sprintf('%d星期前', $week);
    	}
    	
    	if ($between >= $day) {
    		$day = floor($between / $day);
    		return sprintf('%d天前', $day);
    	}
    	
    	if ($between >= $hour) {
    		$hour = floor($between / $hour);
    		return sprintf('%d小时前', $hour);
    	}
    	
    	if ($between >= $minute) {
    		$minute = floor($between / $minute);
    		return sprintf('%d分钟前', $minute);
    	}
    	
    	if ($between < $minute && $between > 0) {
    		$second = $between;
    		return sprintf('%d秒前', $second);
    	}
    	
    	if ($between == 0) {
    		return '刚刚';
    	}
    	
    	return date('Y-m-d', $from);
    }
    
    /**
     * 词义化时间
     *
     * @access public
     * @param string $from 起始时间
     * @param string $now 终止时间
     * @return string
     */
    public static function dateWordEn($from, $now)
    {
    	$between = $now - $from;
    	 
    	$second = 1;
    	$minute = 60*$second;
    	$hour = 60*$minute;
    	$day = 24*$hour;
    	$week = 7*$day;
    	$month = 30*$day;
    	$year = 365*$day;
    	 
    	if ($between >= $year) {
    		$year = floor($between / $year);
    		return sprintf('%d'.($year > 1 ? ' years ago' : ' year ago'), $year);
    	}
    	 
    	if ($between >= $month) {
    		$month = floor($between / $month);
    		return sprintf('%d'.($month > 1 ? ' months ago' : ' month ago'), $month);
    	}
    	 
    	if ($between >= $week) {
    		$week = floor($between / $week);
    		return sprintf('%d'.($week > 1 ? ' weeks ago' : ' week ago'), $week);
    	}
    	 
    	if ($between >= $day) {
    		$day = floor($between / $day);
    		return sprintf('%d'.($day > 1 ? ' days ago' : ' day ago'), $day);
    	}
    	 
    	if ($between >= $hour) {
    		$hour = floor($between / $hour);
    		return sprintf('%d'.($hour > 1 ? ' hours ago' : ' hour ago'), $hour);
    	}
    	 
    	if ($between >= $minute) {
    		$minute = floor($between / $minute);
    		return sprintf('%d'.($minute > 1 ? ' minutes ago' : ' minute ago'), $minute);
    	}
    	 
    	if ($between < $minute && $between > 0) {
    		$second = $between;
    		return sprintf('%d'.($second > 1 ? ' seconds ago' : ' second ago'), $second);
    	}
    	 
    	if ($between == 0) {
    		return 'Just Now';
    	}
    	 
    	return date('M d, Y', $from);
    }
    
}
