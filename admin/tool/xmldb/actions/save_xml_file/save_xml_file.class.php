<?php




class save_xml_file extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'filenotwriteable' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;

                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;
        $unload = optional_param('unload', true, PARAM_BOOL);

                if (!empty($XMLDB->editeddirs)) {
            if (isset($XMLDB->editeddirs[$dirpath])) {
                $editeddir = $XMLDB->editeddirs[$dirpath];
            }
        }
                if (!empty($XMLDB->dbdirs)) {
            if (isset($XMLDB->dbdirs[$dirpath])) {
                $XMLDB->dbdirs[$dirpath] = unserialize(serialize($editeddir));
                $dbdir = $XMLDB->dbdirs[$dirpath];
            }
        }

                if (!is_writeable($dirpath . '/install.xml')) {
            $this->errormsg = $this->str['filenotwriteable'] . '(' . $dirpath . '/install.xml)';
            return false;
        }

                $result = $dbdir->xml_file->saveXMLFile();

        if ($result) {
                        unset ($XMLDB->editeddirs[$dirpath]);
                        unset($XMLDB->dbdirs[$dirpath]->xml_file);
            unset($XMLDB->dbdirs[$dirpath]->xml_loaded);
            unset($XMLDB->dbdirs[$dirpath]->xml_changed);
            unset($XMLDB->dbdirs[$dirpath]->xml_exists);
            unset($XMLDB->dbdirs[$dirpath]->xml_writeable);
        } else {
            $this->errormsg = 'Error saving XML file (' . $dirpath . ')';
            return false;
        }

                if (!$unload) {
            return $this->launch('load_xml_file');
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

