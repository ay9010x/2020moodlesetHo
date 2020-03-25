<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../configonlylib.php');



class core_configonlylib_testcase extends advanced_testcase {

    
    public function test_min_fix_utf8() {
        $this->assertSame('abc', min_fix_utf8('abc'));
        $this->assertSame("žlutý koníček přeskočil potůček \n\t\r", min_fix_utf8("žlutý koníček přeskočil potůček \n\t\r\0"));
        $this->assertSame('aš', min_fix_utf8('a'.chr(130).'š'), 'This fails with buggy iconv() when mbstring extenstion is not available as fallback.');
    }

    
    public function test_min_clean_param() {
        $this->assertSame('foo', min_clean_param('foo', 'RAW'));
        $this->assertSame('aš', min_clean_param('a'.chr(130).'š', 'RAW'));

        $this->assertSame(1, min_clean_param('1', 'INT'));
        $this->assertSame(1, min_clean_param('1aa', 'INT'));

        $this->assertSame('1abc-d_f', min_clean_param('/.1ačž"b?;c-d{}\\_f.', 'SAFEDIR'));
        $this->assertSame(1, min_clean_param('1aa', 'INT'));

        $this->assertSame('/a/b/./c5', min_clean_param('/a*?$//b/.../c5', 'SAFEPATH'));
        $this->assertSame(1, min_clean_param('1aa', 'INT'));
    }

    
    public function test_min_optional_param() {
        $this->resetAfterTest();

        $_GET['foo'] = 'bar';
        $_GET['num'] = '1';
        $_GET['xnum'] = '1aa';

        $_POST['foo'] = 'rebar';
        $_POST['oof'] = 'rab';

        $this->assertSame('bar', min_optional_param('foo', null, 'RAW'));
        $this->assertSame(null, min_optional_param('foo2', null, 'RAW'));
        $this->assertSame('rab', min_optional_param('oof', null, 'RAW'));

        $this->assertSame(1, min_optional_param('num', null, 'INT'));
        $this->assertSame(1, min_optional_param('xnum', null, 'INT'));
    }

    
    public function test_min_get_slash_argument() {
        global $CFG;

        $this->resetAfterTest();
        $this->assertEquals('http://www.example.com/moodle', $CFG->wwwroot);

        $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.2.22 (Unix)';
        $_SERVER['QUERY_STRING'] = 'theme=standard&component=core&rev=5&image=u/f1';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php?theme=standard&component=core&rev=5&image=u/f1';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $this->assertSame('', min_get_slash_argument());

        $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Apache/2.2.22 (Unix)';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['PATH_INFO'] = '/standard/core/5/u/f1';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $_GET = array();
        $this->assertSame('/standard/core/5/u/f1', min_get_slash_argument());

                $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS/7.0';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['PATH_INFO'] = '/standard/core/5/u/f1';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $_GET = array();
        $this->assertSame('/standard/core/5/u/f1', min_get_slash_argument());

                $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Microsoft-IIS/7.0';
        $_SERVER['QUERY_STRING'] = 'file=/standard/core/5/u/f1';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $_GET = array();
        $_GET['file'] = '/standard/core/5/u/f1';
        $this->assertSame('/standard/core/5/u/f1', min_get_slash_argument());

        $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Weird server';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['PATH_INFO'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $_GET = array();
        $this->assertSame('/standard/core/5/u/f1', min_get_slash_argument());

        $_SERVER = array();
        $_SERVER['SERVER_SOFTWARE'] = 'Hacker server';
        $_SERVER['QUERY_STRING'] = '';
        $_SERVER['REQUEST_URI'] = '/moodle/theme/image.php/standard/core/5/u/f1';
        $_SERVER['PATH_INFO'] = '/moodle/theme/image.php/standard\\core/..\\../5/u/f1';
        $_SERVER['SCRIPT_NAME'] = '/moodle/theme/image.php';
        $_GET = array();
                $this->assertSame('/standardcore/./5/u/f1', min_get_slash_argument());
    }
}
