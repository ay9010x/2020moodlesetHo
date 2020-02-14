<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filestorage/file_progress.php');

class core_files_tgz_packer_testcase extends advanced_testcase implements file_progress {
    
    protected $progress;

    
    protected static function file_put_contents_at_time($path, $contents, $mtime) {
        file_put_contents($path, $contents);
        touch($path, $mtime);
    }

    
    protected function prepare_file_list() {
        global $CFG;
        $this->resetAfterTest(true);

                $filelist = array();

                self::file_put_contents_at_time($CFG->tempdir . '/file1.txt', 'File 1', 1377993601);
        $filelist['out1.txt'] = $CFG->tempdir . '/file1.txt';

                check_dir_exists($CFG->tempdir . '/dir1/dir2');
        self::file_put_contents_at_time($CFG->tempdir . '/dir1/file2.txt', 'File 2', 1377993602);
        self::file_put_contents_at_time($CFG->tempdir . '/dir1/dir2/file3.txt', 'File 3', 1377993603);
        $filelist['out2'] = $CFG->tempdir . '/dir1';

                $context = context_system::instance();
        $filerecord = array('contextid' => $context->id, 'component' => 'phpunit',
                'filearea' => 'data', 'itemid' => 0, 'filepath' => '/',
                'filename' => 'file4.txt', 'timemodified' => 1377993604);
        $fs = get_file_storage();
        $sf = $fs->create_file_from_string($filerecord, 'File 4');
        $filelist['out3.txt'] = $sf;

                 $filerecord['itemid'] = 1;
        $filerecord['filepath'] = '/dir1/';
        $filerecord['filename'] = 'file5.txt';
        $filerecord['timemodified'] = 1377993605;
        $fs->create_file_from_string($filerecord, 'File 5');
        $filerecord['filepath'] = '/dir1/dir2/';
        $filerecord['filename'] = 'file6.txt';
        $filerecord['timemodified'] = 1377993606;
        $fs->create_file_from_string($filerecord, 'File 6');
        $filerecord['filepath'] = '/';
        $filerecord['filename'] = 'excluded.txt';
        $fs->create_file_from_string($filerecord, 'Excluded');
        $filelist['out4'] = $fs->get_file($context->id, 'phpunit', 'data', 1, '/dir1/', '.');

                $filelist['out5.txt'] = array('File 7');

                $filelist['out6'] = null;

        return $filelist;
    }

    
    public function test_get_packer() {
        $packer = get_file_packer('application/x-gzip');
        $this->assertInstanceOf('tgz_packer', $packer);
    }

    
    public function test_to_normal_files() {
        global $CFG;
        $packer = get_file_packer('application/x-gzip');

                $files = $this->prepare_file_list();
        $archivefile = $CFG->tempdir . '/test.tar.gz';
        $packer->archive_to_pathname($files, $archivefile);

                $outdir = $CFG->tempdir . '/out';
        check_dir_exists($outdir);
        $result = $packer->extract_to_pathname($archivefile, $outdir);

                        $expectedpaths = array('out1.txt', 'out2/', 'out2/dir2/', 'out2/dir2/file3.txt',
                'out2/file2.txt', 'out3.txt', 'out4/', 'out4/dir2/', 'out4/file5.txt',
                'out4/dir2/file6.txt', 'out5.txt', 'out6/');
        sort($expectedpaths);
        $actualpaths = array_keys($result);
        sort($actualpaths);
        $this->assertEquals($expectedpaths, $actualpaths);
        foreach ($result as $path => $booleantrue) {
            $this->assertTrue($booleantrue);
        }

                $this->assertEquals('File 1', file_get_contents($outdir . '/out1.txt'));
        $this->assertEquals('File 2', file_get_contents($outdir . '/out2/file2.txt'));
        $this->assertEquals('File 3', file_get_contents($outdir . '/out2/dir2/file3.txt'));
        $this->assertEquals('File 4', file_get_contents($outdir . '/out3.txt'));
        $this->assertEquals('File 5', file_get_contents($outdir . '/out4/file5.txt'));
        $this->assertEquals('File 6', file_get_contents($outdir . '/out4/dir2/file6.txt'));
        $this->assertEquals('File 7', file_get_contents($outdir . '/out5.txt'));
        $this->assertTrue(is_dir($outdir . '/out6'));
    }

    
    public function test_to_stored_files() {
        global $CFG;
        $packer = get_file_packer('application/x-gzip');

                $files = $this->prepare_file_list();
        $archivefile = $CFG->tempdir . '/test.tar.gz';
        $context = context_system::instance();
        $sf = $packer->archive_to_storage($files,
                $context->id, 'phpunit', 'archive', 1, '/', 'archive.tar.gz');
        $this->assertInstanceOf('stored_file', $sf);

                $outdir = $CFG->tempdir . '/out';
        check_dir_exists($outdir);
        $packer->extract_to_pathname($sf, $outdir);

                $this->assertEquals('File 1', file_get_contents($outdir . '/out1.txt'));
        $this->assertEquals('File 2', file_get_contents($outdir . '/out2/file2.txt'));
        $this->assertEquals('File 3', file_get_contents($outdir . '/out2/dir2/file3.txt'));
        $this->assertEquals('File 4', file_get_contents($outdir . '/out3.txt'));
        $this->assertEquals('File 5', file_get_contents($outdir . '/out4/file5.txt'));
        $this->assertEquals('File 6', file_get_contents($outdir . '/out4/dir2/file6.txt'));
        $this->assertEquals('File 7', file_get_contents($outdir . '/out5.txt'));
        $this->assertTrue(is_dir($outdir . '/out6'));

                $packer->extract_to_storage($sf, $context->id, 'phpunit', 'data', 2, '/out/');
        $fs = get_file_storage();
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/', 'out1.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 1', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/out2/', 'file2.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 2', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/out2/dir2/', 'file3.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 3', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/', 'out3.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 4', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/out4/', 'file5.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 5', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/out4/dir2/', 'file6.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 6', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/', 'out5.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('File 7', $out->get_content());
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/out/out6/', '.');
        $this->assertNotEmpty($out);
        $this->assertTrue($out->is_directory());

                        $sf = $packer->archive_to_storage($files,
                $context->id, 'phpunit', 'archive', 1, '/', 'archive.tar.gz');
        $this->assertInstanceOf('stored_file', $sf);
        $packer->extract_to_storage($sf, $context->id, 'phpunit', 'data', 2, '/out/');
    }

    
    public function test_only_specified_files() {
        global $CFG;
        $packer = get_file_packer('application/x-gzip');

                $files = $this->prepare_file_list();
        $archivefile = $CFG->tempdir . '/test.tar.gz';
        $packer->archive_to_pathname($files, $archivefile);

                $outdir = $CFG->tempdir . '/out';
        check_dir_exists($outdir);
        $result = $packer->extract_to_pathname($archivefile, $outdir,
                array('out3.txt', 'out6/', 'out4/file5.txt'));

                $expectedpaths = array('out3.txt', 'out4/file5.txt', 'out6/');
        sort($expectedpaths);
        $actualpaths = array_keys($result);
        sort($actualpaths);
        $this->assertEquals($expectedpaths, $actualpaths);

                $this->assertFalse(file_exists($outdir . '/out1.txt'));
        $this->assertEquals('File 4', file_get_contents($outdir . '/out3.txt'));
        $this->assertEquals('File 5', file_get_contents($outdir . '/out4/file5.txt'));
        $this->assertTrue(is_dir($outdir . '/out6'));
    }

    
    public function test_file_progress() {
        global $CFG;

                $filelist = $this->prepare_file_list();
        $packer = get_file_packer('application/x-gzip');
        $archive = "$CFG->tempdir/archive.tgz";
        $context = context_system::instance();

                $this->progress = array();
        $result = $packer->archive_to_pathname($filelist, $archive, true, $this);
        $this->assertTrue($result);
                $this->assertTrue(count($this->progress) >= count($filelist));
                $this->check_progress_toward_max();

                $this->progress = array();
        $archivefile = $packer->archive_to_storage($filelist, $context->id,
                'phpunit', 'test', 0, '/', 'archive.tgz', null, true, $this);
        $this->assertInstanceOf('stored_file', $archivefile);
        $this->assertTrue(count($this->progress) >= count($filelist));
        $this->check_progress_toward_max();

                $this->progress = array();
        $target = "$CFG->tempdir/test/";
        check_dir_exists($target);
        $result = $packer->extract_to_pathname($archive, $target, null, $this);
        remove_dir($target);
                $this->assertTrue(count($this->progress) >= 1);
        $this->check_progress_toward_max();

                $this->progress = array();
        $result = $packer->extract_to_storage($archivefile, $context->id,
                'phpunit', 'target', 0, '/', null, $this);
        $this->assertTrue(count($this->progress) >= 1);
        $this->check_progress_toward_max();

                $this->progress = array();
        $result = $packer->extract_to_storage($archive, $context->id,
                'phpunit', 'target', 0, '/', null, $this);
        $this->assertTrue(count($this->progress) >= 1);
        $this->check_progress_toward_max();

                unlink($archive);
    }

    
    public function test_list_files() {
        global $CFG;

                $filelist = $this->prepare_file_list();
        $packer = get_file_packer('application/x-gzip');
        $archive = "$CFG->tempdir/archive.tgz";

                $packer = get_file_packer('application/x-gzip');
        $result = $packer->archive_to_pathname($filelist, $archive, true, $this);
        $this->assertTrue($result);
        $hashwith = sha1_file($archive);

                $files = $packer->list_files($archive);

                $expectedinfo = array(
            array('out1.txt', 1377993601, false, 6),
            array('out2/', tgz_packer::DEFAULT_TIMESTAMP, true, 0),
            array('out2/dir2/', tgz_packer::DEFAULT_TIMESTAMP, true, 0),
            array('out2/dir2/file3.txt', 1377993603, false, 6),
            array('out2/file2.txt', 1377993602, false, 6),
            array('out3.txt', 1377993604, false, 6),
            array('out4/', tgz_packer::DEFAULT_TIMESTAMP, true, 0),
            array('out4/dir2/', tgz_packer::DEFAULT_TIMESTAMP, true, 0),
            array('out4/dir2/file6.txt', 1377993606, false, 6),
            array('out4/file5.txt', 1377993605, false, 6),
            array('out5.txt', tgz_packer::DEFAULT_TIMESTAMP, false, 6),
            array('out6/', tgz_packer::DEFAULT_TIMESTAMP, true, 0),
        );
        $this->assertEquals($expectedinfo, self::convert_info_for_assert($files));

                $this->progress = array();
        $packer->set_include_index(false);
        $result = $packer->archive_to_pathname($filelist, $archive, true, $this);
        $this->assertTrue($result);
        $hashwithout = sha1_file($archive);
        $files = $packer->list_files($archive);
        $this->assertEquals($expectedinfo, self::convert_info_for_assert($files));

                $this->assertNotEquals($hashwith, $hashwithout);

                $packer->set_include_index(true);
    }

    
    protected static function convert_info_for_assert(array $files) {
        $actualinfo = array();
        foreach ($files as $file) {
            $actualinfo[] = array($file->pathname, $file->mtime, $file->is_directory, $file->size);
        }
        usort($actualinfo, function($a, $b) {
            return strcmp($a[0], $b[0]);
        });
        return $actualinfo;
    }

