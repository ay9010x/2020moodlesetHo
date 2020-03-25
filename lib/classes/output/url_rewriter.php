<?php



namespace core\output;

defined('MOODLE_INTERNAL') || die();


interface url_rewriter {

    
    public static function url_rewrite(\moodle_url $url);

    
    public static function html_head_setup();


}

