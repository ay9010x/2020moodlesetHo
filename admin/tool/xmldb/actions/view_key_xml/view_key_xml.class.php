<?php




class view_key_xml extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_XML;

                global $CFG, $XMLDB;

        
                $key =  required_param('key', PARAM_PATH);
        $table =  required_param('table', PARAM_PATH);
        $select = required_param('select', PARAM_ALPHA);                 $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

                if ($select == 'original') {
            if (!empty($XMLDB->dbdirs)) {
                $base = $XMLDB->dbdirs[$dirpath];
            }
        } else if ($select == 'edited') {
            if (!empty($XMLDB->editeddirs)) {
                $base = $XMLDB->editeddirs[$dirpath];
            }
        } else {
            $this->errormsg = 'Cannot access to ' . $select . ' info';
            $result = false;
        }
        if ($base) {
                        if (!$base->path_exists || !$base->xml_loaded) {
                $this->errormsg = 'Directory ' . $dirpath . ' not loaded';
                return false;
            }
        } else {
            $this->errormsg = 'Problem handling ' . $select . ' files';
            return false;
        }

                if ($result) {
            if (!$structure = $base->xml_file->getStructure()) {
                $this->errormsg = 'Error retrieving ' . $select . ' structure';
                $result = false;
            }
        }
                if ($result) {
            if (!$tables = $structure->getTables()) {
                $this->errormsg = 'Error retrieving ' . $select . ' tables';
                $result = false;
            }
        }
                if ($result && !$t = $structure->getTable($table)) {
            $this->errormsg = 'Error retrieving ' . $table . ' table';
            $result = false;
        }
                if ($result) {
            if (!$keys = $t->getKeys()) {
                $this->errormsg = 'Error retrieving ' . $select . ' keys';
                $result = false;
            }
        }
                if ($result && !$k = $t->getKey($key)) {
            $this->errormsg = 'Error retrieving ' . $key . ' key';
            $result = false;
        }

        if ($result) {
                        $this->output = $k->xmlOutput();
        } else {
                        $this->does_generate = ACTION_GENERATE_HTML;
        }

                return $result;
    }
}