    public function test_is_tgz_file() {
        global $CFG;

                $filelist = $this->prepare_file_list();
        $packer1 = get_file_packer('application/x-gzip');
        $packer2 = get_file_packer('application/zip');
        $archive2 = "$CFG->tempdir/archive.zip";

                $context = context_system::instance();
        $archive1 = $packer1->archive_to_storage($filelist, $context->id,
                'phpunit', 'test', 0, '/', 'archive.tgz', null, true, $this);
        $this->assertInstanceOf('stored_file', $archive1);
        $result = $packer2->archive_to_pathname($filelist, $archive2);
        $this->assertTrue($result);

                        $this->assertTrue(tgz_packer::is_tgz_file($archive1));
        $this->assertFalse(tgz_packer::is_tgz_file($archive2));
    }

    
    protected function check_progress_toward_max() {
        $lastvalue = -1; $lastmax = -1;
        foreach ($this->progress as $progressitem) {
            list($value, $max) = $progressitem;
            if ($lastmax != -1) {
                $this->assertEquals($max, $lastmax);
            } else {
                $lastmax = $max;
            }
            $this->assertTrue(is_integer($value));
            $this->assertTrue(is_integer($max));
            $this->assertNotEquals(file_progress::INDETERMINATE, $max);
            $this->assertTrue($value <= $max);
            $this->assertTrue($value >= $lastvalue);
            $lastvalue = $value;
        }
    }

    
    public function progress($progress = file_progress::INDETERMINATE, $max = file_progress::INDETERMINATE) {
        $this->progress[] = array($progress, $max);
    }
}
