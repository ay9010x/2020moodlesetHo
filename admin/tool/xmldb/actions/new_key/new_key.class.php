<?php




class new_key extends XMLDBAction {

    
    function init() {
        parent::init();

        
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
        } else {
            return false;
        }
        if (!empty($XMLDB->editeddirs)) {
            $editeddir = $XMLDB->editeddirs[$dirpath];
            $structure = $editeddir->xml_file->getStructure();
        }

        $tableparam = required_param('table', PARAM_CLEAN);

        $table = $structure->getTable($tableparam);

                $changeme_exists = false;
        if ($keys = $table->getKeys()) {
            if ($key = $table->getKey('changeme')) {
                $changeme_exists = true;
            }
        }
        if (!$changeme_exists) {             $key = new xmldb_key('changeme');
            $table->addKey($key);

                        $structure->setVersion(userdate(time(), '%Y%m%d', 99, false));
            $structure->setChanged(true);
        }
                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

