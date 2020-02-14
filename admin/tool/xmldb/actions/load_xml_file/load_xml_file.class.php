<?php




class load_xml_file extends XMLDBAction {

    
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

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

                if (!empty($XMLDB->dbdirs)) {
            $dbdir = $XMLDB->dbdirs[$dirpath];
            if ($dbdir) {
                                $dbdir->xml_exists = false;
                $dbdir->xml_writeable = false;
                $dbdir->xml_loaded  = false;
                                if (!$dbdir->path_exists) {
                    return false;
                }
                $xmldb_file = new xmldb_file($dbdir->path . '/install.xml');
                                $xmldb_file->setDTD($CFG->dirroot . '/lib/xmldb/xmldb.dtd');
                $xmldb_file->setSchema($CFG->dirroot . '/lib/xmldb/xmldb.xsd');
                                if ($xmldb_file->fileExists()) {
                    $dbdir->xml_exists = true;
                }
                if ($xmldb_file->fileWriteable()) {
                    $dbdir->xml_writeable = true;
                }
                                $loaded = $xmldb_file->loadXMLStructure();
                if ($loaded && $xmldb_file->isLoaded()) {
                    $dbdir->xml_loaded = true;
                    $dbdir->filemtime = filemtime($dbdir->path . '/install.xml');
                }
                $dbdir->xml_file = $xmldb_file;
            } else {
                $this->errormsg = 'Wrong directory (' . $dirpath . ')';
                $result = false;
            }
        } else {
            $this->errormsg = 'XMLDB structure not found';
            $result = false;
        }
                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

        return $result;
    }
}

