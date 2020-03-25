<?php




class edit_table_save extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'tablenameempty' => 'tool_xmldb',
            'incorrecttablename' => 'tool_xmldb',
            'duplicatetablename' => 'tool_xmldb',
            'back' => 'tool_xmldb',
            'administration' => ''
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                        $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
        if (!data_submitted()) {             print_error('wrongcall', 'error');
        }

                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

        $tableparam = strtolower(required_param('table', PARAM_PATH));
        $name = substr(trim(strtolower(required_param('name', PARAM_PATH))),0,xmldb_table::NAME_MAX_LENGTH);
        $comment = required_param('comment', PARAM_CLEAN);
        $comment = $comment;

        $dbdir = $XMLDB->dbdirs[$dirpath];

        $editeddir = $XMLDB->editeddirs[$dirpath];
        $structure = $editeddir->xml_file->getStructure();
        $table = $structure->getTable($tableparam);

        $errors = array(); 
                        if (empty($name)) {
            $errors[] = $this->str['tablenameempty'];
        }
                if ($name == 'changeme') {
            $errors[] = $this->str['incorrecttablename'];
        }
                if ($tableparam != $name && $structure->getTable($name)) {
            $errors[] = $this->str['duplicatetablename'];
        }

        if (!empty($errors)) {
            $temptable = new xmldb_table($name);
                            $o = '<p>' .implode(', ', $errors) . '</p>
                  <p>' . $temptable->getName() . '</p>';
            $o.= '<a href="index.php?action=edit_table&amp;table=' . $tableparam .
                 '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
            $this->output = $o;


                } else if (empty($errors)) {
                                    if ($tableparam != $name) {
                $table->setName($name);
                if ($table->getPrevious()) {
                    $prev = $structure->getTable($table->getPrevious());
                    $prev->setNext($name);
                    $prev->setChanged(true);
                }
                if ($table->getNext()) {
                    $next = $structure->getTable($table->getNext());
                    $next->setPrevious($name);
                    $next->setChanged(true);
                }
                                $table->setChanged(true);
            }

                        if ($table->getComment() != $comment) {
                $table->setComment($comment);
                                $table->setChanged(true);
            }

                        $structure->calculateHash(true);

                                    $origstructure = $dbdir->xml_file->getStructure();
            if ($structure->getHash() != $origstructure->getHash()) {
                $structure->setVersion(userdate(time(), '%Y%m%d', 99, false));
                $structure->setChanged(true);
            }

                        if ($this->getPostAction() && $result) {
                return $this->launch($this->getPostAction());
            }
        }

                return $result;
    }
}

