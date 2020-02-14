<?php




class move_updown_key extends XMLDBAction {

    
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
        $keyparam = required_param('key', PARAM_CLEAN);
        $direction  = required_param('direction', PARAM_ALPHA);
        $tables = $structure->getTables();
        $table = $structure->getTable($tableparam);
        $keys = $table->getKeys();
        if ($direction == 'down') {
            $key = $table->getKey($keyparam);
            $swap = $table->getKey($key->getNext());
        } else {
            $swap = $table->getKey($keyparam);
            $key = $table->getKey($swap->getPrevious());
        }

                if ($key->getPrevious()) {
            $prev = $table->getKey($key->getPrevious());
            $prev->setNext($swap->getName());
            $swap->setPrevious($prev->getName());
            $prev->setChanged(true);
        } else {
            $swap->setPrevious(NULL);
        }
                if ($swap->getNext()) {
            $next = $table->getKey($swap->getNext());
            $next->setPrevious($key->getName());
            $key->setNext($next->getName());
            $next->setChanged(true);
        } else {
            $key->setNext(NULL);
        }
                $key->setPrevious($swap->getName());
        $swap->setNext($key->getName());

                $key->setChanged(true);
        $swap->setChanged(true);

                $table->setChanged(true);

                $table->orderKeys();

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

