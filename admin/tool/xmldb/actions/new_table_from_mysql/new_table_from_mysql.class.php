<?php




class new_table_from_mysql extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'createtable' => 'tool_xmldb',
            'aftertable' => 'tool_xmldb',
            'create' => 'tool_xmldb',
            'back' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $DB, $OUTPUT;

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

        $tableparam = optional_param('table', NULL, PARAM_CLEAN);

                if (!$tableparam) {
                        $this->postaction = NULL;
                        $dbtables = $DB->get_tables();
            $selecttables = array();
            foreach ($dbtables as $dbtable) {
                $i = $structure->findTableInArray($dbtable);
                if ($i === NULL) {
                    $selecttables[$dbtable] = $dbtable;
                }
            }
                        $aftertables = array();
            if ($tables = $structure->getTables()) {
                foreach ($tables as $aftertable) {
                    $aftertables[$aftertable->getName()] = $aftertable->getName();
                }
            }
            if (!$selecttables) {
                $this->errormsg = 'No tables available to be retrofitted';
                return false;
            }
                        $o = '<form id="form" action="index.php" method="post">';
            $o .= '<div>';
            $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
            $o.= '    <input type="hidden" name ="action" value="new_table_from_mysql" />';
            $o.= '    <input type="hidden" name ="postaction" value="edit_table" />';
            $o.= '    <input type="hidden" name ="sesskey" value="' . sesskey() . '" />';
            $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
            $o.= '      <tr><td><label for="menutable" accesskey="t">' . $this->str['createtable'] .' </label>' . html_writer::select($selecttables, 'table') . '<label for="menuafter" accesskey="a">' . $this->str['aftertable'] . ' </label>' .html_writer::select($aftertables, 'after') . '</td></tr>';
            $o.= '      <tr><td colspan="2" align="center"><input type="submit" value="' .$this->str['create'] . '" /></td></tr>';
            $o.= '      <tr><td colspan="2" align="center"><a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a></td></tr>';
            $o.= '    </table>';
            $o.= '</div></form>';

            $this->output = $o;


                        } else {
                        $tableparam = required_param('table', PARAM_CLEAN);
            $afterparam = required_param('after', PARAM_CLEAN);

                        $table = new xmldb_table(strtolower(trim($tableparam)));
            $table->setComment($table->getName() . ' table retrofitted from MySQL');
                        $dbfields = $DB->get_columns($tableparam);
            if ($dbfields) {
                foreach ($dbfields as $dbfield) {
                                        $field = new xmldb_field($dbfield->name);
                                        $field->setFromADOField($dbfield);
                                        $table->addField($field);
                }
            }
                        $dbindexes = $DB->get_indexes($tableparam);
            if ($dbindexes) {
                $lastkey = NULL;                 foreach ($dbindexes as $indexname => $dbindex) {
                                        $dbindex['name'] = $indexname;
                                        if ($dbindex['unique']) {
                        $key = new xmldb_key(strtolower($dbindex['name']));
                                                $key->setFromADOKey($dbindex);
                                                if ($key->getType() == XMLDB_KEY_PRIMARY) {
                        }
                                                $table->addKey($key);

                                        } else {
                        $index = new xmldb_index(strtolower($dbindex['name']));
                                                $index->setFromADOIndex($dbindex);
                                                $table->addIndex($index);
                    }
                }
            }
                                    $structure->addTable($table, $afterparam);
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

