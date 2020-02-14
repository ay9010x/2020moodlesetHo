<?php




class move_updown_table extends XMLDBAction {

    
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
        $direction  = required_param('direction', PARAM_ALPHA);
        $tables = $structure->getTables();
        if ($direction == 'down') {
            $table = $structure->getTable($tableparam);
            $swap  = $structure->getTable($table->getNext());
        } else {
            $swap  = $structure->getTable($tableparam);
            $table = $structure->getTable($swap->getPrevious());
        }

                if ($table->getPrevious()) {
            $prev = $structure->getTable($table->getPrevious());
            $prev->setNext($swap->getName());
            $swap->setPrevious($prev->getName());
            $prev->setChanged(true);
        } else {
            $swap->setPrevious(NULL);
        }
                if ($swap->getNext()) {
            $next = $structure->getTable($swap->getNext());
            $next->setPrevious($table->getName());
            $table->setNext($next->getName());
            $next->setChanged(true);
        } else {
            $table->setNext(NULL);
        }
                $table->setPrevious($swap->getName());
        $swap->setNext($table->getName());

                $table->setChanged(true);

                $structure->orderTables();

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

