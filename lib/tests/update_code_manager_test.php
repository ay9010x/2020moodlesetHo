<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__.'/fixtures/testable_update_code_manager.php');


class core_update_code_manager_testcase extends advanced_testcase {

    public function test_get_remote_plugin_zip() {
        $codeman = new \core\update\testable_code_manager();

        $this->assertFalse($codeman->get_remote_plugin_zip('ftp://not.support.ed/', 'doesnotmatter'));
        $this->assertDebuggingCalled('Error fetching plugin ZIP: unsupported transport protocol: ftp://not.support.ed/');

        $this->assertEquals(0, $codeman->downloadscounter);
        $this->assertFalse($codeman->get_remote_plugin_zip('http://first/', ''));
        $this->assertDebuggingCalled('Error fetching plugin ZIP: md5 mismatch.');
        $this->assertEquals(1, $codeman->downloadscounter);
        $this->assertNotFalse($codeman->get_remote_plugin_zip('http://first/', md5('http://first/')));
        $this->assertEquals(2, $codeman->downloadscounter);
        $this->assertNotFalse($codeman->get_remote_plugin_zip('http://two/', md5('http://two/')));
        $this->assertEquals(3, $codeman->downloadscounter);
        $this->assertNotFalse($codeman->get_remote_plugin_zip('http://first/', md5('http://first/')));
        $this->assertEquals(3, $codeman->downloadscounter);
    }

    public function test_get_remote_plugin_zip_corrupted_cache() {

        $temproot = make_request_directory();
        $codeman = new \core\update\testable_code_manager(null, $temproot);

        file_put_contents($temproot.'/distfiles/'.md5('http://valid/').'.zip', 'http://invalid/');

                        $returned = $codeman->get_remote_plugin_zip('http://valid/', md5('http://valid/'));

        $this->assertEquals(basename($returned), md5('http://valid/').'.zip');
        $this->assertEquals(file_get_contents($returned), 'http://valid/');
    }

    public function test_unzip_plugin_file() {
        $codeman = new \core\update\testable_code_manager();
        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/invalidroot.zip';
        $targetdir = make_request_directory();
        mkdir($targetdir.'/aaa_another');

        $files = $codeman->unzip_plugin_file($zipfilepath, $targetdir);

        $this->assertInternalType('array', $files);
        $this->assertCount(4, $files);
        $this->assertSame(true, $files['invalid-root/']);
        $this->assertSame(true, $files['invalid-root/lang/']);
        $this->assertSame(true, $files['invalid-root/lang/en/']);
        $this->assertSame(true, $files['invalid-root/lang/en/fixed_root.php']);
        foreach ($files as $file => $status) {
            if (substr($file, -1) === '/') {
                $this->assertTrue(is_dir($targetdir.'/'.$file));
            } else {
                $this->assertTrue(is_file($targetdir.'/'.$file));
            }
        }

        $files = $codeman->unzip_plugin_file($zipfilepath, $targetdir, 'fixed_root');

        $this->assertInternalType('array', $files);
        $this->assertCount(4, $files);
        $this->assertSame(true, $files['fixed_root/']);
        $this->assertSame(true, $files['fixed_root/lang/']);
        $this->assertSame(true, $files['fixed_root/lang/en/']);
        $this->assertSame(true, $files['fixed_root/lang/en/fixed_root.php']);
        foreach ($files as $file => $status) {
            if (substr($file, -1) === '/') {
                $this->assertTrue(is_dir($targetdir.'/'.$file));
            } else {
                $this->assertTrue(is_file($targetdir.'/'.$file));
            }
        }

        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/bar.zip';
        $files = $codeman->unzip_plugin_file($zipfilepath, $targetdir, 'bar');
    }

    public function test_unzip_plugin_file_multidir() {
        $codeman = new \core\update\testable_code_manager();
        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/multidir.zip';
        $targetdir = make_request_directory();
                $this->setExpectedException('moodle_exception');
        $files = $codeman->unzip_plugin_file($zipfilepath, $targetdir, 'foo');
    }

