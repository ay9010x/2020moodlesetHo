<?php




class unload_xml_file extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;

                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

                if (!empty($XMLDB->dbdirs)) {
            if (isset($XMLDB->dbdirs[$dirpath])) {
                $dbdir = $XMLDB->dbdirs[$dirpath];
                if ($dbdir) {
                    unset($dbdir->xml_file);
                    unset($dbdir->xml_loaded);
                    unset($dbdir->xml_changed);
                    unset($dbdir->xml_exists);
                    unset($dbdir->xml_writeable);
                }
            }
        }
                if (!empty($XMLDB->editeddirs)) {
            if (isset($XMLDB->editeddirs[$dirpath])) {
                unset($XMLDB->editeddirs[$dirpath]);
            }
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

