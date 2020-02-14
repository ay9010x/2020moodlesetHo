<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filestorage/file_progress.php');

class core_files_mbz_packer_testcase extends advanced_testcase {

    public function test_archive_with_both_options() {
        global $CFG;
        $this->resetAfterTest();

                $packer = get_file_packer('application/vnd.moodle.backup');
        require_once($CFG->dirroot . '/lib/filestorage/tgz_packer.php');

                $files = array('1.txt' => array('frog'));

                $CFG->usezipbackups = true;
        $filefalse = $CFG->tempdir . '/false.mbz';
        $this->assertNotEmpty($packer->archive_to_pathname($files, $filefalse));
        $context = context_system::instance();
        $this->assertNotEmpty($storagefalse = $packer->archive_to_storage(
                $files, $context->id, 'phpunit', 'data', 0, '/', 'false.mbz'));

                $CFG->usezipbackups = false;
        $filetrue = $CFG->tempdir . '/true.mbz';
        $this->assertNotEmpty($packer->archive_to_pathname($files, $filetrue));
        $context = context_system::instance();
        $this->assertNotEmpty($storagetrue = $packer->archive_to_storage(
                $files, $context->id, 'phpunit', 'data', 0, '/', 'false.mbz'));

                $this->assertNotEquals(filesize($filefalse), filesize($filetrue));
        $this->assertNotEquals($storagefalse->get_filesize(), $storagetrue->get_filesize());

        
                $packer->extract_to_pathname($filefalse, $CFG->tempdir);
        $onefile = $CFG->tempdir . '/1.txt';
        $this->assertEquals('frog', file_get_contents($onefile));
        unlink($onefile);

                $packer->extract_to_pathname($filetrue, $CFG->tempdir);
        $onefile = $CFG->tempdir . '/1.txt';
        $this->assertEquals('frog', file_get_contents($onefile));
        unlink($onefile);

                $packer->extract_to_storage($storagefalse, $context->id, 'phpunit', 'data', 1, '/');
        $fs = get_file_storage();
        $out = $fs->get_file($context->id, 'phpunit', 'data', 1, '/', '1.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('frog', $out->get_content());

                $packer->extract_to_storage($storagetrue, $context->id, 'phpunit', 'data', 2, '/');
        $out = $fs->get_file($context->id, 'phpunit', 'data', 2, '/', '1.txt');
        $this->assertNotEmpty($out);
        $this->assertEquals('frog', $out->get_content());
    }
}
