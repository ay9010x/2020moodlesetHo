<?php



namespace core\output;

use coding_exception;


class mustache_filesystem_loader extends \Mustache_Loader_FilesystemLoader {

    
    public function __construct() {
    }

    
    protected function getFileName($name) {
                return mustache_template_finder::get_template_filepath($name);
    }
}
