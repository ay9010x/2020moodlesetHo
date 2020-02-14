<?php



defined('MOODLE_INTERNAL') || die();


abstract class moodle_recordset implements Iterator {

    
    
    
    
    
    
    
    public function rewind() {
                return;
    }

    
    
    
    public abstract function close();
}
