<?php
/**
 * @Author wuyong
 * @Date: 2015年 01月 18日 星期日 15:25:38 CST 
 * @Desc: 生成单元测试文件.
 * @todo: 1.如果生成的测试文件已经编辑了，就不用生成覆盖(采用文件的sha1sum值做对比,还有一种方案是如果存在了，生成一份*.new.php文件done);
done:2.递归方式遍历生成的所有文件有些是不需要测试的，需要加入过滤优化，(已经加入了过滤文件夹)
	3.加入忽略策略时，在New_Test问价夹中应该删除原来已经生成的文件
 */

require_once('config.php');
define( "FILE_DEFAULT_DIRECTORY_CREATION_MODE", 0755 );

try{
	if($argc<2) {
		echo "\n请输入应用名称：tuan; dyp; mai; dh123; case; .. etc.\n\n";
		exit;
	}
	$app = $argv[1];
	$config = Config::load($app);
    $oUnitTestBuilder = new UnitTestBuilder($config);
    $oUnitTestBuilder->run();
}catch (Exception $e){
    echo "Exception: ".$e->getMessage();
}

/**
 * UnitTestBuilder 
 * 
 * @desc: 创建单元测试类
 * @version $1.0$
 * @copyright 2013-2015 upd-sogou
 * @author wuyong <wuyong@sogou-inc.com> 
 */
class UnitTestBuilder {

    private static $_skipFolders = array('cache', 'log', '.svn', 'sqls', 'logs', 'temp','.git','template','test');
    private static $_skipFiles= array();
    private static $_fileNameTemplate = array('php');
	private static $_skipFunctions = array('__construct','run');

	private static $_testFunctionTpl = '
	/**
	 * @test
	 */
	public function ###FUNCTION###(){
		$this->assertTrue(true);
		$this->assertEmpty(array());
		$this->assertCount(1,array(1));
		$this->assertEquals(1,1);
		//...
	}
	';

	private static $_testUnitCodeTpl = '<?php
/**
 * @author: wuyong
 * @create_time: ###TIME###
 */
class Test_###CLASS### extends PHPUnit_Framework_TestCase{

	public function setUp(){ }

	public function tearDown(){ }
		
	public function testExample(){
		$this->assertTrue(true);
		$this->assertEmpty(array());
		$this->assertCount(1,array(1));
		$this->assertEquals(1,1);
		//...etc
	}
	###TEST_FUNCTIONS###

}
?>';

	private static $_testSOSOUnitCodeTpl = '<?php
/**
 * @author: wuyong
 * @create_time: ###TIME###
 */
