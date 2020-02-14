<?php



defined('MOODLE_INTERNAL') || die();



class core_setuplib_testcase extends advanced_testcase {

    
    public function test_get_docs_url_standard() {
        global $CFG;
        if (empty($CFG->docroot)) {
            $docroot = 'http://docs.moodle.org/';
        } else {
            $docroot = $CFG->docroot;
        }
        $this->assertRegExp('~^' . preg_quote($docroot, '') . '/\d{2}/' . current_language() . '/course/editing$~',
                get_docs_url('course/editing'));
    }

    
    public function test_get_docs_url_http() {
        $url = 'http://moodle.org/';
        $this->assertEquals($url, get_docs_url($url));
    }

    
    public function test_get_docs_url_https() {
        $url = 'https://moodle.org/';
        $this->assertEquals($url, get_docs_url($url));
    }

    
    public function test_get_docs_url_wwwroot() {
        global $CFG;
        $this->assertSame($CFG->wwwroot . '/lib/tests/setuplib_test.php',
                get_docs_url('%%WWWROOT%%/lib/tests/setuplib_test.php'));
    }

    
    public function test_exception_info_removes_serverpaths() {
        global $CFG;

                $cfgnames = array('dataroot', 'dirroot', 'tempdir', 'cachedir', 'localcachedir');

        $fixture  = '';
        $expected = '';
        foreach ($cfgnames as $cfgname) {
            if (!empty($CFG->$cfgname)) {
                $fixture  .= $CFG->$cfgname.' ';
                $expected .= "[$cfgname] ";
            }
        }
        $exception     = new moodle_exception('generalexceptionmessage', 'error', '', $fixture, $fixture);
        $exceptioninfo = get_exception_info($exception);

        $this->assertContains($expected, $exceptioninfo->message, 'Exception message does not contain system paths');
        $this->assertContains($expected, $exceptioninfo->debuginfo, 'Exception debug info does not contain system paths');
    }

    public function test_localcachedir() {
        global $CFG;

        $this->resetAfterTest(true);

                $this->assertSame("$CFG->dataroot/localcache", $CFG->localcachedir);

        $this->setCurrentTimeStart();
        $timestampfile = "$CFG->localcachedir/.lastpurged";

                        remove_dir($CFG->localcachedir, true);
        $dir = make_localcache_directory('', false);
        $this->assertSame($CFG->localcachedir, $dir);
        $this->assertFileNotExists("$CFG->localcachedir/.htaccess");
        $this->assertFileExists($timestampfile);
        $this->assertTimeCurrent(filemtime($timestampfile));

        $dir = make_localcache_directory('test/test', false);
        $this->assertSame("$CFG->localcachedir/test/test", $dir);

                $CFG->localcachedir = "$CFG->dataroot/testlocalcache";
        $this->setCurrentTimeStart();
        $timestampfile = "$CFG->localcachedir/.lastpurged";
        $this->assertFileNotExists($timestampfile);

        $dir = make_localcache_directory('', false);
        $this->assertSame($CFG->localcachedir, $dir);
        $this->assertFileExists("$CFG->localcachedir/.htaccess");
        $this->assertFileExists($timestampfile);
        $this->assertTimeCurrent(filemtime($timestampfile));

        $dir = make_localcache_directory('test', false);
        $this->assertSame("$CFG->localcachedir/test", $dir);

        $prevtime = filemtime($timestampfile);
        $dir = make_localcache_directory('pokus', false);
        $this->assertSame("$CFG->localcachedir/pokus", $dir);
        $this->assertSame($prevtime, filemtime($timestampfile));

                $testfile = "$CFG->localcachedir/test/test.txt";
        $this->assertTrue(touch($testfile));

        $now = $this->setCurrentTimeStart();
        set_config('localcachedirpurged', $now - 2);
        purge_all_caches();
        $this->assertFileNotExists($testfile);
        $this->assertFileNotExists(dirname($testfile));
        $this->assertFileExists($timestampfile);
        $this->assertTimeCurrent(filemtime($timestampfile));
        $this->assertTimeCurrent($CFG->localcachedirpurged);

                make_localcache_directory('test', false);
        $this->assertTrue(touch($testfile));
        set_config('localcachedirpurged', $now - 1);
        $this->assertTrue(touch($timestampfile, $now - 2));
        clearstatcache();
        $this->assertSame($now - 2, filemtime($timestampfile));

        $this->setCurrentTimeStart();
        $dir = make_localcache_directory('', false);
        $this->assertSame("$CFG->localcachedir", $dir);
        $this->assertFileNotExists($testfile);
        $this->assertFileNotExists(dirname($testfile));
        $this->assertFileExists($timestampfile);
        $this->assertTimeCurrent(filemtime($timestampfile));
    }

