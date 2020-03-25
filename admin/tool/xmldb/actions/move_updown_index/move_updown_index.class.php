<?php




class move_updown_index extends XMLDBAction {

    
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
        $indexparam = required_param('index', PARAM_CLEAN);
        $direction  = required_param('direction', PARAM_ALPHA);
        $tables = $structure->getTables();
        $table = $structure->getTable($tableparam);
        $indexes = $table->getIndexes();
        if ($direction == 'down') {
            $index = $table->getIndex($indexparam);
            $swap  = $table->getIndex($index->getNext());
        } else {
            $swap  = $table->getIndex($indexparam);
            $index = $table->getIndex($swap->getPrevious());
        }

                if ($index->getPrevious()) {
            $prev = $table->getIndex($index->getPrevious());
            $prev->setNext($swap->getName());
            $swap->setPrevious($prev->getName());
            $prev->setChanged(true);
        } else {
            $swap->setPrevious(NULL);
        }
                if ($swap->getNext()) {
            $next = $table->getIndex($swap->getNext());
            $next->setPrevious($index->getName());
            $index->setNext($next->getName());
            $next->setChanged(true);
        } else {
            $index->setNext(NULL);
        }
                $index->setPrevious($swap->getName());
        $swap->setNext($index->getName());

                $index->setChanged(true);
        $swap->setChanged(true);

                $table->setChanged(true);

                $table->orderIndexes();

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

