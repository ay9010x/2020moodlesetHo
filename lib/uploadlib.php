<?php




defined('MOODLE_INTERNAL') || die();


class upload_manager {

    
    function __construct($inputname='', $deleteothers=false, $handlecollisions=false, $course=null, $recoverifmultiple=false, $modbytes=0, $silent=false, $allownull=false, $allownullmultiple=true) {
        throw new coding_exception('upload_manager class can not be used any more, please use file picker instead');
    }
}