class Test_###CLASS### extends PHPUnit_Framework_TestCase{
	protected $soso=null;

	public function setUp(){ 
		require_once("###SOSO_PHP###");
		if(is_null($this->soso)){
			$soso = new SOSO("###WEB_XML###");
		}
	}

	public function tearDown(){ }
		
	public function testExample(){
		$this->assertTrue(true);
		$this->assertEmpty(array());
		$this->assertCount(1,array(1));
		$this->assertEquals(1,1);
		//...etc
	}
	###TEST_FUNCTIONS###

}
?>';

	private $mSrcClassPath;
	private $mOutTestDir;
	private $mSuperphpPath;
	private $mWebXmlFile;
	private $mRecursive;

	public function __construct($config){
		$this->mSrcClassPath = $config['SRC_CLASS_PATH'];
		$this->mOutTestDir = $config['UNIT_TEST_PATH'];
		$this->mSuperphpPath = $config['SUPERPHP_PATH'];
		$this->mWebXmlFile = $config['WEB_XML_FILE'];
		$this->mRecursive = $config['RECURSIVE'];

		self::$_skipFunctions = array_unique(array_merge(self::$_skipFunctions,$config['SKIP_FUNCTIONS']));
		self::$_skipFolders = array_unique(array_merge(self::$_skipFolders,$config['SKIP_FOLDERS']));
	}
	// }}}

    static public function getClassCodeTpl($type=''){
		$tpl = '';
		switch($type){
		case 'case':
			$tpl = self::$_testUnitCodeTpl;
			break;
		case 'soso':
			$tpl = self::$_testSOSOUnitCodeTpl;
			break;
		default:
			$tpl = self::$_testSOSOUnitCodeTpl;
			break;
		}

		return $tpl;
    }

	public function run(){
		echo "\nsearch files in {$this->mSrcClassPath} successed!\n\n";
        $path = $this->mSrcClassPath;
		$files = self::findFiles($path,$this->mRecursive);//递归找
		$classes = self::findClasses($files);
		$this->generatorTestFile($classes);
		echo "\ngenerator test files in {$this->mOutTestDir} successed!\n\n";
	}

	/* private findFiles($dirname,$isRecursive=true) {{{ */ 
	/**
	 * findFiles
	 * 
	 * @desc: 获取给定目录下的文件，默认是递归方式
	 * @author: wuyong
	 * @return array
	 */
	static private function findFiles($dirname,$isRecursive=true){
		$dirname = rtrim($dirname,"\/");
        $filelist = array();
        $currentfilelist = scandir($dirname);
		foreach ($currentfilelist as $file) {
			if ($file == "." || $file == ".." ) continue;

			$file = "$dirname/$file";
			if(self::isSkipFolders($file)) continue;

			if (is_dir($file)) {
				if(!$isRecursive) continue;
				foreach (self::findFiles($file) as $tmpFile){
					$filelist[] = $tmpFile;
				}
				continue;
			}

			if (false == self::skipFiles($file)) {
				echo $file."\n";
				$filelist[] = $file;
			}
		}
        return $filelist;
	}
	// }}}

	/* private findClasses($files) {{{ */ 
	/**
	 * findClasses
	 * 
	 * @desc: 从一个文件中获取class,和对应的functions (文件中出现相同的类名处理，相同类时，返回的数组只保存最后第一次出现的class,functions)
	 * @author: wuyong
	 * @return array
	 */
	static private function findClasses($files){
		$files_classes_funcs = array();
		$classes = array();
        foreach ($files as $file) {
			$classes_funcs = self::findClassAndFinctionFromAFile($file);
            foreach ($classes_funcs as $class_functions) {
				if(count($classes)>0&&in_array($class_functions['class'],$classes)){
					echo "Repeatedly Class {$class_functions['class']} in file $file\n";
				} 
				$classes[] = $class_functions['class'];
				$files_classes_funcs[$class_functions['class']]['file'] = $file;

				$functions = array_diff($class_functions['functions'],self::$_skipFunctions);
				$files_classes_funcs[$class_functions['class']]['functions'] = $functions;

            }
        }
        return $files_classes_funcs;
	}
	// }}}

    /* private generatorTestFile($classes) {{{ */ 
    /**
     * generatorTestFile
     * 
     * @desc: 生成单元测试文件
	 * @author: wuyong
     */
    private function generatorTestFile($classes) {
		$class_code = self::getClassCodeTpl('soso');
		$func_code = self::$_testFunctionTpl;
        foreach ($classes as $class => $value) {
			$outTestDir = $this->mOutTestDir;
			$outFile = $this->mOutTestDir . $class . ".php";
			$file = new File($outFile);
			if($file->exists()) {
				$outTestDir = rtrim($this->mOutTestDir,'\/') . "/New_Test/";
				$outFile = $outTestDir . $class . ".php";
				$file->setFileName($outFile);
			}
			$file->createDir($outTestDir);
			$file->open("w+");
			$code = str_replace("###TIME###",date('Y-m-d H:i:s',time()),$class_code);
			$code = str_replace("###CLASS###",$class,$code);
			$code = str_replace("###SOSO_PHP###",$this->mSuperphpPath."SOSO/SOSO.php",$code);
			$code = str_replace("###WEB_XML###",$this->mWebXmlFile,$code);
			$functions = "";
			foreach($value['functions'] as $func){
				$function = str_replace("###FUNCTION###",$func,$func_code);
				$functions .= $function;
			}
			$code = str_replace("###TEST_FUNCTIONS###",$functions,$code);
			$file->write($code);
			$file->close();
        }
    }
    // }}}

    static private function findClassAndFinctionFromAFile($file) {
        $classes = $functions = array();
		$class = '';
        $lines = file($file);
		$index = 0;//0是文件中没有被类封装的过程函数
        foreach ($lines as $line) {
            if (preg_match("/^\s*class\s+(\S+)\s*/i", $line, $match)) {
                $class = trim(rtrim($match[1],'{'));
				$index++;
            }
            if (preg_match("/^\s*abstract\s*class\s+(\S+)\s*/i", $line, $match)) {
                $class = trim(rtrim($match[1],'{'));
				$index++;
            }
            if (preg_match("/^\s*interface\s+(\S+)\s*/i", $line, $match)) {
                $class = trim(rtrim($match[1],'{'));
				$index++;
            }

			if (preg_match("/^\s*(?:(public\s+))?function\s+(\S+)\s*\(/i", $line, $match)) {
				$func = explode('(',$match[2]);
				$classes[$index]['functions'][] = trim($func[0]);
				$classes[$index]['class'] = $class; 
			}
        }
        return $classes;
    }

    static private function skipFiles($file) {
        foreach(self::$_skipFiles as $fileRule) {
            if(preg_match("/$fileRule/i",$file))
                return ;
        }
        $suffix = self::getFileSuffix($file);
        return ( false == in_array($suffix, self::$_fileNameTemplate)
			|| (1 == preg_match("/\.svn/", $file)) 
			|| (0 == preg_match("/.+\.php/", $file)) );
    }

    static private function getFileSuffix($fileName) {
        $pointPos = strrpos($fileName, ".");

        if ($pointPos!==false) {
            return substr($fileName, $pointPos+1, strlen($fileName) - $pointPos);
        }
        return;
    }

    static private function isSkipFolders($file) {
		if(!is_dir($file)) return false;
        foreach (self::$_skipFolders as $skip) {
            $skip = quotemeta($skip);
            if (1 == preg_match("/$skip/i", $file)) return true;
        }
    }
}

