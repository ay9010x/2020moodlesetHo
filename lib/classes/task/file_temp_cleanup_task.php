<?php


namespace core\task;


class file_temp_cleanup_task extends scheduled_task {

    
    public function get_name() {
        return get_string('tasktempfilecleanup', 'admin');
    }

    
    public function execute() {
        global $CFG;

        $tmpdir = $CFG->tempdir;
                $time = time() - ($CFG->tempdatafoldercleanup * 3600);

        $dir = new \RecursiveDirectoryIterator($tmpdir);
                $iter = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);

                $modifieddateobject = array();

                        for ($iter->rewind(); $iter->valid(); $iter->next()) {
            $node = $iter->getRealPath();
            if (!is_readable($node)) {
                continue;
            }
            $modifieddateobject[$node] = $iter->getMTime();
        }

                for ($iter->rewind(); $iter->valid(); $iter->next()) {
            $node = $iter->getRealPath();
            if (!is_readable($node)) {
                continue;
            }

                        if ($modifieddateobject[$node] < $time) {
                if ($iter->isDir() && !$iter->isDot()) {
                                        if (!glob($node. DIRECTORY_SEPARATOR . '*')) {
                        if (@rmdir($node) === false) {
                            mtrace("Failed removing directory '$node'.");
                        }
                    }
                }
                if ($iter->isFile()) {
                    if (@unlink($node) === false) {
                        mtrace("Failed removing file '$node'.");
                    }
                }
            } else {
                                if ($iter->isDir() && !$iter->isDot()) {
                    touch($node, $modifieddateobject[$node]);
                }
            }
        }
    }

}
