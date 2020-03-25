<?php




class get_db_directories extends XMLDBAction {

    
    function init() {
        parent::init();
                $this->can_subaction = ACTION_NONE;
        
                $this->sesskey_protected = false; 
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;
        
                global $CFG, $XMLDB;

        
                        if (!isset($XMLDB->dbdirs)) {
            $XMLDB->dbdirs = array();
        }

                $db_directories = get_db_directories();
        foreach ($db_directories as $path) {
            $dbdir = new stdClass;
            $dbdir->path = $path;
            if (!isset($XMLDB->dbdirs[$dbdir->path])) {
                $XMLDB->dbdirs[$dbdir->path] = $dbdir;
             }
            $XMLDB->dbdirs[$dbdir->path]->path_exists = file_exists($dbdir->path);           }

                ksort($XMLDB->dbdirs);

                return true;
    }
}