class File {/*{{{*/

    var $_fileName;
    var $_handler;
    var $_mode;

    function File( $fileName )
    {
        $this->_fileName = $fileName; 
    }

	function setFileName($fileName){//add by wuyong
		$this->_fileName = $fileName;
	}

    function open( $mode = "r" )
    {
        $this->_handler = fopen( $this->_fileName, $mode );

        $this->_mode = $mode;

        return $this->_handler;
    }

    /**
     * Closes the stream currently being held by this object
     *
     * @return nothing
     */
    function close()
    {
        fclose( $this->_handler );
    }

    /**
     * Reads the whole file and put it into an array, where every position
     * of the array is a line of the file (new-line characters not
     * included)
     *
     * @return An array where every position is a line from the file.
     */
    function readFile()
    {
        $contents = Array();

        $contents = file( $this->_fileName );

        for( $i = 0; $i < count( $contents ); $i++ )
            $contents[$i] = trim( $contents[$i] );

        return $contents;
    }

    /**
     * Reads bytes from the currently opened file
     *
     * @param size Amount of bytes we'd like to read from the file. It is
     * set to 4096 by default.
     * @return Returns the read contents
     */
    function read( $size = 4096 )
    {
        return( fread( $this->_handler, $size ));
    }

    /**
     * checks whether we've reached the end of file
     *
     * @return True if we reached the end of the file or false otherwise
     */
    function eof()
    {
        return feof( $this->_handler );
    }

    /**
     * Writes data to disk
     *
     * @param data The data that we'd like to write to disk
     * @return returns the number of bytes written, or false otherwise
     */
    function write( $data )
    {
        return fwrite( $this->_handler, $data );
    }

    /**
     * truncates the currently opened file to a given length
     *
     * @param length Lenght at which we'd like to truncate the file
     * @return true if successful or false otherwise
     */
    function truncate( $length = 0 )
    {
        return ftruncate( $this->_handler, $length );
    }

