<?php




class edit_index extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'change' => 'tool_xmldb',
            'vieworiginal' => 'tool_xmldb',
            'viewedited' => 'tool_xmldb',
            'yes' => '',
            'no' => '',
            'back' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $OUTPUT;

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

                $tableparam = required_param('table', PARAM_CLEAN);
        if (!$table = $structure->getTable($tableparam)) {
            $this->errormsg = 'Wrong table specified: ' . $tableparam;
            return false;
        }
        $indexparam = required_param('index', PARAM_CLEAN);
        if (!$index = $table->getIndex($indexparam)) {
                        $indexparam = required_param('name', PARAM_CLEAN);
            $index = $table->getIndex($indexparam);
        }

        $dbdir = $XMLDB->dbdirs[$dirpath];
        $origstructure = $dbdir->xml_file->getStructure();

                $o = '<form id="form" action="index.php" method="post">';
        $o.= '<div>';
        $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
        $o.= '    <input type="hidden" name ="table" value="' . $tableparam .'" />';
        $o.= '    <input type="hidden" name ="index" value="' . $indexparam .'" />';
        $o.= '    <input type="hidden" name ="sesskey" value="' . sesskey() .'" />';
        $o.= '    <input type="hidden" name ="action" value="edit_index_save" />';
        $o.= '    <input type="hidden" name ="postaction" value="edit_table" />';
        $o.= '    <table id="formelements" class="boxaligncenter">';
                        $disabled = '';
        if ($structure->getIndexUses($table->getName(), $index->getName())) {
            $disabled = ' disabled="disabled " ';
        }
        $o.= '      <tr valign="top"><td><label for="name" accesskey="n">Name:</label></td><td colspan="2"><input name="name" type="text" size="30" id="name"' . $disabled . ' value="' . s($index->getName()) . '" /></td></tr>';
                $o.= '      <tr valign="top"><td><label for="comment" accesskey="c">Comment:</label></td><td colspan="2"><textarea name="comment" rows="3" cols="80" id="comment">' . s($index->getComment()) . '</textarea></td></tr>';
                $typeoptions = array (0 => 'not unique',
                              1 => 'unique');
        $o.= '      <tr valign="top"><td><label for="menuunique" accesskey="t">Type:</label></td>';
        $select = html_writer::select($typeoptions, 'unique', $index->getUnique(), false);
        $o.= '        <td colspan="2">' . $select . '</td></tr>';
                $o.= '      <tr valign="top"><td><label for="fields" accesskey="f">Fields:</label></td>';
        $o.= '        <td colspan="2"><input name="fields" type="text" size="40" maxlength="80" id="fields" value="' . s(implode(', ', $index->getFields())) . '" /></td></tr>';
                $o.= '      <tr valign="top"><td><label for="hints" accesskey="h">Hints:</label></td>';
        $o.= '        <td colspan="2"><input name="hints" type="text" size="40" maxlength="80" id="hints" value="' . s(implode(', ', $index->getHints())) . '" /></td></tr>';
                $o.= '      <tr valign="top"><td>&nbsp;</td><td colspan="2"><input type="submit" value="' .$this->str['change'] . '" /></td></tr>';
        $o.= '    </table>';
        $o.= '</div></form>';
                $b = ' <p class="centerpara buttons">';
                if ($table->getIndex($indexparam)) {
            $b .= '&nbsp;<a href="index.php?action=view_index_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=original&amp;table=' . $tableparam . '&amp;index=' . $indexparam . '">[' . $this->str['vieworiginal'] . ']</a>';
        } else {
            $b .= '&nbsp;[' . $this->str['vieworiginal'] . ']';
        }
                if ($index->hasChanged()) {
            $b .= '&nbsp;<a href="index.php?action=view_index_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=edited&amp;table=' . $tableparam . '&amp;index=' . $indexparam . '">[' . $this->str['viewedited'] . ']</a>';
        } else {
            $b .= '&nbsp;[' . $this->str['viewedited'] . ']';
        }
                $b .= '&nbsp;<a href="index.php?action=edit_table&amp;table=' . $tableparam . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o .= $b;

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

