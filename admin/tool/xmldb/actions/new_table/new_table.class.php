<?php




class new_table extends XMLDBAction {

    
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

                $changeme_exists = false;
        if ($tables = $structure->getTables()) {
            if ($table = $structure->getTable('changeme')) {
                $changeme_exists = true;
            }
        }
        if (!$changeme_exists) {             $field = new xmldb_field('id');
            $field->setType(XMLDB_TYPE_INTEGER);
            $field->setLength(10);
            $field->setNotNull(true);
            $field->setSequence(true);
            $field->setLoaded(true);
            $field->setChanged(true);

            $key = new xmldb_key('primary');
            $key->setType(XMLDB_KEY_PRIMARY);
            $key->setFields(array('id'));
            $key->setLoaded(true);
            $key->setChanged(true);

            $table = new xmldb_table('changeme');
            $table->setComment('Default comment for the table, please edit me');
            $table->addField($field);
            $table->addKey($key);

                                    $structure->addTable($table);
        }
                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

