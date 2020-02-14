<?php




class edit_index_save extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'indexnameempty' => 'tool_xmldb',
            'incorrectindexname' => 'tool_xmldb',
            'duplicateindexname' => 'tool_xmldb',
            'nofieldsspecified' => 'tool_xmldb',
            'duplicatefieldsused' => 'tool_xmldb',
            'fieldsnotintable' => 'tool_xmldb',
            'fieldsusedinkey' => 'tool_xmldb',
            'fieldsusedinindex' => 'tool_xmldb',
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
        $indexparam = strtolower(required_param('index', PARAM_PATH));
        $name = trim(strtolower(optional_param('name', $indexparam, PARAM_PATH)));

        $comment = required_param('comment', PARAM_CLEAN);
        $comment = trim($comment);

        $unique = required_param('unique', PARAM_INT);
        $fields = required_param('fields', PARAM_CLEAN);
        $fields = str_replace(' ', '', trim(strtolower($fields)));
        $hints = required_param('hints', PARAM_CLEAN);
        $hints = str_replace(' ', '', trim(strtolower($hints)));

        $editeddir = $XMLDB->editeddirs[$dirpath];
        $structure = $editeddir->xml_file->getStructure();
        $table = $structure->getTable($tableparam);
        $index = $table->getIndex($indexparam);
        $oldhash = $index->getHash();

        $errors = array(); 
                        if (empty($name)) {
            $errors[] = $this->str['indexnameempty'];
        }
                if ($name == 'changeme') {
            $errors[] = $this->str['incorrectindexname'];
        }
                if ($indexparam != $name && $table->getIndex($name)) {
            $errors[] = $this->str['duplicateindexname'];
        }
        $fieldsarr = explode(',', $fields);
                if (empty($fieldsarr[0])) {
            $errors[] = $this->str['nofieldsspecified'];
        } else {
                        $uniquearr = array_unique($fieldsarr);
            if (count($fieldsarr) != count($uniquearr)) {
                $errors[] = $this->str['duplicatefieldsused'];
            }
                        foreach ($fieldsarr as $field) {
                if (!$table->getField($field)) {
                    $errors[] = $this->str['fieldsnotintable'];
                    break;
                }
            }
                        $tablekeys = $table->getKeys();
            if ($tablekeys) {
                foreach ($tablekeys as $tablekey) {
                    $keyfieldsarr = $tablekey->getFields();
                                        $diferences = array_merge(array_diff($fieldsarr, $keyfieldsarr), array_diff($keyfieldsarr, $fieldsarr));
                    if (empty($diferences)) {
                        $errors[] = $this->str['fieldsusedinkey'];
                        break;
                    }
                }
            }
                        $tableindexes = $table->getIndexes();
            if ($tableindexes) {
                foreach ($tableindexes as $tableindex) {
                                        if ($indexparam == $tableindex->getName()) {
                        continue;
                    }
                    $indexfieldsarr = $tableindex->getFields();
                                        $diferences = array_merge(array_diff($fieldsarr, $indexfieldsarr), array_diff($indexfieldsarr, $fieldsarr));
                    if (empty($diferences)) {
                        $errors[] = $this->str['fieldsusedinindex'];
                        break;
                    }
                }
            }
        }
        $hintsarr = array();
        foreach (explode(',', $hints) as $hint) {
            $hint = preg_replace('/[^a-z]/', '', $hint);
            if ($hint === '') {
                continue;
            }
            $hintsarr[] = $hint;
        }

        if (!empty($errors)) {
            $tempindex = new xmldb_index($name);
            $tempindex->setUnique($unique);
            $tempindex->setFields($fieldsarr);
            $tempindex->setHints($hintsarr);
                        $o = '<p>' .implode(', ', $errors) . '</p>
                  <p>' . $tempindex->readableInfo() . '</p>';
            $o.= '<a href="index.php?action=edit_index&amp;index=' .$index->getName() . '&amp;table=' . $table->getName() .
                 '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
            $this->output = $o;
        }

                if (empty($errors)) {
                                    if ($indexparam != $name) {
                $index->setName($name);
                if ($index->getPrevious()) {
                    $prev = $table->getIndex($index->getPrevious());
                    $prev->setNext($name);
                    $prev->setChanged(true);
                }
                if ($index->getNext()) {
                    $next = $table->getIndex($index->getNext());
                    $next->setPrevious($name);
                    $next->setChanged(true);
                }
            }

                        $index->setComment($comment);

                        $index->setUnique($unique);
            $index->setFields($fieldsarr);
            $index->setHints($hintsarr);

                                    $index->calculateHash(true);
            if ($oldhash != $index->getHash()) {
                $index->setChanged(true);
                $table->setChanged(true);
                                $structure->calculateHash(true);
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

