<?php




class load_xml_files extends XMLDBAction {

    
    function init() {
        parent::init();
                $this->can_subaction = ACTION_NONE;
        
        
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;
        
                global $CFG, $XMLDB;

        
                if ($XMLDB->dbdirs) {
            $dbdirs = $XMLDB->dbdirs;
            foreach ($dbdirs as $dbdir) {
                                $dbdir->xml_exists = false;
                $dbdir->xml_writeable = false;
                $dbdir->xml_loaded  = false;
                                if (!$dbdir->path_exists) {
                    continue;
                }
                $xmldb_file = new xmldb_file($dbdir->path . '/install.xml');
                                if ($xmldb_file->fileExists()) {
                    $dbdir->xml_exists = true;
                }
                if ($xmldb_file->fileWriteable()) {
                    $dbdir->xml_writeable = true;
                }
                                $loaded = $xmldb_file->loadXMLStructure();
                if ($loaded && $xmldb_file->isLoaded()) {
                    $dbdir->xml_loaded = true;
                }
                $dbdir->xml_file = $xmldb_file;
            }
        }
        return $result;
    }
}