    public function test_get_plugin_zip_root_dir() {
        $codeman = new \core\update\testable_code_manager();

        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/invalidroot.zip';
        $this->assertEquals('invalid-root', $codeman->get_plugin_zip_root_dir($zipfilepath));

        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/bar.zip';
        $this->assertEquals('bar', $codeman->get_plugin_zip_root_dir($zipfilepath));

        $zipfilepath = __DIR__.'/fixtures/update_validator/zips/multidir.zip';
        $this->assertSame(false, $codeman->get_plugin_zip_root_dir($zipfilepath));
    }

    public function test_list_plugin_folder_files() {
        $fixtures = __DIR__.'/fixtures/update_validator/plugindir';
        $codeman = new \core\update\testable_code_manager();
        $files = $codeman->list_plugin_folder_files($fixtures.'/foobar');
        $this->assertInternalType('array', $files);
        $this->assertEquals(6, count($files));
        $fixtures = str_replace(DIRECTORY_SEPARATOR, '/', $fixtures);
        $this->assertEquals($files['foobar/'], $fixtures.'/foobar');
        $this->assertEquals($files['foobar/lang/en/local_foobar.php'], $fixtures.'/foobar/lang/en/local_foobar.php');
    }

    public function test_zip_plugin_folder() {
        $fixtures = __DIR__.'/fixtures/update_validator/plugindir';
        $storage = make_request_directory();
        $codeman = new \core\update\testable_code_manager();
        $codeman->zip_plugin_folder($fixtures.'/foobar', $storage.'/foobar.zip');
        $this->assertTrue(file_exists($storage.'/foobar.zip'));

        $fp = get_file_packer('application/zip');
        $zipfiles = $fp->list_files($storage.'/foobar.zip');
        $this->assertNotEmpty($zipfiles);
        foreach ($zipfiles as $zipfile) {
            if ($zipfile->is_directory) {
                $this->assertTrue(is_dir($fixtures.'/'.$zipfile->pathname));
            } else {
                $this->assertTrue(file_exists($fixtures.'/'.$zipfile->pathname));
            }
        }
    }

    public function test_archiving_plugin_version() {
        $fixtures = __DIR__.'/fixtures/update_validator/plugindir';
        $codeman = new \core\update\testable_code_manager();

        $this->assertFalse($codeman->archive_plugin_version($fixtures.'/foobar', 'local_foobar', 0));
        $this->assertFalse($codeman->archive_plugin_version($fixtures.'/foobar', 'local_foobar', null));
        $this->assertFalse($codeman->archive_plugin_version($fixtures.'/foobar', '', 2015100900));
        $this->assertFalse($codeman->archive_plugin_version($fixtures.'/foobar-does-not-exist', 'local_foobar', 2013031900));

        $this->assertFalse($codeman->get_archived_plugin_version('local_foobar', 2013031900));
        $this->assertFalse($codeman->get_archived_plugin_version('mod_foobar', 2013031900));

        $this->assertTrue($codeman->archive_plugin_version($fixtures.'/foobar', 'local_foobar', 2013031900, true));

        $this->assertNotFalse($codeman->get_archived_plugin_version('local_foobar', 2013031900));
        $this->assertTrue(file_exists($codeman->get_archived_plugin_version('local_foobar', 2013031900)));
        $this->assertTrue(file_exists($codeman->get_archived_plugin_version('local_foobar', '2013031900')));

        $this->assertFalse($codeman->get_archived_plugin_version('mod_foobar', 2013031900));
        $this->assertFalse($codeman->get_archived_plugin_version('local_foobar', 2013031901));
        $this->assertFalse($codeman->get_archived_plugin_version('', 2013031901));
        $this->assertFalse($codeman->get_archived_plugin_version('local_foobar', ''));

        $this->assertTrue($codeman->archive_plugin_version($fixtures.'/foobar', 'local_foobar', '2013031900'));
        $this->assertTrue(file_exists($codeman->get_archived_plugin_version('local_foobar', 2013031900)));

    }
}
