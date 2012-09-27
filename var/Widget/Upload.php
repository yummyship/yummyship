<?php
/**
 * 上传组件
 *
 * @author BYENDS (byends@gmail.com)
 * @package Widget_Upload
 * @copyright  Copyright (c) 2012 Byends (http://www.byends.com)
 */
class Widget_Upload extends Byends_Widget
{
	private $imageType = array('gif', 'jpg', 'jpeg', 'png', 'tiff', 'bmp');
	private $mediaType = array('mp3', 'wmv', 'wma', 'rmvb', 'rm', 'avi', 'flv');
	private $docType   = array('txt', 'doc', 'docx', 'xls', 'xlsx', 
								'ppt', 'pptx', 'zip', 'rar', 'pdf');
	private $uploadType = 'steps';
	private $status = false;
	
	/**
	 * 单例句柄
	 *
	 * @access private
	 * @var Widget_Upload
	 */
	private static $_instance = null;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 获取单例句柄
	 *
	 * @access public
	 * @return Widget_Upload
	 */
	public static function getInstance()
	{
		if (null === self::$_instance) {
			self::$_instance = new Widget_Upload();
		}
	
		return self::$_instance;
	}
	
	/**
	 * 上传文件
	 * return array
	 */
	public function upload()
	{
		$result = array(
			'status' => 1,
			'data'   => 'There is no image uploaded.'
		);
		if (!empty($_FILES)) {
			$file = array_pop($_FILES);
			if (0 == $file['error'] && is_uploaded_file($file['tmp_name'])) {
				$data = $this->uploadHandle($file);
				$result['status'] = $this->status ? 0 : 1;
				$result['data'] = $data;
			}
		}
		elseif ($url = $this->request->filter('trim')->url) {
			$data = $this->fetch($url);
			$result['status'] = $this->status ? 0 : 1;
			$result['data'] = $data;
		}
		
		return $result;
	}
 	
	/**
	 * 设置上传的类型
	 * @param string $type
	 * @return Widget_Upload
	 */
	public function setUploadType($type) 
	{
		if ($type) {
			$this->uploadType = $type;
		}
		
		return $this;
	}
	
