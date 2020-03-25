<?php




class edit_field extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'change' => 'tool_xmldb',
            'float2numbernote' => 'tool_xmldb',
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
        $fieldparam = required_param('field', PARAM_CLEAN);
        if (!$field = $table->getField($fieldparam)) {
                        $fieldparam = required_param('name', PARAM_CLEAN);
            $field = $table->getField($fieldparam);
        }

        $dbdir = $XMLDB->dbdirs[$dirpath];
        $origstructure = $dbdir->xml_file->getStructure();

        $o = ''; 
                if ($field->getType() == XMLDB_TYPE_FLOAT) {
            $o .= '<p>' . $this->str['float2numbernote'] . '</p>';
        }

                $o.= '<form id="form" action="index.php" method="post">';
        $o.= '    <div>';
        $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
        $o.= '    <input type="hidden" name ="table" value="' . $tableparam .'" />';
        $o.= '    <input type="hidden" name ="field" value="' . $fieldparam .'" />';
        $o.= '    <input type="hidden" name ="sesskey" value="' . sesskey() .'" />';
        $o.= '    <input type="hidden" name ="action" value="edit_field_save" />';
        $o.= '    <input type="hidden" name ="postaction" value="edit_table" />';
        $o.= '    <table id="formelements" class="boxaligncenter">';
                        $disabled = '';
        if ($structure->getFieldUses($table->getName(), $field->getName())) {
            $o.= '      <input type="hidden" name ="name" value="' .  s($field->getName()) .'" />';
            $o.= '      <tr valign="top"><td>Name:</td><td colspan="2">' . s($field->getName()) . '</td></tr>';
        } else {
            $o.= '      <tr valign="top"><td><label for="name" accesskey="n">Name:</label></td><td colspan="2"><input name="name" type="text" size="30" maxlength="30" id="name" value="' . s($field->getName()) . '" /></td></tr>';
        }
                $o.= '      <tr valign="top"><td><label for="comment" accesskey="c">Comment:</label></td><td colspan="2"><textarea name="comment" rows="3" cols="80" id="comment">' . s($field->getComment()) . '</textarea></td></tr>';
                $typeoptions = array (XMLDB_TYPE_INTEGER => $field->getXMLDBTypeName(XMLDB_TYPE_INTEGER),
                              XMLDB_TYPE_NUMBER  => $field->getXMLDBTypeName(XMLDB_TYPE_NUMBER),
                              XMLDB_TYPE_FLOAT   => $field->getXMLDBTypeName(XMLDB_TYPE_FLOAT),
                              XMLDB_TYPE_DATETIME=> $field->getXMLDBTypeName(XMLDB_TYPE_DATETIME),
                              XMLDB_TYPE_CHAR    => $field->getXMLDBTypeName(XMLDB_TYPE_CHAR),
                              XMLDB_TYPE_TEXT    => $field->getXMLDBTypeName(XMLDB_TYPE_TEXT),
                              XMLDB_TYPE_BINARY  => $field->getXMLDBTypeName(XMLDB_TYPE_BINARY));
                                if ($field->getType() != XMLDB_TYPE_FLOAT) {
            unset ($typeoptions[XMLDB_TYPE_FLOAT]);
        }
                if ($field->getType() != XMLDB_TYPE_DATETIME) {
            unset ($typeoptions[XMLDB_TYPE_DATETIME]);
        }
        $select = html_writer::select($typeoptions, 'type', $field->getType(), false);
        $o.= '      <tr valign="top"><td><label for="menutype" accesskey="t">Type:</label></td>';
        $o.= '        <td colspan="2">' . $select . '</td></tr>';
                $o.= '      <tr valign="top"><td><label for="length" accesskey="l">Length:</label></td>';
        $o.= '        <td colspan="2"><input name="length" type="text" size="6" maxlength="6" id="length" value="' . s($field->getLength()) . '" /><span id="lengthtip"></span></td></tr>';
                $o.= '      <tr valign="top"><td><label for="decimals" accesskey="d">Decimals:</label></td>';
        $o.= '        <td colspan="2"><input name="decimals" type="text" size="6" maxlength="6" id="decimals" value="' . s($field->getDecimals()) . '" /><span id="decimalstip"></span></td></tr>';
                $notnulloptions = array (0 => 'null', 'not null');
        $select = html_writer::select($notnulloptions, 'notnull', $field->getNotNull(), false);
        $o.= '      <tr valign="top"><td><label for="menunotnull" accesskey="n">Not Null:</label></td>';
        $o.= '        <td colspan="2">' . $select . '</td></tr>';
                $sequenceoptions = array (0 => $this->str['no'], 1 => 'auto-numbered');
        $select = html_writer::select($sequenceoptions, 'sequence', $field->getSequence(), false);
        $o.= '      <tr valign="top"><td><label for="menusequence" accesskey="s">Sequence:</label></td>';
        $o.= '        <td colspan="2">' . $select . '</td></tr>';
                $o.= '      <tr valign="top"><td><label for="default" accesskey="d">Default:</label></td>';
        $o.= '        <td colspan="2"><input type="text" name="default" size="30" maxlength="80" id="default" value="' . s($field->getDefault()) . '" /></td></tr>';
                $o.= '      <tr valign="top"><td>&nbsp;</td><td colspan="2"><input type="submit" value="' .$this->str['change'] . '" /></td></tr>';
        $o.= '    </table>';
        $o.= '</div></form>';
                $b = ' <p class="centerpara buttons">';
                if ($table->getField($fieldparam)) {
            $b .= '&nbsp;<a href="index.php?action=view_field_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=original&amp;table=' . $tableparam . '&amp;field=' . $fieldparam . '">[' . $this->str['vieworiginal'] . ']</a>';
        } else {
            $b .= '&nbsp;[' . $this->str['vieworiginal'] . ']';
        }
                if ($field->hasChanged()) {
            $b .= '&nbsp;<a href="index.php?action=view_field_xml&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;select=edited&amp;table=' . $tableparam . '&amp;field=' . $fieldparam . '">[' . $this->str['viewedited'] . ']</a>';
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

