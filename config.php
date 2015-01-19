<?php
/**
 * @Author: wuyong 
 * @Desc: 单元测试配置文件
 * @Date: 2015年 01月 18日 星期日 15:25:38 CST 
 */
class Config{
	static public $sosoConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/tuan/code/backend/WEB-INF/class/',//需要测试文件所在的目录
		'UNIT_TEST_PATH' => '/search/nginx/html/tuan/code/tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/tuan/code/backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array('processor','CRON','Base','util'),//忽略所要测试的文件夹(i)
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数
	);

	static public $caseConfig = array(
		'SRC_CLASS_PATH' => '/search/nginx/html/famous_brands/backend/WEB-INF/class/',//需要测试文件所在的目录
		'UNIT_TEST_PATH' => '/search/nginx/html/famous_brands/tests/Test/',//生成的单元测试文件目录
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录
		'WEB_XML_FILE' => '/search/nginx/html/famous_brands/backend/WEB-INF/web.xml',//web.xml文件
		'SKIP_FOLDERS' => array(),//忽略所要测试的文件夹
		'SKIP_FUNCTIONS' => array('__construct','run','test'),//忽略所要测试的函数
	);

	//...etc
}
?>
