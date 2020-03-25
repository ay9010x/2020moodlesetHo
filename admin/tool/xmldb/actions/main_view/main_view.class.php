<?php




class main_view extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'load' => 'tool_xmldb',
            'create' => 'tool_xmldb',
            'edit' => 'tool_xmldb',
            'save' => 'tool_xmldb',
            'revert' => 'tool_xmldb',
            'unload' => 'tool_xmldb',
            'delete' => 'tool_xmldb',
            'reservedwords' => 'tool_xmldb',
            'gotolastused' => 'tool_xmldb',
            'checkindexes' => 'tool_xmldb',
            'checkdefaults' => 'tool_xmldb',
            'checkforeignkeys' => 'tool_xmldb',
            'checkbigints' => 'tool_xmldb',
            'checkoraclesemantics' => 'tool_xmldb',
            'doc' => 'tool_xmldb',
            'filemodifiedoutfromeditor' => 'tool_xmldb',
            'viewxml' => 'tool_xmldb',
            'pendingchangescannotbesavedreload' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $SESSION, $DB;

                $o = '';
        if (isset($SESSION->lastused)) {
            if ($lastused = $SESSION->lastused) {
                                $o .= '<p class="centerpara"><a href="#lastused">' . $this->str['gotolastused'] . '</a></p>';
            }
        } else {
            $lastused = NULL;
        }

                $b = '<p class="centerpara buttons">';
                $b .= '&nbsp;<a href="index.php?action=view_reserved_words">[' . $this->str['reservedwords'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=generate_all_documentation">[' . $this->str['doc'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=check_indexes&amp;sesskey=' . sesskey() . '">[' . $this->str['checkindexes'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=check_defaults&amp;sesskey=' . sesskey() . '">[' . $this->str['checkdefaults'] . ']</a>';
                if ($DB->get_dbfamily() == 'mysql' || $DB->get_dbfamily() == 'postgres') {
            $b .= '&nbsp;<a href="index.php?action=check_bigints&amp;sesskey=' . sesskey() . '">[' . $this->str['checkbigints'] . ']</a>';
        }
                if ($DB->get_dbfamily() == 'oracle') {
            $b .= '&nbsp;<a href="index.php?action=check_oracle_semantics&amp;sesskey=' . sesskey() . '">[' . $this->str['checkoraclesemantics'] . ']</a>';
        }
        $b .= '&nbsp;<a href="index.php?action=check_foreign_keys&amp;sesskey=' . sesskey() . '">[' . $this->str['checkforeignkeys'] . ']</a>';
        $b .= '</p>';
                $o .= $b;

        
                $result = $this->launch('get_db_directories');
                if ($result && !empty($XMLDB->dbdirs)) {
            $o .= '<table id="listdirectories" border="0" cellpadding="5" cellspacing="1" class="admintable generaltable">';
            $row = 0;
            foreach ($XMLDB->dbdirs as $key => $dbdir) {
                                $hithis = false;
                if (str_replace($CFG->dirroot, '', $key) == $lastused) {
                    $hithis = true;
                }
                $elementtext = str_replace($CFG->dirroot . '/', '', $key);
                                if (!isset($dbdir->has_changed) && isset($dbdir->xml_loaded)) {
                    $dbdir->xml_changed = false;
                    if (isset($XMLDB->editeddirs[$key])) {
                        $editeddir = $XMLDB->editeddirs[$key];
                        if (isset($editeddir->xml_file)) {
                            $structure = $editeddir->xml_file->getStructure();
                            if ($structure->hasChanged()) {
                                $dbdir->xml_changed = true;
                                $editeddir->xml_changed = true;
                            }
                        }
                    }
                }
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    is_readable($key) &&
                    !empty($dbdir->xml_loaded)) {
                    $f = '<a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '">' . $elementtext . '</a>';
                } else {
                    $f = $elementtext;
                }
                                $b = ' <td class="button cell">';
                                if ($dbdir->path_exists &&
                    !file_exists($key . '/install.xml') &&
                    is_writeable($key)) {
                    $b .= '<a href="index.php?action=create_xml_file&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '&amp;time=' . time() . '&amp;postaction=main_view#lastused">[' . $this->str['create'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['create'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    empty($dbdir->xml_loaded)) {
                    $b .= '<a href="index.php?action=load_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '&amp;time=' . time() . '&amp;postaction=main_view#lastused">[' . $this->str['load'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['load'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    is_readable($key) &&
                    !empty($dbdir->xml_loaded)) {
                    $b .= '<a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '">[' . $this->str['edit'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['edit'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_writeable($key . '/install.xml') &&
                    is_writeable($key) &&
                    !empty($dbdir->xml_loaded) &&
                    !empty($dbdir->xml_changed)) {
                    $b .= '<a href="index.php?action=save_xml_file&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '&amp;time=' . time() . '&amp;postaction=main_view#lastused">[' . $this->str['save'] . ']</a>';
                                        if ($dbdir->filemtime != filemtime($key . '/install.xml')) {
                                                $this->errormsg = $this->str['filemodifiedoutfromeditor'];
                    }
                } else {
                    $b .= '[' . $this->str['save'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    is_readable($key)) {
                    $b .= '<a href="index.php?action=generate_documentation&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '">[' . $this->str['doc'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['doc'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml')) {
                    $b .= '<a href="index.php?action=view_xml&amp;file=' . urlencode(str_replace($CFG->dirroot, '', $key) . '/install.xml') . '">[' . $this->str['viewxml'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['viewxml'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    is_writeable($key) &&
                    !empty($dbdir->xml_loaded) &&
                    !empty($dbdir->xml_changed)) {
                    $b .= '<a href="index.php?action=revert_changes&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '">[' . $this->str['revert'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['revert'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    !empty($dbdir->xml_loaded) &&
                    empty($dbdir->xml_changed)) {
                    $b .= '<a href="index.php?action=unload_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '&amp;time=' . time() . '&amp;postaction=main_view#lastused">[' . $this->str['unload'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['unload'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    is_readable($key . '/install.xml') &&
                    is_writeable($key) &&
                    empty($dbdir->xml_loaded)) {
                    $b .= '<a href="index.php?action=delete_xml_file&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $key)) . '">[' . $this->str['delete'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['delete'] . ']';
                }
                $b .= '</td>';
                                if ($hithis) {
                    $o .= '<tr class="highlight"><td class="directory cell"><a name="lastused" />' . $f . '</td>' . $b . '</tr>';
                } else {
                    $o .= '<tr class="r' . $row . '"><td class="directory cell">' . $f . '</td>' . $b . '</tr>';
                }
                $row = ($row + 1) % 2;
                                if (isset($dbdir->xml_file)) {
                    if ($structure = $dbdir->xml_file->getStructure()) {
                        $errors = !empty($this->errormsg) ? array($this->errormsg) : array();
                        $structureerrors = $structure->getAllErrors();
                        if ($structureerrors) {
                            $errors = array_merge($errors, $structureerrors);
                        }
                        if (!empty($errors)) {
                            if ($hithis) {
                                $o .= '<tr class="highlight"><td class="error cell" colspan="10">' . implode (', ', $errors) . '</td></tr>';
                            } else {
                                $o .= '<tr class="r' . $row . '"><td class="error cell" colspan="10">' . implode (', ', $errors) . '</td></tr>';
                            }
                        }
                    }
                }
                                if ($dbdir->path_exists &&
                    file_exists($key . '/install.xml') &&
                    !empty($dbdir->xml_loaded) &&
                    !empty($dbdir->xml_changed) &&
                    (!is_writeable($key . '/install.xml') || !is_writeable($key))) {

                    if ($hithis) {
                        $o .= '<tr class="highlight"><td class="error cell" colspan="10">';
                    } else {
                        $o .= '<tr class="r' . $row . '"><td class="error cell" colspan="10">';
                    }
                    $o .= $this->str['pendingchangescannotbesavedreload'];
                    $o .= '</td></tr>';
                }
            }
            $o .= '</table>';

                        $this->output = $o;
        }

                return $result;
    }
}

