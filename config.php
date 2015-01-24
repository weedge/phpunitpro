<?php
/**
 * @Author: wuyong 
 * @Desc: 单元测试配置文件
 * @Date: 2015年 01月 18日 星期日 15:25:38 CST 
 */
class Config{

	static private $defaultConfig = array(
		'SRC_CLASS_PATH' => '',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '',//superPHP框架所在的目录
		'WEB_XML_FILE' => '',//web.xml文件
		'SKIP_FOLDERS' => array(),//忽略所要测试的文件夹(i)
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	//团购
	static private $tuanConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/tuan/code/backend/WEB-INF/class/',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '/search/nginx/html/tuan/code/tuan_tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/tuan/code/backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array('api','Auth','GenVr','Page','SmartyPlugins','processor','CRON','Base','util'),//忽略所要测试的文件夹(i)
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	//电影票
	static private $dypConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/tuan/code/dyp_backend/WEB-INF/class/',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '/search/nginx/html/tuan/code/dyp_tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/tuan/code/dyp_backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array('api','Auth','GenVr','Page','SmartyPlugins','processor','CRON','Base','util'),//忽略所要测试的文件夹(i)
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	//逛逛
	static private $maiConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/famous_brands/backend/WEB-INF/class/',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '/search/nginx/html/famous_brands/tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/famous_brands/backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array(),//忽略所要测试的文件夹
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	//无线网址导航
	static private $dh123Config = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/newdh123/backend/WEB-INF/class/',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '/search/nginx/html/newdh123/tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/newdh123/backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array(),//忽略所要测试的文件夹
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	//...etc
	//MyFrame
	static private $caseConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/myframe/class/',//需要测试文件所在的目录
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH
		'UNIT_TEST_PATH' => '/search/nginx/html/myframe/tests/Test/',//生成的单元测试文件目录
		'AUTOLOAD_FILE' => '/search/nginx/html/myframe/src/auto_load.php',//框架的auto_load文件
		'SKIP_FOLDERS' => array('CRON','Base','util'),//忽略所要测试的文件夹(i)
		'SKIP_FUNCTIONS' => array('__construct','run','test'),//忽略所要测试的函数
	);

	static public function load($app=""){
		$config = array();
		switch(strtolower(trim($app))){
		case "tuan":
			$config = self::$tuanConfig;
			break;
		case "dyp":
			$config = self::$dypConfig;
			break;
		case "mai":
			$config = self::$maiConfig;
			break;
		case "dh123":
			$config = self::$dh123Config;
			break;
		case "case":
			$config = self::$caseConfig;
			break;
		default:
			$config = self::$defaultConfig;
			break;
		}
		return $config;
	}
}
?>
