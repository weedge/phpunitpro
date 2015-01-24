###介绍：
1. 很多猿人，包括自己在内，总会出现对自己写的代码感觉良好，不习惯去些什么单元测试；  
单元测试在项目代码不断壮大的过程中，起到一个代码质量监控的作用;还有一个好处是如果人猿变动了，也能保证代码质量，逻辑不易出错；也可当做一种代码文档；如果是测试先行，那就属于设计阶段的行为了。  
2. 由于以前项目整体重构，没有足够的时间对各个单元模块的逻辑功能进行单元测试，为了保证代码质量，将单元测试补上，将整个代码逻辑覆盖倒，也为了以后代码重构时不会出现逻辑错误。  
3. 写了一个小工具(有待完善)，主要是通过配置文件配置，来遍历被测试目录下文件类中的public函数(类的设计)；生成对应指定的测试用例文件；节省编写测试用例的时间。  

###安装phpunit工具：
	wget https://phar.phpunit.de/phpunit.phar  
	chmod +x phpunit.phar  
	sudo mv phpunit.phar /usr/local/bin/phpunit  
	或者从https://phar.phpunit.de/ 下载所需要的版本  

（用pear来安装phpunit版本过低，现在不维护了，已经安装了，需要卸载，命令:  
pear uninstall phpunit/PHPUnit phpunit/PHPUnit_MockObject phpunit/PHP_CodeCoverage  
https://github.com/sebastianbergmann/phpunit/wiki/End-of-Life-for-PEAR-Installation-Method ）  

###配置config.php文件，
   1. 对于superphp设置如下：  
  
		'SRC_CLASS_PATH' => '/search/nginx/html/famous_brands/backend/WEB-INF/class/',//需要测试文件所在的目录  
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH  
		'UNIT_TEST_PATH' => '/search/nginx/html/famous_brands/tests/Test/',//生成的单元测试文件目录  
		'SUPERPHP_PATH' => '/search/superphp/',//superPHP框架所在的目录  
		'WEB_XML_FILE' => '/search/nginx/html/famous_brands/backend/WEB-INF/web.xml',//web.xml文件  
		'SKIP_FOLDERS' => array('processor','CRON','Base','util'),//忽略所要测试的文件夹（i）  
		'SKIP_FUNCTIONS' => array('__construct','run','test','beforeList','afterList'),//忽略所要测试的函数  
  
   2. 对于myframe(自己瞎整的框架)设置如下：  
  
		'SRC_CLASS_PATH' => '/search/nginx/html/myframe/class/',//需要测试文件所在的目录  
		'RECURSIVE' => true,//是否递归遍历SRC_CLASS_PATH  
		'UNIT_TEST_PATH' => '/search/nginx/html/myframe/tests/Test/',//生成的单元测试文件目录  
		'AUTOLOAD_FILE' => '/search/nginx/html/myframe/src/auto_load.php',//框架的auto_load文件  
		'SKIP_FOLDERS' => array('CRON','Base','util'),//忽略所要测试的文件夹(i)  
		'SKIP_FUNCTIONS' => array('__construct','run','test'),//忽略所要测试的函数  

###生成测试文件
运行：（针对类中的public方法,生成测试类文件中对应的方法;再次运行，会生成在New_Test文件夹中，不影响正在编辑的测试用例类）  
	php genUnitTestFile.php  

###运行已经生成的单元测试文件
例如:  
	cd /search/nginx/html/famous_brands/tests/Test/ && ls *.php | xargs -I '{}' phpunit '{}'  
	如果没有出现异常，测试成功，具体phpunit工具的是用可以参考帮助文档或者官网在线文档  
  
	具体生成的单元测试文件可以根据自己的逻辑，使用phpunit 单独测试调试代码，测试用例参考文档.  
(使用覆盖测试可以指定目录生成html静态文件，可以通过在该目录下输入命令：python -m SimpleHTTPServer 8888建个简单的服务，通过 http://localhost:8080/index.html 浏览产生的代码覆盖情况；需要安装xdebug扩展:yum install php-pecl-xdebug.x86_64 -y; 或者直接用pecl search xdebug,然后选择一个版本的xdebug安装上)  

###采用phpUnit源码框架进行测试
@todo  
	
###PHPUnit:
1. 官网：https://phpunit.de/  
2. 文档：https://phpunit.de/manual/current/zh_cn/installation.html  

@Date: 2015年 01月 19日 星期一 00:29:56 CST  
@modified Date: 2015年 01月 24日 星期六 22:38:10 CST  
