<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/cronlib.php');

class cronlib_testcase extends basic_testcase {

    
    public function cron_delete_from_temp_provider() {
        global $CFG;

        $tmpdir = realpath($CFG->tempdir);
                $time = 0;

                $lastweekstime = -($CFG->tempdatafoldercleanup * 3600);         $beforelastweekstime = $lastweekstime - 3600 - 1;         $afterlastweekstime = $lastweekstime + 3600 + 1; 
        $nodes = array();
                $nodes[] = $this->generate_test_path('/dir1/dir1_1/dir1_1_1/dir1_1_1_1/', true, $lastweekstime * 52, false);

                $nodes[] = $this->generate_test_path('/dir1/dir1_2/', true, $time, true);

                $nodes[] = $this->generate_test_path('/dir2/', true, $afterlastweekstime, true);

                $nodes[] = $this->generate_test_path('/dir3/', true, $beforelastweekstime, false);

                $nodes[] = $this->generate_test_path('/dir1/dir1_1/dir1_1_1/file1_1_1_1', false, $beforelastweekstime, false);

                $nodes[] = $this->generate_test_path('/dir1/dir1_1/dir1_1_1/file1_1_1_2', false, $time, true);

                $nodes[] = $this->generate_test_path('/dir1/dir1_2/file1_1_2_1', false, $beforelastweekstime, false);

                $nodes[] = $this->generate_test_path('/dir1/dir1_2/file1_1_2_2', false, $time, true);

                $nodes[] = $this->generate_test_path('/file1', false, $time, true);

                $nodes[] = $this->generate_test_path('/file2', false, $beforelastweekstime, false);

                        
        $nodes[] = $this->generate_test_path('/dir4/dir4_1', true, $beforelastweekstime, true);

        $nodes[] = $this->generate_test_path('/dir4/dir4_1/dir4_1_1/', true, $beforelastweekstime, true);

                $nodes[] = $this->generate_test_path('/dir4/dir4_1/dir4_1_1/file4_1_1_1', false, $beforelastweekstime, false);

        $expectednodes = array();
        foreach ($nodes as $node) {
            if ($node->keep) {
                $path = $tmpdir;
                $pelements = preg_split('/\//', $node->path);
                foreach ($pelements as $pelement) {
                    if ($pelement === '') {
                        continue;
                    }
                    $path .= DIRECTORY_SEPARATOR . $pelement;
                    if (!in_array($path, $expectednodes)) {
                        $expectednodes[] = $path;
                    }
                }
            }
        }
        sort($expectednodes);

        $data = array(
                array(
                    $nodes,
                    $expectednodes
                ),
                array(
                    array(),
                    array()
                )
        );

        return $data;
    }

    
    private function generate_test_path($path, $isdir = false, $time = 0, $keep = false) {
        $node = new stdClass();
        $node->path = $path;
        $node->isdir = $isdir;
        $node->time = $time;
        $node->keep = $keep;
        return $node;
    }
    
    public function test_cron_delete_from_temp($nodes, $expected) {
        global $CFG;

        $tmpdir = $CFG->tempdir;

        foreach ($nodes as $data) {
            if ($data->isdir) {
                mkdir($tmpdir.$data->path, $CFG->directorypermissions, true);
            }
        }
                        foreach ($nodes as $data) {
            touch($tmpdir.$data->path, time() + $data->time);
        }

        $task = new \core\task\file_temp_cleanup_task();
        $task->execute();

        $dir = new RecursiveDirectoryIterator($tmpdir);
        $iter = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);

        $actual = array();
        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            if (!$iter->isDot()) {
                $actual[] = $iter->getRealPath();
            }
        }

                sort($actual);

        $this->assertEquals($expected, $actual);
    }
}