    /**
     * Writes an array of text lines to the file.
     *
     * @param lines The array with the text.
     * @return Returns true if successful or false otherwise.
     */
    function writeLines( $lines )
    {
        // truncate the file to remove the old contents
        $this->truncate();

        foreach( $lines as $line ) {
            //print("read: \"".htmlentities($line)."\"<br/>");
            if( !$this->write( $line, strlen($line))) {
                return false;
            }
            /*else
            print("written: \"".htmlentities($line)."\"<br/>");*/
        }

        return true;
    }

    /**
     * Returns true wether the file is a directory. See
     * http://fi.php.net/manual/en/function.is-dir.php for more details.
     *
     * @param file The filename we're trying to check. If omitted, the
     * current file will be used (note that this function can be used as
     * static as long as the file parameter is provided)
     * @return Returns true if the file is a directory.
     */
    function isDir( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        return is_dir( $file );
    }

    /**
     * Returns true if the file is writable by the current user.
     * See http://fi.php.net/manual/en/function.is-writable.php for more
     * details.
     *
     * @param file The filename we're trying to check. If omitted, the
     * current file will be used (note that this function can be used as
     * static as long as the file parameter is provided)
     * @return Returns true if the file is writable, or false otherwise.
     */
    function isWritable( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        return is_writable( $file );
    }

    /**
     * returns true if the file is readable. Can be used as static if a
     * filename is provided
     *
     * @param if provided, this method can be used as an static method and
     * it will check for the readability status of the file
     * @return true if readable or false otherwise
     */
    function isReadable( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        clearstatcache();

        return is_readable( $file );
    }

    /**
     * removes a file. Can be used as static if a filename is provided.
     * Otherwise it will remove the file that was given in the constructor
     *
     * @param optionally, name of the file to delete
     * @return True if successful or false otherwise
     */
    function delete( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        if( !File::isReadable( $file ))
            return false;

        if( File::isDir( $file ))
            $result = rmdir( $file );
        else
            $result = unlink( $file );

        return $result;
    }

    /**
     * removes a directory, optinally in a recursive fashion
     *
     * @param dirName
     * @param recursive Whether to recurse through all subdirectories that
     * are within the given one and remove them.
     * @param onlyFiles If the recursive mode is enabled, setting this to 'true' will
     * force the method to only remove files but not folders. The directory will not be
     * removed but all the files included it in (and all subdirectories) will be.
     * @return True if successful or false otherwise
     * @static
     */
    function deleteDir( $dirName, $recursive = false, $onlyFiles = false )
    {
        // if the directory can't be read, then quit with an error
        if( !File::isReadable( $dirName ) || !File::exists( $dirName )) {
            return false;
        }

        // if it's not a file, let's get out of here and transfer flow
        // to the right place...
        if( !File::isDir( $dirName )) {
            return File::delete( $dirName );
        }

        // Glob::myGlob is easier to use than Glob::glob, specially when
        // we're relying on the native version... This improved version
        // will automatically ignore things like "." and ".." for us,
        // making it much easier!
        //$files = Glob::myGlob( $dirName, "*" );
        $files = glob(rtrim($dirName,'\/')."/*");//add by wuyong
        foreach( $files as $file ) {
            if( File::isDir( $file )) {
                // perform a recursive call if we were allowed to do so
                if( $recursive )
                    File::deleteDir( $file, $recursive, $onlyFiles );
            }

            // File::delete can remove empty folders as well as files
            if( File::isReadable( $file ))
                File::delete( $file );			}

        // finally, remove the top-level folder but only in case we
        // are supposed to!
        if( !$onlyFiles )
            File::delete( $dirName );

        return true;
    }