    public function test_make_unique_directory_basedir_is_file() {
        global $CFG;

                $base = $CFG->tempdir . DIRECTORY_SEPARATOR . md5(microtime(true) + rand());
        touch($base);

                $this->assertFalse(make_unique_writable_directory($base, false));

                $this->setExpectedException('invalid_dataroot_permissions',
                $base . ' is not writable. Unable to create a unique directory within it.'
            );
        make_unique_writable_directory($base);

        unlink($base);
    }

    public function test_make_unique_directory() {
        global $CFG;

                $firstdir = make_unique_writable_directory($CFG->tempdir);
        $this->assertTrue(is_dir($firstdir));
        $this->assertTrue(is_writable($firstdir));

        $seconddir = make_unique_writable_directory($CFG->tempdir);
        $this->assertTrue(is_dir($seconddir));
        $this->assertTrue(is_writable($seconddir));

                $this->assertNotEquals($firstdir, $seconddir);
    }

    public function test_get_request_storage_directory() {
                $firstdir = get_request_storage_directory();
        $seconddir = get_request_storage_directory();
        $this->assertTrue(is_dir($firstdir));
        $this->assertEquals($firstdir, $seconddir);

                remove_dir($firstdir);
        $this->assertFalse(file_exists($firstdir));
        $this->assertFalse(is_dir($firstdir));

        $thirddir = get_request_storage_directory();
        $this->assertTrue(is_dir($thirddir));
        $this->assertNotEquals($firstdir, $thirddir);

                remove_dir($thirddir);
        $this->assertFalse(file_exists($thirddir));
        $this->assertFalse(is_dir($thirddir));
        touch($thirddir);
        $this->assertTrue(file_exists($thirddir));
        $this->assertFalse(is_dir($thirddir));

        $fourthdir = get_request_storage_directory();
        $this->assertTrue(is_dir($fourthdir));
        $this->assertNotEquals($thirddir, $fourthdir);
    }


    public function test_make_request_directory() {
                $firstdir   = make_request_directory();
        $seconddir  = make_request_directory();
        $thirddir   = make_request_directory();
        $fourthdir  = make_request_directory();

        $this->assertNotEquals($firstdir,   $seconddir);
        $this->assertNotEquals($firstdir,   $thirddir);
        $this->assertNotEquals($firstdir,   $fourthdir);
        $this->assertNotEquals($seconddir,  $thirddir);
        $this->assertNotEquals($seconddir,  $fourthdir);
        $this->assertNotEquals($thirddir,   $fourthdir);

                $requestdir = get_request_storage_directory();
        $this->assertEquals(0, strpos($firstdir,    $requestdir));
        $this->assertEquals(0, strpos($seconddir,   $requestdir));
        $this->assertEquals(0, strpos($thirddir,    $requestdir));
        $this->assertEquals(0, strpos($fourthdir,   $requestdir));

                remove_dir($requestdir);
        $this->assertFalse(file_exists($requestdir));
        $this->assertFalse(is_dir($requestdir));

        $fifthdir   = make_request_directory();
        $this->assertNotEquals($firstdir,   $fifthdir);
        $this->assertNotEquals($seconddir,  $fifthdir);
        $this->assertNotEquals($thirddir,   $fifthdir);
        $this->assertNotEquals($fourthdir,  $fifthdir);
        $this->assertTrue(is_dir($fifthdir));
        $this->assertFalse(strpos($fifthdir, $requestdir));

                $newrequestdir = get_request_storage_directory();
        $this->assertEquals(0, strpos($fifthdir, $newrequestdir));
    }

