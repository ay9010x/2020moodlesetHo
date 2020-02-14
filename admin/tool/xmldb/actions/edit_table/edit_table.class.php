<?php




class edit_table extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'change' => 'tool_xmldb',
            'vieworiginal' => 'tool_xmldb',
            'viewedited' => 'tool_xmldb',
            'viewsqlcode' => 'tool_xmldb',
            'viewphpcode' => 'tool_xmldb',
            'newfield' => 'tool_xmldb',
            'newkey' => 'tool_xmldb',
            'newindex' => 'tool_xmldb',
            'fields' => 'tool_xmldb',
            'keys' => 'tool_xmldb',
            'indexes' => 'tool_xmldb',
            'edit' => 'tool_xmldb',
            'up' => 'tool_xmldb',
            'down' => 'tool_xmldb',
            'delete' => 'tool_xmldb',
            'reserved' => 'tool_xmldb',
            'back' => 'tool_xmldb',
            'viewxml' => 'tool_xmldb',
            'pendingchanges' => 'tool_xmldb',
            'pendingchangescannotbesaved' => 'tool_xmldb',
            'save' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

                        $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

                if (!empty($XMLDB->dbdirs)) {
            $dbdir = $XMLDB->dbdirs[$dirpath];
        } else {
            return false;
        }
                        if (!isset($XMLDB->editeddirs[$dirpath])) {
            $XMLDB->editeddirs[$dirpath] = unserialize(serialize($dbdir));
        }

        if (!empty($XMLDB->editeddirs)) {
            $editeddir = $XMLDB->editeddirs[$dirpath];
            $structure = $editeddir->xml_file->getStructure();
        }

        $tableparam = required_param('table', PARAM_CLEAN);
        if (!$table = $structure->getTable($tableparam)) {
                        $tableparam = required_param('name', PARAM_CLEAN);
            $table = $structure->getTable($tableparam);
        }

        $dbdir = $XMLDB->dbdirs[$dirpath];
        $origstructure = $dbdir->xml_file->getStructure();

                $o = '<form id="form" action="index.php" method="post">';
        $o.= '<div>';
        $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
        $o.= '    <input type="hidden" name ="table" value="' . $tableparam .'" />';
        $o.= '    <input type="hidden" name ="action" value="edit_table_save" />';
        $o.= '    <input type="hidden" name ="sesskey" value="' . sesskey() .'" />';
        $o.= '    <input type="hidden" name ="postaction" value="edit_table" />';
        $o.= '    <table id="formelements" class="boxaligncenter">';
                if ($structure->getTableUses($table->getName())) {
            $o.= '      <tr valign="top"><td>Name:</td><td><input type="hidden" name ="name" value="' . s($table->getName()) . '" />' . s($table->getName()) .'</td></tr>';
        } else {
            $o.= '      <tr valign="top"><td><label for="name" accesskey="p">Name:</label></td><td><input name="name" type="text" size="28" maxlength="28" id="name" value="' . s($table->getName()) . '" /></td></tr>';
        }
        $o.= '      <tr valign="top"><td><label for="comment" accesskey="c">Comment:</label></td><td><textarea name="comment" rows="3" cols="80" id="comment">' . s($table->getComment()) . '</textarea></td></tr>';
        $o.= '      <tr valign="top"><td>&nbsp;</td><td><input type="submit" value="' .$this->str['change'] . '" /></td></tr>';
        $o.= '    </table>';
        $o.= '</div></form>';
                $e = '';
        $cansavenow = false;
        if ($structure->hasChanged()) {
            if (!is_writeable($dirpath . '/install.xml') || !is_writeable($dirpath)) {
                $e .= '<p class="centerpara error">' . $this->str['pendingchangescannotbesaved'] . '</p>';
            } else {
                $e .= '<p class="centerpara warning">' . $this->str['pendingchanges'] . '</p>';
                $cansavenow = true;
            }
        }
                $b = ' <p class="centerpara buttons">';
                if ($origstructure->getTable($tableparam)) {
            $b .= '&nbsp;<a href="index.php?action=view_table_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=original&amp;table=' . $tableparam . '">[' . $this->str['vieworiginal'] . ']</a>';
        } else {
            $b .= '&nbsp;[' . $this->str['vieworiginal'] . ']';
        }
                if ($table->hasChanged()) {
            $b .= '&nbsp;<a href="index.php?action=view_table_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=edited&amp;table=' . $tableparam . '">[' . $this->str['viewedited'] . ']</a>';
        } else {
            $b .= '&nbsp;[' . $this->str['viewedited'] . ']';
        }
                $b .= '&nbsp;<a href="index.php?action=new_field&amp;sesskey=' . sesskey() . '&amp;postaction=edit_field&amp;table=' . $tableparam . '&amp;field=changeme&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['newfield'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=new_key&amp;sesskey=' . sesskey() . '&amp;postaction=edit_key&amp;table=' . $tableparam . '&amp;key=changeme&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['newkey'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=new_index&amp;sesskey=' . sesskey() . '&amp;postaction=edit_index&amp;table=' . $tableparam . '&amp;index=changeme&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['newindex'] . ']</a>';
        $b .= '</p>';

        $b .= ' <p class="centerpara buttons">';
                $b .= '<a href="index.php?action=view_table_sql&amp;table=' . $tableparam . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' .$this->str['viewsqlcode'] . ']</a>';
                $b .= '&nbsp;<a href="index.php?action=view_table_php&amp;table=' . $tableparam . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['viewphpcode'] . ']</a>';
                if ($cansavenow) {
            $b .= '&nbsp;<a href="index.php?action=save_xml_file&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;time=' . time() . '&amp;unload=false&amp;postaction=edit_table&amp;table=' . $tableparam . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['save'] . ']</a>';
        }
                $b .= '&nbsp;<a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o .= $e . $b;

        require_once("$CFG->libdir/ddl/sql_generator.php");
        $reserved_words = sql_generator::getAllReservedWords();

                $table->deleteField('changeme');
        $table->deleteKey('changeme');
        $table->deleteIndex('changeme');

                $fields = $table->getFields();
        if (!empty($fields)) {
            $o .= '<h3 class="main">' . $this->str['fields'] . '</h3>';
            $o .= '<table id="listfields" border="0" cellpadding="5" cellspacing="1" class="boxaligncenter flexible">';
            $row = 0;
            foreach ($fields as $field) {
                                if (!$structure->getFieldUses($table->getName(), $field->getName())) {
                    $f = '<a href="index.php?action=edit_field&amp;field=' .$field->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">' . $field->getName() . '</a>';
                } else {
                    $f = $field->getName();
                }
                                $b = '</td><td class="button cell">';
                                if (!$structure->getFieldUses($table->getName(), $field->getName())) {
                    $b .= '<a href="index.php?action=edit_field&amp;field=' .$field->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['edit'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['edit'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($field->getPrevious()) {
                    $b .= '<a href="index.php?action=move_updown_field&amp;direction=up&amp;sesskey=' . sesskey() . '&amp;field=' . $field->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['up'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['up'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($field->getNext()) {
                    $b .= '<a href="index.php?action=move_updown_field&amp;direction=down&amp;sesskey=' . sesskey() . '&amp;field=' . $field->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['down'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['down'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if (count($fields) > 1 &&
                !$structure->getFieldUses($table->getName(), $field->getName())) {
                    $b .= '<a href="index.php?action=delete_field&amp;sesskey=' . sesskey() . '&amp;field=' . $field->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['delete'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['delete'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                $b .= '<a href="index.php?action=view_field_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;field=' . $field->getName() . '&amp;table=' . $table->getName() . '&amp;select=edited">[' . $this->str['viewxml'] . ']</a>';
                                if (array_key_exists($field->getName(), $reserved_words)) {
                    $b .= '&nbsp;<a href="index.php?action=view_reserved_words"><span class="error">' . $this->str['reserved'] . '</span></a>';
                }
                                $r = '</td><td class="readableinfo cell">' . $field->readableInfo() . '</td>';
                                $o .= '<tr class="r' . $row . '"><td class="table cell">' . $f . $b . $r . '</tr>';
                $row = ($row + 1) % 2;
            }
            $o .= '</table>';
        }
                $keys = $table->getKeys();
        if (!empty($keys)) {
            $o .= '<h3 class="main">' . $this->str['keys'] . '</h3>';
            $o .= '<table id="listkeys" border="0"  cellpadding="5" cellspacing="1" class="boxaligncenter flexible">';
            $row = 0;
            foreach ($keys as $key) {
                                if (!$structure->getKeyUses($table->getName(), $key->getName())) {
                    $k = '<a href="index.php?action=edit_key&amp;key=' .$key->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">' . $key->getName() . '</a>';
                } else {
                    $k = $key->getName();
                }
                                $b = '</td><td class="button cell">';
                                if (!$structure->getKeyUses($table->getName(), $key->getName())) {
                    $b .= '<a href="index.php?action=edit_key&amp;key=' .$key->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['edit'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['edit'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($key->getPrevious()) {
                    $b .= '<a href="index.php?action=move_updown_key&amp;direction=up&amp;sesskey=' . sesskey() . '&amp;key=' . $key->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['up'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['up'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($key->getNext()) {
                    $b .= '<a href="index.php?action=move_updown_key&amp;direction=down&amp;sesskey=' . sesskey() . '&amp;key=' . $key->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['down'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['down'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if (!$structure->getKeyUses($table->getName(), $key->getName())) {
                    $b .= '<a href="index.php?action=delete_key&amp;sesskey=' . sesskey() . '&amp;key=' . $key->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['delete'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['delete'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                $b .= '<a href="index.php?action=view_key_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;key=' . $key->getName() . '&amp;table=' . $table->getName() . '&amp;select=edited">[' . $this->str['viewxml'] . ']</a>';
                                $r = '</td><td class="readableinfo cell">' . $key->readableInfo() . '</td>';
                            $o .= '<tr class="r' . $row . '"><td class="table cell">' . $k . $b . $r .'</tr>';
                $row = ($row + 1) % 2;
            }
            $o .= '</table>';
        }
               $indexes = $table->getIndexes();
        if (!empty($indexes)) {
            $o .= '<h3 class="main">' . $this->str['indexes'] . '</h3>';
            $o .= '<table id="listindexes" border="0" cellpadding="5" cellspacing="1" class="boxaligncenter flexible">';
            $row = 0;
            foreach ($indexes as $index) {
                                $i = '<a href="index.php?action=edit_index&amp;index=' .$index->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">' . $index->getName() . '</a>';
                                $b = '</td><td class="button cell">';
                                $b .= '<a href="index.php?action=edit_index&amp;index=' .$index->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['edit'] . ']</a>';
                $b .= '</td><td class="button cell">';
                                if ($index->getPrevious()) {
                    $b .= '<a href="index.php?action=move_updown_index&amp;direction=up&amp;sesskey=' . sesskey() . '&amp;index=' . $index->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['up'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['up'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                if ($index->getNext()) {
                    $b .= '<a href="index.php?action=move_updown_index&amp;direction=down&amp;sesskey=' . sesskey() . '&amp;index=' . $index->getName() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_table' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['down'] . ']</a>';
                } else {
                    $b .= '[' . $this->str['down'] . ']';
                }
                $b .= '</td><td class="button cell">';
                                    $b .= '<a href="index.php?action=delete_index&amp;sesskey=' . sesskey() . '&amp;index=' . $index->getName() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['delete'] . ']</a>';
                $b .= '</td><td class="button cell">';
                                $b .= '<a href="index.php?action=view_index_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;index=' . $index->getName() . '&amp;table=' . $table->getName() . '&amp;select=edited">[' . $this->str['viewxml'] . ']</a>';
                                $r = '</td><td class="readableinfo cell">' . $index->readableInfo() . '</td>';
                            $o .= '<tr class="r' . $row . '"><td class="table cell">' . $i . $b . $r .'</tr>';
                $row = ($row + 1) % 2;
            }
            $o .= '</table>';
        }

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

