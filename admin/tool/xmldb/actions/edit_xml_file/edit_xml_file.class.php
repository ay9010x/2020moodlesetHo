<?php




class edit_xml_file extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'change' => 'tool_xmldb',
            'edit' => 'tool_xmldb',
            'up' => 'tool_xmldb',
            'down' => 'tool_xmldb',
            'delete' => 'tool_xmldb',
            'vieworiginal' => 'tool_xmldb',
            'viewedited' => 'tool_xmldb',
            'tables' => 'tool_xmldb',
            'newtable' => 'tool_xmldb',
            'newtablefrommysql' => 'tool_xmldb',
            'viewsqlcode' => 'tool_xmldb',
            'viewphpcode' => 'tool_xmldb',
            'reserved' => 'tool_xmldb',
            'backtomainview' => 'tool_xmldb',
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

                global $CFG, $XMLDB, $DB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;

                if (!empty($XMLDB->dbdirs)) {
            $dbdir = $XMLDB->dbdirs[$dirpath];
            if ($dbdir) {
                                if (!$dbdir->path_exists || !$dbdir->xml_loaded) {
                    return false;
                }
                                if (empty($XMLDB->editeddirs)) {
                    $XMLDB->editeddirs = array();
                }
                                if (!isset($XMLDB->editeddirs[$dirpath])) {
                    $XMLDB->editeddirs[$dirpath] = unserialize(serialize($dbdir));
                }
                                $editeddir = $XMLDB->editeddirs[$dirpath];
                $structure = $editeddir->xml_file->getStructure();

                                $o = '<form id="form" action="index.php" method="post">';
                $o.= '<div>';
                $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
                $o.= '    <input type="hidden" name ="action" value="edit_xml_file_save" />';
                $o.= '    <input type="hidden" name ="postaction" value="edit_xml_file" />';
                $o.= '    <input type="hidden" name ="path" value="' . s($structure->getPath()) .'" />';
                $o.= '    <input type="hidden" name ="version" value="' . s($structure->getVersion()) .'" />';
                $o.= '    <input type="hidden" name ="sesskey" value="' . sesskey() .'" />';
                $o.= '    <table id="formelements" class="boxaligncenter">';
                $o.= '      <tr valign="top"><td>Path:</td><td>' . s($structure->getPath()) . '</td></tr>';
                $o.= '      <tr valign="top"><td>Version:</td><td>' . s($structure->getVersion()) . '</td></tr>';
                $o.= '      <tr valign="top"><td><label for="comment" accesskey="c">Comment:</label></td><td><textarea name="comment" rows="3" cols="80" id="comment">' . $structure->getComment() . '</textarea></td></tr>';
                $o.= '      <tr><td>&nbsp;</td><td><input type="submit" value="' .$this->str['change'] . '" /></td></tr>';
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
                                $b .= '&nbsp;<a href="index.php?action=view_structure_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=original">[' . $this->str['vieworiginal'] . ']</a>';
                                if ($structure->hasChanged()) {
                    $b .= '&nbsp;<a href="index.php?action=view_structure_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=edited">[' . $this->str['viewedited'] . ']</a>';
                } else {
                    $b .= '&nbsp;[' . $this->str['viewedited'] . ']';
                }
                                $b .= '&nbsp;<a href="index.php?action=new_table&amp;sesskey=' . sesskey() . '&amp;postaction=edit_table&amp;table=changeme&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['newtable'] . ']</a>';
                                if ($DB->get_dbfamily() == 'mysql') {
                    $b .= '&nbsp;<a href="index.php?action=new_table_from_mysql&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['newtablefrommysql'] . ']</a>';
                } else {
                    $b .= '&nbsp;[' . $this->str['newtablefrommysql'] . ']';
                }

                                $b .= '<a href="index.php?action=view_structure_sql&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' .$this->str['viewsqlcode'] . ']</a>';
                                $b .= '&nbsp;<a href="index.php?action=view_structure_php&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['viewphpcode'] . ']</a>';
                                if ($cansavenow) {
                    $b .= '&nbsp;<a href="index.php?action=save_xml_file&amp;sesskey=' . sesskey() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;time=' . time() . '&amp;unload=false&amp;postaction=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['save'] . ']</a>';
                }

                                $b .= '&nbsp;<a href="index.php?action=main_view#lastused">[' . $this->str['backtomainview'] . ']</a>';
                $b .= '</p>';
                $o .= $e . $b;

                                                require_once("$CFG->libdir/ddl/sql_generator.php");
                $reserved_words = sql_generator::getAllReservedWords();

                                $tables = $structure->getTables();
                if ($tables) {
                    $o .= '<h3 class="main">' . $this->str['tables'] . '</h3>';
                    $o .= '<table id="listtables" border="0" cellpadding="5" cellspacing="1" class="boxaligncenter flexible">';
                    $row = 0;
                    foreach ($tables as $table) {
                                                $t = '<a href="index.php?action=edit_table&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">' . $table->getName() . '</a>';
                                                $b = '</td><td class="button cell">';
                                                $b .= '<a href="index.php?action=edit_table&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['edit'] . ']</a>';
                        $b .= '</td><td class="button cell">';
                                                if ($table->getPrevious()) {
                            $b .= '<a href="index.php?action=move_updown_table&amp;direction=up&amp;sesskey=' . sesskey() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_xml_file' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['up'] . ']</a>';
                        } else {
                            $b .= '[' . $this->str['up'] . ']';
                        }
                        $b .= '</td><td class="button cell">';
                                                if ($table->getNext()) {
                            $b .= '<a href="index.php?action=move_updown_table&amp;direction=down&amp;sesskey=' . sesskey() . '&amp;table=' . $table->getName() . '&amp;postaction=edit_xml_file' . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['down'] . ']</a>';
                        } else {
                            $b .= '[' . $this->str['down'] . ']';
                        }
                        $b .= '</td><td class="button cell">';
                                                if (count($tables) > 1 &&
                            !$structure->getTableUses($table->getName())) {
                                                            $b .= '<a href="index.php?action=delete_table&amp;sesskey=' . sesskey() . '&amp;table=' . $table->getName() . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['delete'] . ']</a>';
                        } else {
                            $b .= '[' . $this->str['delete'] . ']';
                        }
                        $b .= '</td><td class="button cell">';
                                                $b .= '<a href="index.php?action=view_table_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;table=' . $table->getName() . '&amp;select=edited">[' . $this->str['viewxml'] . ']</a>';
                                                 if (array_key_exists($table->getName(), $reserved_words)) {
                             $b .= '&nbsp;<a href="index.php?action=view_reserved_words"><span class="error">' . $this->str['reserved'] . '</span></a>';
                         }
                        $b .= '</td>';
                                                $o .= '<tr class="r' . $row . '"><td class="table cell">' . $t . $b . '</tr>';
                        $row = ($row + 1) % 2;
                    }
                    $o .= '</table>';
                }
                        $this->output = $o;
            }
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

        return $result;
    }
}

