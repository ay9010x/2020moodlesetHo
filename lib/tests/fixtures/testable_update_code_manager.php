<?php



namespace core\update;

defined('MOODLE_INTERNAL') || die();


class testable_code_manager extends code_manager {

    
    public $downloadscounter = 0;

    
    protected function download_file_content($url, $tofile) {
        $this->downloadscounter++;
        file_put_contents($tofile, $url);
        return true;
    }
}
