<?php




class view_table_php extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'selectaction' => 'tool_xmldb',
            'selectfieldkeyindex' => 'tool_xmldb',
            'view' => 'tool_xmldb',
            'table' => 'tool_xmldb',
            'selectonecommand' => 'tool_xmldb',
            'selectonefieldkeyindex' => 'tool_xmldb',
            'mustselectonefield' => 'tool_xmldb',
            'mustselectonekey' => 'tool_xmldb',
            'mustselectoneindex' => 'tool_xmldb',
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

        $tableparam = required_param('table', PARAM_PATH);

        $table = $structure->getTable($tableparam);
        $fields = $table->getFields();
        $field = reset($fields);
        $defaultfieldkeyindex = null;
        if ($field) {
            $defaultfieldkeyindex = 'f#' . $field->getName();
        }
        $keys = $table->getKeys();
        $indexes = $table->getIndexes();

                $commandparam = optional_param('command', 'add_field', PARAM_PATH);
        $origfieldkeyindexparam = optional_param('fieldkeyindex', $defaultfieldkeyindex, PARAM_PATH);
        $fieldkeyindexparam = preg_replace('/[fki]#/i', '', $origfieldkeyindexparam);         $fieldkeyindexinitial = substr($origfieldkeyindexparam, 0, 1); 
                $b = ' <p class="centerpara buttons">';
        $b .= '<a href="index.php?action=edit_table&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;table=' . $tableparam . '">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o = $b;

                $o .= '<h3 class="main">' . $this->str['table'] . ': ' . s($tableparam) . '</h3>';

                $optionspacer = '&nbsp;&nbsp;&nbsp;';

                $commands = array('Fields',
                         $optionspacer . 'add_field',
                         $optionspacer . 'drop_field',
                         $optionspacer . 'rename_field',
                         $optionspacer . 'change_field_type',
                         $optionspacer . 'change_field_precision',
                         $optionspacer . 'change_field_notnull',
                         $optionspacer . 'change_field_default',
                         'Keys',
                         $optionspacer . 'add_key',
                         $optionspacer . 'drop_key',
                         $optionspacer . 'rename_key',
                         'Indexes',
                         $optionspacer . 'add_index',
                         $optionspacer . 'drop_index',
                         $optionspacer . 'rename_index');
        foreach ($commands as $command) {
            $popcommands[str_replace($optionspacer, '', $command)] = str_replace('_', ' ', $command);
        }
                if ($fields) {
            $popfields['fieldshead'] = 'Fields';
            foreach ($fields as $field) {
                $popfields['f#' . $field->getName()] = $optionspacer . $field->getName();
            }
        }
        if ($keys) {
            $popfields['keyshead'] = 'Keys';
            foreach ($keys as $key) {
                $popfields['k#' . $key->getName()] = $optionspacer . $key->getName();
            }
        }
        if ($indexes) {
            $popfields['indexeshead'] = 'Indexes';
            foreach ($indexes as $index) {
                $popfields['i#' . $index->getName()] = $optionspacer . $index->getName();
            }
        }

                $o.= '<form id="form" action="index.php" method="post">';
        $o.= '<div>';
        $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
        $o.= '    <input type="hidden" name ="table" value="' . s($tableparam) . '" />';
        $o.= '    <input type="hidden" name ="action" value="view_table_php" />';
        $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td><label for="menucommand" accesskey="c">' . $this->str['selectaction'] .' </label>' . html_writer::select($popcommands, 'command', $commandparam, false) . '&nbsp;<label for="menufieldkeyindex" accesskey="f">' . $this->str['selectfieldkeyindex'] . ' </label>' .html_writer::select($popfields, 'fieldkeyindex', $origfieldkeyindexparam, false) . '</td></tr>';
        $o.= '      <tr><td colspan="2" align="center"><input type="submit" value="' .$this->str['view'] . '" /></td></tr>';
        $o.= '    </table>';
        $o.= '</div></form>';

        $o.= '    <table id="phpcode" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td><textarea cols="80" rows="32">';
                if ($fieldkeyindexparam == 'fieldshead' || $fieldkeyindexparam == 'keyshead' || $fieldkeyindexparam == 'indexeshead') {
            $o.= s($this->str['selectonefieldkeyindex']);
                } else if ($commandparam == 'Fields' || $commandparam == 'Keys' || $commandparam == 'Indexes') {
            $o.= s($this->str['selectonecommand']);
        } else {
                        switch ($commandparam) {
                case 'add_field':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->add_field_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'drop_field':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->drop_field_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'rename_field':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->rename_field_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'change_field_type':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->change_field_type_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'change_field_precision':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->change_field_precision_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'change_field_notnull':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->change_field_notnull_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'change_field_default':
                    if ($fieldkeyindexinitial == 'f') {                         $o.= s($this->change_field_default_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonefield'];
                    }
                    break;
                case 'add_key':
                    if ($fieldkeyindexinitial == 'k') {                         $o.= s($this->add_key_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonekey'];
                    }
                    break;
                case 'drop_key':
                    if ($fieldkeyindexinitial == 'k') {                         $o.= s($this->drop_key_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonekey'];
                    }
                    break;
                case 'rename_key':
                    if ($fieldkeyindexinitial == 'k') {                         $o.= s($this->rename_key_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectonekey'];
                    }
                    break;
                case 'add_index':
                    if ($fieldkeyindexinitial == 'i') {                         $o.= s($this->add_index_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectoneindex'];
                    }
                    break;
                case 'drop_index':
                    if ($fieldkeyindexinitial == 'i') {                         $o.= s($this->drop_index_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectoneindex'];
                    }
                    break;
                case 'rename_index':
                    if ($fieldkeyindexinitial == 'i') {                         $o.= s($this->rename_index_php($structure, $tableparam, $fieldkeyindexparam));
                    } else {
                        $o.= $this->str['mustselectoneindex'];
                    }
                    break;
            }
        }
        $o.= '</textarea></td></tr>';
        $o.= '    </table>';

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }

    
    function add_field_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define field ' . $field->getName() . ' to be added to ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch add field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if (!$dbman->field_exists($table, $field)) {'. XMLDB_LINEFEED;
        $result .= '            $dbman->add_field($table, $field);' . XMLDB_LINEFEED;
        $result .= '        }'. XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function drop_field_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define field ' . $field->getName() . ' to be dropped from ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "'" . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch drop field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if ($dbman->field_exists($table, $field)) {' . XMLDB_LINEFEED;
        $result .= '            $dbman->drop_field($table, $field);' . XMLDB_LINEFEED;
        $result .= '        }' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function rename_field_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Rename field ' . $field->getName() . ' on table ' . $table->getName() . ' to NEWNAMEGOESHERE.'. XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch rename field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->rename_field($table, $field, ' . "'" . 'NEWNAMEGOESHERE' . "'" . ');' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function change_field_type_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $type = $field->getXMLDBTypeName($field->getType());

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Changing type of field ' . $field->getName() . ' on table ' . $table->getName() . ' to ' . $type . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch change of type for field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->change_field_type($table, $field);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function change_field_precision_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $precision = '(' . $field->getLength();
        if ($field->getDecimals()) {
            $precision .= ', ' . $field->getDecimals();
        }
        $precision .= ')';

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Changing precision of field ' . $field->getName() . ' on table ' . $table->getName() . ' to ' . $precision . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch change of precision for field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->change_field_precision($table, $field);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function change_field_notnull_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $notnull = $field->getNotnull() ? 'not null' : 'null';

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Changing nullability of field ' . $field->getName() . ' on table ' . $table->getName() . ' to ' . $notnull . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch change of nullability for field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->change_field_notnull($table, $field);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function change_field_default_php($structure, $table, $field) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$field = $table->getField($field)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $default = $field->getDefault() === null ? 'drop it' : $field->getDefault();

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Changing the default of field ' . $field->getName() . ' on table ' . $table->getName() . ' to ' . $default . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $field = new xmldb_field(' . "'" . $field->getName() . "', " . $field->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch change of default for field ' . $field->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->change_field_default($table, $field);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function add_key_php($structure, $table, $key) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$key = $table->getKey($key)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define key ' . $key->getName() . ' ('. $key->getXMLDBKeyName($key->getType()) . ') to be added to ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $key = new xmldb_key(' . "'" . $key->getName() . "', " . $key->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch add key ' . $key->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->add_key($table, $key);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function drop_key_php($structure, $table, $key) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$key = $table->getKey($key)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define key ' . $key->getName() . ' ('. $key->getXMLDBKeyName($key->getType()) . ') to be dropped form ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $key = new xmldb_key(' . "'" . $key->getName() . "', " . $key->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch drop key ' . $key->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->drop_key($table, $key);' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function rename_key_php($structure, $table, $key) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$key = $table->getKey($key)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= 'DON\'T USE THIS FUNCTION (IT\'S ONLY EXPERIMENTAL). SOME DBs DON\'T SUPPORT IT!' . XMLDB_LINEFEED . XMLDB_LINEFEED;

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define key ' . $key->getName() . ' ('. $key->getXMLDBKeyName($key->getType()) . ') to be renamed to NEWNAMEGOESHERE.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $key = new xmldb_key(' . "'" . $key->getName() . "', " . $key->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch rename key ' . $key->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->rename_key($table, $key, ' . "'" . 'NEWNAMEGOESHERE' . "'" . ');' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function add_index_php($structure, $table, $index) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$index = $table->getIndex($index)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define index ' . $index->getName() . ' ('. ($index->getUnique() ? 'unique' : 'not unique') . ') to be added to ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $index = new xmldb_index(' . "'" . $index->getName() . "', " . $index->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch add index ' . $index->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if (!$dbman->index_exists($table, $index)) {' . XMLDB_LINEFEED;
        $result .= '            $dbman->add_index($table, $index);' . XMLDB_LINEFEED;
        $result .= '        }' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function drop_index_php($structure, $table, $index) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$index = $table->getIndex($index)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define index ' . $index->getName() . ' ('. ($index->getUnique() ? 'unique' : 'not unique') . ') to be dropped form ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $index = new xmldb_index(' . "'" . $index->getName() . "', " . $index->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch drop index ' . $index->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if ($dbman->index_exists($table, $index)) {' . XMLDB_LINEFEED;
        $result .= '            $dbman->drop_index($table, $index);' . XMLDB_LINEFEED;
        $result .= '        }' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function rename_index_php($structure, $table, $index) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if (!$index = $table->getIndex($index)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= 'DON\'T USE THIS FUNCTION (IT\'S ONLY EXPERIMENTAL). SOME DBs DON\'T SUPPORT IT!' . XMLDB_LINEFEED . XMLDB_LINEFEED;

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define index ' . $index->getName() . ' ('. ($index->getUnique() ? 'unique' : 'not unique') . ') to be renamed to NEWNAMEGOESHERE.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= '        $index = new xmldb_index(' . "'" . $index->getName() . "', " . $index->getPHP(true) . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch rename index ' . $index->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->rename_index($table, $index, ' . "'" . 'NEWNAMEGOESHERE' . "'" . ');' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

}

