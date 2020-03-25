<?php




class edit_key_save extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'keynameempty' => 'tool_xmldb',
            'incorrectkeyname' => 'tool_xmldb',
            'duplicatekeyname' => 'tool_xmldb',
            'nofieldsspecified' => 'tool_xmldb',
            'duplicatefieldsused' => 'tool_xmldb',
            'fieldsnotintable' => 'tool_xmldb',
            'fieldsusedinkey' => 'tool_xmldb',
            'fieldsusedinindex' => 'tool_xmldb',
            'noreftablespecified' => 'tool_xmldb',
            'wrongnumberofreffields' => 'tool_xmldb',
            'noreffieldsspecified' => 'tool_xmldb',
            'nomasterprimaryuniquefound' => 'tool_xmldb',
            'masterprimaryuniqueordernomatch' => 'tool_xmldb',
            'primarykeyonlyallownotnullfields' => 'tool_xmldb',
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
        $keyparam = strtolower(required_param('key', PARAM_PATH));
        $name = trim(strtolower(optional_param('name', $keyparam, PARAM_PATH)));

        $comment = required_param('comment', PARAM_CLEAN);
        $comment = trim($comment);

        $type = required_param('type', PARAM_INT);
        $fields = required_param('fields', PARAM_CLEAN);
        $fields = str_replace(' ', '', trim(strtolower($fields)));

        if ($type == XMLDB_KEY_FOREIGN ||
            $type == XMLDB_KEY_FOREIGN_UNIQUE) {
            $reftable = trim(strtolower(required_param('reftable', PARAM_PATH)));
            $reffields= required_param('reffields', PARAM_CLEAN);
            $reffields = str_replace(' ', '', trim(strtolower($reffields)));
        }

        $editeddir = $XMLDB->editeddirs[$dirpath];
        $structure = $editeddir->xml_file->getStructure();
        $table = $structure->getTable($tableparam);
        $key = $table->getKey($keyparam);
        $oldhash = $key->getHash();

        $errors = array(); 
                        if (empty($name)) {
            $errors[] = $this->str['keynameempty'];
        }
                if ($name == 'changeme') {
            $errors[] = $this->str['incorrectkeyname'];
        }
                if ($keyparam != $name && $table->getKey($name)) {
            $errors[] = $this->str['duplicatekeyname'];
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
                        if ($type == XMLDB_KEY_PRIMARY) {
                foreach ($fieldsarr as $field) {
                    if ($fi = $table->getField($field)) {
                        if (!$fi->getNotNull()) {
                            $errors[] = $this->str['primarykeyonlyallownotnullfields'];
                            break;
                        }
                    }
                }
            }
                        $tablekeys = $table->getKeys();
            if ($tablekeys) {
                foreach ($tablekeys as $tablekey) {
                                        if ($keyparam == $tablekey->getName()) {
                        continue;
                    }
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
                    $indexfieldsarr = $tableindex->getFields();
                                        $diferences = array_merge(array_diff($fieldsarr, $indexfieldsarr), array_diff($indexfieldsarr, $fieldsarr));
                    if (empty($diferences)) {
                        $errors[] = $this->str['fieldsusedinindex'];
                        break;
                    }
                }
            }
                        if ($type == XMLDB_KEY_FOREIGN ||
                $type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $reffieldsarr = explode(',', $reffields);
                                if (empty($reftable)) {
                    $errors[] = $this->str['noreftablespecified'];
                } else
                                if (empty($reffieldsarr[0])) {
                    $errors[] = $this->str['noreffieldsspecified'];
                } else
                                if (count($fieldsarr) != count($reffieldsarr)) {
                    $errors[] = $this->str['wrongnumberofreffields'];
                } else {
                                    if ($rt = $structure->getTable($reftable)) {
                        $masterfound = false;
                        $reftablekeys = $rt->getKeys();
                        if ($reftablekeys) {
                            foreach ($reftablekeys as $reftablekey) {
                                                                if ($reftablekey->getType() != XMLDB_KEY_PRIMARY && $reftablekey->getType() != XMLDB_KEY_UNIQUE) {
                                    continue;
                                }
                                $keyfieldsarr = $reftablekey->getFields();
                                                                $diferences = array_merge(array_diff($reffieldsarr, $keyfieldsarr), array_diff($keyfieldsarr, $reffieldsarr));
                                if (empty($diferences)) {
                                    $masterfound = true;
                                    break;
                                }
                            }
                            if (!$masterfound) {
                                $errors[] = $this->str['nomasterprimaryuniquefound'];
                            } else {
                                                               if (implode(',', $reffieldsarr) != implode(',', $keyfieldsarr)) {
                                   $errors[] = $this->str['masterprimaryuniqueordernomatch'];
                               }
                            }
                        }
                    }
                }
            }
        }


        if (!empty($errors)) {
            $tempkey = new xmldb_key($name);
            $tempkey->setType($type);
            $tempkey->setFields($fieldsarr);
            if ($type == XMLDB_KEY_FOREIGN ||
                $type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $tempkey->setRefTable($reftable);
                $tempkey->setRefFields($reffieldsarr);
            }
                        $o = '<p>' .implode(', ', $errors) . '</p>
                  <p>' . $name . ': ' . $tempkey->readableInfo() . '</p>';
            $o.= '<a href="index.php?action=edit_key&amp;key=' .$key->getName() . '&amp;table=' . $table->getName() .
                 '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
            $this->output = $o;
        }

                if (empty($errors)) {
                                    if ($keyparam != $name) {
                $key->setName($name);
                if ($key->getPrevious()) {
                    $prev = $table->getKey($key->getPrevious());
                    $prev->setNext($name);
                    $prev->setChanged(true);
                }
                if ($key->getNext()) {
                    $next = $table->getKey($key->getNext());
                    $next->setPrevious($name);
                    $next->setChanged(true);
                }
            }

                        $key->setComment($comment);

                        $key->setType($type);
            $key->setFields($fieldsarr);
            if ($type == XMLDB_KEY_FOREIGN ||
                $type == XMLDB_KEY_FOREIGN_UNIQUE) {
                $key->setRefTable($reftable);
                $key->setRefFields($reffieldsarr);
            }

                                    $key->calculateHash(true);
            if ($oldhash != $key->getHash()) {
                $key->setChanged(true);
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