    /**
     * Creates a new folder. If the folder name is /a/b/c and neither
     * /a or /a/b exist, this method will take care of creating the
     * whole folder structure automatically.
     *
     * @static
     * @param dirName The name of the new folder
     * @param mode Attributes that will be given to the folder
     * @return Returns true if no problem or false otherwise.
     */
    function createDir( $dirName,
                        $mode = FILE_DEFAULT_DIRECTORY_CREATION_MODE )
    {
		
        if(File::exists($dirName)) return true;

        if(substr($dirName, strlen($dirName)-1) == "/" ){
            $dirName = substr($dirName, 0,strlen($dirName)-1);
        }

        // for example, we will create dir "/a/b/c"
        // $firstPart = "/a/b"
        $firstPart = substr($dirName,0,strrpos($dirName, "/" ));

        if(file_exists($firstPart)){
            if(!mkdir($dirName,$mode)) return false;
            chmod( $dirName, $mode );
        } else {
            File::createDir($firstPart,$mode);
            if(!mkdir($dirName,$mode)) return false;
            chmod( $dirName, $mode );
        }

        return true;
    }


    /**
     * returns a temporary filename in a pseudo-random manner
     *
     * @return a temporary name
     */
    function getTempName()
    {
        return md5(microtime());
    }

    /**
     * Returns the size of the file.
     *
     * @param string fileName An optional parameter specifying the name
     * of the file. If omitted, we will use the file that we have
     * currently opened. Please note that this function can
     * be used as static if a file name is specified.
     * @return An integer specifying the size of the file.
     */
    function getSize( $fileName = null )
    {
        if( $fileName == null )
            $fileName = $this->_fileName;

        $size = filesize( $fileName );
        if( !$size )
            return -1;
        else
            return $size;
    }

    /**
     * renames a file
     *
     * http://www.php.net/manual/en/function.rename.php
     *
     * This function can be used as static if inFile and outFile are both
     * not empty. if outFile is empty, then the internal file of the
     * current object will be used as the input file and the first
     * parameter of this method will become the destination file name.
     *
     * @param inFile Original file
     * @param outFile Destination file.
     * @return Returns true if file was renamed ok or false otherwise.
     */
    function rename( $inFile, $outFile = null )
    {
        // check how many parameters we have
        if( $outFile == null ) {
            $outFile = $inFile;
            $inFile  = $this->_fileName;
        }

        // In order to work around the bug in php versions older
        // than 4.3.3, where rename will not work across different
        // partitions, this will be a copy and delete of the original file

        // copy the file to the new location
        if (!copy($inFile, $outFile)) {
            // The copy failed, return false
            return FALSE;
        }

        // Now delete the old file
        // NOTICE, we are not checking the error here.  It is possible
        // the the original file will remain and the copy will exist.
        //
        // One way to potentially fix this is to look at the result of
        // unlink, and then delete the copy if unlink returned FALSE,
        // but this call to unlink could just as easily fail
        unlink( $inFile );

        return TRUE;
    }

    /**
     * copies a file from one place to another.
     * This method is always static
     *
     * @param inFile
     * @param destFile
     * @return True if successful or false otherwise
     * @static
     */
    function copy( $inFile, $outFile )
    {
        return @copy( $inFile, $outFile );
    }

    /**
     * changes the permissions of a file, via PHP's chmod() function
     *
     * @param inFile The name of the file whose mode we'd like to change
     * @param mode The new mode, or if none provided, it will default
     * to 0644
     * @return true if successful or false otherwise
     * @static
     */
    function chMod( $inFile, $mode = 0644 )
    {
        return chmod( $inFile, $mode );
    }

    /**
     * returns true if the file exists.
     *
     * Can be used as an static method if a file name is provided as a
     *  parameter
     * @param fileName optinally, name of the file whose existance we'd
     * like to check
     * @return true if successful or false otherwise
     */
    function exists( $fileName = null )
    {
        if( $fileName == null )
            $fileName = $this->_fileName;

        clearstatcache();

        return file_exists( $fileName );
    }

    /**
     * returns true if the file could be touched
     *
     * Can be used to create a file or to reset the timestamp.
     * @return true if successful or false otherwise
     * @see PHP Function touch()
     *
     */
    function touch( $fileName = null )
    {
        if( $fileName == null )
            return false;

        return touch($fileName);
    }
}/*}}}*/
