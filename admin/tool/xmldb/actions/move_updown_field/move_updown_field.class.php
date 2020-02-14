<?php




class move_updown_field extends XMLDBAction {

    
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

        $prev = NULL;
        $next = NULL;
        $tableparam = required_param('table', PARAM_CLEAN);
        $fieldparam = required_param('field', PARAM_CLEAN);
        $direction  = required_param('direction', PARAM_ALPHA);
        $tables = $structure->getTables();
        $table = $structure->getTable($tableparam);
        $fields = $table->getFields();
        if ($direction == 'down') {
            $field  = $table->getField($fieldparam);
            $swap   = $table->getField($field->getNext());
        } else {
            $swap   = $table->getField($fieldparam);
            $field  = $table->getField($swap->getPrevious());
        }

                if ($field->getPrevious()) {
            $prev = $table->getField($field->getPrevious());
            $prev->setNext($swap->getName());
            $swap->setPrevious($prev->getName());
            $prev->setChanged(true);
        } else {
            $swap->setPrevious(NULL);
        }
                if ($swap->getNext()) {
            $next = $table->getField($swap->getNext());
            $next->setPrevious($field->getName());
            $field->setNext($next->getName());
            $next->setChanged(true);
        } else {
            $field->setNext(NULL);
        }
                $field->setPrevious($swap->getName());
        $swap->setNext($field->getName());

                $field->setChanged(true);
        $swap->setChanged(true);

                $table->setChanged(true);

                $table->orderFields();

                $structure->calculateHash(true);

                        $origstructure = $dbdir->xml_file->getStructure();
        if ($structure->getHash() != $origstructure->getHash()) {
            $structure->setVersion(userdate(time(), '%Y%m%d', 99, false));
            $structure->setChanged(true);
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