	/**
     * 上传文件处理函数
	 * @param source $file
	 * @return string|boolean
	 */
	public function uploadHandle($file)
	{
		if (empty($file['name'])) {
			return 'There is no image uploaded.';
		}
		
		//获取扩展名
		$ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));
		
		if (!in_array($ext, $this->imageType)) {
			return 'Does not support this image format.';
		}
		
		$hash = md5_file($file['tmp_name']);
		if ($this->uploadType == 'cover' && $this->checkCoverExists($hash)) {
			return 'The Cover image is exists yet.';
		}
		
		$uniqueArr = $this->uniqueStr($hash);
		
		foreach ($uniqueArr as $v) {
			$file['name'] = $v;
			$fileName = $file['name'] . '.' . $ext;
			if (!$this->checkFileExists(__BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__, $fileName)) {
				break;
			}
		}
		
		$path =__BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__ . $fileName;
		
		//移动上传文件
		if (!$this->mkdirr(dirname($path)) || !move_uploaded_file($file['tmp_name'], $path)) {
			return 'Move the image Failed.';
		}
	
		if (!isset($file['size'])) {
			$file['size'] = filesize($path);
		}
		
		$this->status = true;
		
		return array(
				'permanlink' => BYENDS_TEMPS_STATIC_URL.$fileName,
				'fileName' => $fileName,
				'size' => $file['size'],
				'uploadType' => $this->uploadType,
				'hash' => $hash
		);
	}
	
	/**
	 * 抓取远程图片
	 * @param string $url
	 * @param string $referer
	 * @param string $target
	 * @return boolean
	 */
	public function fetch($url, $referer = null)
	{
		if (!$this->mkdirr(dirname(__BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__))) {
			return 'Make dir failed.';
		}
		
		$validate = new Byends_Validate();
		if (!$validate->url($url)) {
			return 'The url incorrect.';
		}
		
		$referer = $referer ? $referer : $url;
		$fileName = basename($url);
		
		if( !preg_match('/\.(png|gif|jpg|jpeg)$/i', $fileName) ) {
			$ext = 'jpg';
		}
		else {
			$ext = strtolower(substr($fileName, strrpos($fileName, '.') + 1));
		}
		
		$uniqueArr = $this->uniqueStr(md5($url));
		
		foreach ($uniqueArr as $v) {
			$target = $v;
			$fileName = $target . '.' . $ext;
			if (!$this->checkFileExists(__BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__, $fileName)) {
				break;
			}
		}
		
		$target = __BYENDS_ROOT_DIR__.__BYENDS_TEMPS_DIR__.$fileName;
		
		// Open the target file for writing
		$fpLocal = @fopen( $target, 'w' );
		if( !$fpLocal ) {
			return 'Fetch image failed.';
		}
	
		// Use cURL to download if available
		if( is_callable('curl_init') ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_REFERER, $referer );
			curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_FILE, $fpLocal );
			if( !curl_exec($ch) ) {
				fclose( $fpLocal );
				curl_close( $ch );
				return 'Fetch image failed.';
			}
			curl_close( $ch );
		}
		// Otherwise use fopen
		else {
			$opts = array(
					'http' => array(
							'method' => "GET",
							'header' => "Referer: $referer\r\n"
					)
			);
	
			$context = stream_context_create( $opts );
			$fpRemote = @fopen( $url, 'r', false, $context );
			if( !$fpRemote ) {
				fclose( $fpLocal );
				return 'Fetch image failed.';
			}
	
			while( !feof( $fpRemote ) ) {
				fwrite( $fpLocal, fread($fpRemote, 8192) );
			}
			fclose( $fpRemote );
		}
	
		fclose( $fpLocal );
		$hash = md5_file($target);
		if ($this->uploadType == 'cover' && $this->checkCoverExists($hash)) {
			@unlink($target);
			return 'duplicate-image';
		}
		
		$this->status = true;
		
		return array(
				'permanlink' => BYENDS_TEMPS_STATIC_URL.$fileName,
				'fileName' => $fileName,
				'size' => filesize($target),
				'uploadType' => $this->uploadType,
				'hash' => $hash
		);
	}
	
	/**
	 * 生成缩略图
	 * @param string $imgPath
	 * @param string $thumbPath
	 * @param string $thumbWidth
	 * @param string $thumbHeight
	 * @param integer $quality
	 * @return boolean
	 */
	public function createThumb($imgPath, $thumbPath, $thumbWidth, $thumbHeight, $quality, $cropType)
	{
		// Get image type and size and check if we can handle it
		list( $srcWidth, $srcHeight, $type ) = getimagesize( $imgPath );
		if(
				$srcWidth < 1 //|| $srcWidth > 4096
				|| $srcHeight < 1 //|| $srcHeight > 4096
		) {
			return false;
		}
	
		switch( $type ) {
			case IMAGETYPE_JPEG: $imgCreate = 'ImageCreateFromJPEG'; break;
			case IMAGETYPE_GIF: $imgCreate = 'ImageCreateFromGIF'; break;
			case IMAGETYPE_PNG: $imgCreate = 'ImageCreateFromPNG'; break;
			default: return false;
		}
	
		// Crop the image horizontal or vertical
		$srcX = 0;
		$srcY = 0;
		
		//中间裁剪
		if($cropType) {
			if( ( $srcWidth/$srcHeight ) > ( $thumbWidth/$thumbHeight ) ) {
				$zoom = ($srcWidth/$srcHeight) / ($thumbWidth/$thumbHeight);
				$srcX = ($srcWidth - $srcWidth / $zoom) / 2;
				$srcWidth = $srcWidth / $zoom;
			}
			else {
				$zoom = ($thumbWidth/$thumbHeight) / ($srcWidth/$srcHeight);
				$srcY = ($srcHeight - $srcHeight / $zoom) / 2;
				$srcHeight = $srcHeight / $zoom;
			}
		}
		//缩略图
		else {
			// 计算缩放比例
			//$scale = min($thumbWidth/$srcWidth, $thumbHeight/$srcHeight);
			//改为以宽度缩放
			$scale = $thumbWidth/$srcWidth;
				
			if($scale >= 1)
			{
				// 超过原图大小不再缩略
				$thumbWidth   =  $srcWidth;
				$thumbHeight  =  $srcHeight;
			}
			else
			{
				// 缩略图尺寸
				$thumbWidth  = (int)($srcWidth*$scale);
				$thumbHeight = (int)($srcHeight*$scale);
			}
				
			$thumbZoom = $thumbWidth/$thumbHeight;
			$srcZoom = $srcWidth/$srcHeight;
			if($srcZoom >= $thumbZoom)
			{
				$srcX = ($srcWidth - ($thumbZoom * $srcHeight)) / 2;
				$thumbWidth = ($thumbHeight * $srcWidth) / $srcHeight;
			}
			else
			{
				$srcY = ($srcHeight - ( (1 / $thumbZoom) * $srcWidth)) / 2;
				$thumbHeight = ($thumbWidth * $srcHeight) / $srcWidth;
			}
		}
	
		// Resample and create the thumbnail
		$thumb = $type == IMAGETYPE_GIF ? imagecreate($thumbWidth, $thumbHeight) : imageCreateTrueColor($thumbWidth, $thumbHeight);
		$orig = $imgCreate( $imgPath );
		imageCopyResampled( $thumb, $orig, 0, 0, $srcX, $srcY, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight );
		imagejpeg( $thumb, $thumbPath, $quality );
	
		imageDestroy( $thumb );
		imageDestroy( $orig );
		return true;
	}
	
	/**
	 * 检测封面是否已经存在
	 * @param string $coverHash
	 * @return boolean
	 */
	public function checkCoverExists($coverHash) 
	{
		$c = $this->db->query('SELECT cid FROM '.BYENDS_TABLE_CONTENTS.' WHERE coverHash = :1', $coverHash);
		
		return $c ? true : false;
	}
	
	/**
	 * 检测文件是否已经存在
	 * @param string $dir
	 * @param string $fileName
	 * @return boolean
	 */
	public function checkFileExists($dir, $fileName)
	{
		$path = $dir . $fileName;
		return file_exists($path) ? true : false;
	}
	
	/**
	 * 获取唯一的文件名称
	 * @param string $directory
	 * @param string $initialName
	 * @return string
	 */
	public function getUniqueFileName($directory, $initialName)
	{
		list($newName, $imageSuffix) = explode('.', $initialName);
		$path = $directory .'/'. $initialName;
	
		// Do we already have a file with this name -> Add a numerical prefix
		for( $i = 1; file_exists($path); $i++ ) {
			$newName = md5($newName.'_'.$i);
			$path = $directory .'/'. $newName.'.'.$imageSuffix;
		}
	
		return $newName.'.'.$imageSuffix;
	}
	
	/**
	 * 递归生成多层目录
	 */
	public function mkdirr($pathname)
	{
		if( empty($pathname) || is_dir($pathname) ) {
			return true;
		}
		if ( is_file($pathname) ) {
			return false;
		}
	
		$nextPathname = substr( $pathname, 0, strrpos( $pathname, '/' ) );
		if( $this->mkdirr( $nextPathname ) ) {
			if( !file_exists( $pathname ) ) {
				$oldUmask = umask(0);
				$success = @mkdir( $pathname, 0777 );
				umask( $oldUmask );
				return $success;
			}
		}
		return false;
	}
	
	public function uniqueStr($hex)
	{
		//用于生成唯一串的字符 （个数与 0x0000003D 对应）
		$base = array(
				'a','b','c','d','e','f','g','h',
				'i','j','k','l','m','n','o','p',
				'q','r','s','t','u','v','w','x',
				'y','z',/*'0','1','2','3','4','5',
				'6','7','8','9',*/'A','B','C','D',
				'E','F','G','H','I','J','K','L',
				'M','N','O','P','Q','R','S','T',
				'U','V','W','X','Y','Z'
		);
	
		//$hex = md5_file($filename);
		$hexLen = strlen($hex);
		$subHexLen = $hexLen / 8;
		$output = array();
	
		for ($i = 0; $i < $subHexLen; $i++) {
			$subHex = substr ($hex, $i * 8, 8);
			//把md5字符按照8位一组16进制与0x3FFFFFFF进行位与运算
			$int = 0x3FFFFFFF & (1 * ('0x'.$subHex));
			$out = '';
			for ($j = 0; $j < 5; $j++) {
				//把得到的值与0x0000003D进行位与运算，取得字符数组$base索引
				$val = 0x00000033 & $int;
				$out .= $base[$val];
				//每次循环按位右移6位
				$int = $int >> 6;
			}
			$output[] = $out;
		}
		return $output;
	}
	
}

?>