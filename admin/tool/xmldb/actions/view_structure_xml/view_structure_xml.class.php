<?php




class view_structure_xml extends XMLDBAction {

    
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
                        $this->output = $structure->xmlOutput();
        } else {
                        $this->does_generate = ACTION_GENERATE_HTML;
        }

                return $result;
    }
}