    public function test_merge_query_params() {
        $original = array(
            'id' => '1',
            'course' => '2',
            'action' => 'delete',
            'grade' => array(
                0 => 'a',
                1 => 'b',
                2 => 'c',
            ),
            'items' => array(
                'a' => 'aa',
                'b' => 'bb',
            ),
            'mix' => array(
                0 => '2',
            ),
            'numerical' => array(
                '2' => array('a' => 'b'),
                '1' => '2',
            ),
        );

        $chunk = array(
            'numerical' => array(
                '0' => 'z',
                '2' => array('d' => 'e'),
            ),
            'action' => 'create',
            'next' => '2',
            'grade' => array(
                0 => 'e',
                1 => 'f',
                2 => 'g',
            ),
            'mix' => 'mix',
        );

        $expected = array(
            'id' => '1',
            'course' => '2',
            'action' => 'create',
            'grade' => array(
                0 => 'a',
                1 => 'b',
                2 => 'c',
                3 => 'e',
                4 => 'f',
                5 => 'g',
            ),
            'items' => array(
                'a' => 'aa',
                'b' => 'bb',
            ),
            'mix' => 'mix',
            'numerical' => array(
                '2' => array('a' => 'b', 'd' => 'e'),
                '1' => '2',
                '0' => 'z',
            ),
            'next' => '2',
        );

        $array = $original;
        merge_query_params($array, $chunk);

        $this->assertSame($expected, $array);
        $this->assertNotSame($original, $array);

        $query = "id=1&course=2&action=create&grade%5B%5D=a&grade%5B%5D=b&grade%5B%5D=c&grade%5B%5D=e&grade%5B%5D=f&grade%5B%5D=g&items%5Ba%5D=aa&items%5Bb%5D=bb&mix=mix&numerical%5B2%5D%5Ba%5D=b&numerical%5B2%5D%5Bd%5D=e&numerical%5B1%5D=2&numerical%5B0%5D=z&next=2";
        $decoded = array();
        parse_str($query, $decoded);
        $this->assertSame($expected, $decoded);

                $this->assertNotSame($expected, array_merge_recursive($original, $chunk));
    }

    
    public function test_get_exception_info_link() {
        global $CFG, $SESSION;

        $initialloginhttps = $CFG->loginhttps;
        $httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $CFG->loginhttps = false;

                $url = $CFG->wwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($url, $infos->link);

                $url = '/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $url = $httpswwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $CFG->loginhttps = true;
        $url = $httpswwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($url, $infos->link);

                $url = 'http://moodle.org/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $url = 'https://moodle.org/something/here?really=yes';
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $url = 'http://moodle.org/something/here?' . $CFG->wwwroot;
        $exception = new moodle_exception('none', 'error', $url);
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $SESSION->fromurl = $url = $CFG->wwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($url, $infos->link);

                $SESSION->fromurl = $url = $httpswwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($url, $infos->link);

                $CFG->loginhttps = false;
        $SESSION->fromurl = $httpswwwroot . '/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $SESSION->fromurl = 'http://moodle.org/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $SESSION->fromurl = 'https://moodle.org/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

                $CFG->loginhttps = true;
        $SESSION->fromurl = 'https://moodle.org/something/here?really=yes';
        $exception = new moodle_exception('none');
        $infos = $this->get_exception_info($exception);
        $this->assertSame($CFG->wwwroot . '/', $infos->link);

        $CFG->loginhttps = $initialloginhttps;
        $SESSION->fromurl = '';
    }

    
    public function get_exception_info($ex) {
        try {
            throw $ex;
        } catch (moodle_exception $e) {
            return get_exception_info($e);
        }
    }

    public function test_object() {
        $obj = new object();
        $this->assertDebuggingCalled("'object' class has been deprecated, please use stdClass instead.");
        $this->assertInstanceOf('stdClass', $obj);
    }

    
    public function data_for_test_get_real_size() {
        return array(
            array('8KB', 8192),
            array('8Kb', 8192),
            array('8K', 8192),
            array('8k', 8192),
            array('50MB', 52428800),
            array('50Mb', 52428800),
            array('50M', 52428800),
            array('50m', 52428800),
            array('8Gb', 8589934592),
            array('8GB', 8589934592),
            array('8G', 8589934592),
        );
    }

    
    public function test_get_real_size($input, $expectedbytes) {
        $this->assertEquals($expectedbytes, get_real_size($input));
    }
}
