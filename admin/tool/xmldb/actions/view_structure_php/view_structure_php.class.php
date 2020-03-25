<?php




class view_structure_php extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'selectaction' => 'tool_xmldb',
            'selecttable' => 'tool_xmldb',
            'view' => 'tool_xmldb',
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

        $tables = $structure->getTables();
        $table = reset($tables);
        $defaulttable = null;
        if ($table) {
            $defaulttable = $table->getName();
        }

                $commandparam = optional_param('command', 'create_table', PARAM_PATH);
        $tableparam = optional_param('table', $defaulttable, PARAM_PATH);

                $b = ' <p class="centerpara buttons">';
        $b .= '<a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o = $b;

                $commands = array('create_table',
                         'drop_table',
                         'rename_table');
        foreach ($commands as $command) {
            $popcommands[$command] = str_replace('_', ' ', $command);
        }
                foreach ($tables as $table) {
            $poptables[$table->getName()] = $table->getName();
        }
                $o.= '<form id="form" action="index.php" method="post">';
        $o.='<div>';
        $o.= '    <input type="hidden" name ="dir" value="' . str_replace($CFG->dirroot, '', $dirpath) . '" />';
        $o.= '    <input type="hidden" name ="action" value="view_structure_php" />';
        $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td><label for="menucommand" accesskey="c">' . $this->str['selectaction'] .' </label>' . html_writer::select($popcommands, 'command', $commandparam, false) . '&nbsp;<label for="menutable" accesskey="t">' . $this->str['selecttable'] . ' </label>' .html_writer::select($poptables, 'table', $tableparam, false) . '</td></tr>';
        $o.= '      <tr><td colspan="2" align="center"><input type="submit" value="' .$this->str['view'] . '" /></td></tr>';
        $o.= '    </table>';
        $o.= '</div></form>';
        $o.= '    <table id="phpcode" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td><textarea cols="80" rows="32">';
                switch ($commandparam) {
            case 'create_table':
                $o.= s($this->create_table_php($structure, $tableparam));
                break;
            case 'drop_table':
                $o.= s($this->drop_table_php($structure, $tableparam));
                break;
            case 'rename_table':
                $o.= s($this->rename_table_php($structure, $tableparam));
                break;
        }
        $o.= '</textarea></td></tr>';
        $o.= '    </table>';

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }

    
    function create_table_php($structure, $table) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define table ' . $table->getName() . ' to be created.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;
        $result .= XMLDB_LINEFEED;
        $result .= '        // Adding fields to table ' . $table->getName() . '.' . XMLDB_LINEFEED;
                foreach ($table->getFields() as $field) {
                        $result .= '        $table->add_field(' . "'" . $field->getName() . "', ";
                        $result .= $field->getPHP(false);
                        $result .= ');' . XMLDB_LINEFEED;
        }
                if ($keys = $table->getKeys()) {
            $result .= XMLDB_LINEFEED;
            $result .= '        // Adding keys to table ' . $table->getName() . '.' . XMLDB_LINEFEED;
            foreach ($keys as $key) {
                                $result .= '        $table->add_key(' . "'" . $key->getName() . "', ";
                                $result .= $key->getPHP();
                                $result .= ');' . XMLDB_LINEFEED;
            }
        }
                if ($indexes = $table->getIndexes()) {
            $result .= XMLDB_LINEFEED;
            $result .= '        // Adding indexes to table ' . $table->getName() . '.' . XMLDB_LINEFEED;
            foreach ($indexes as $index) {
                                $result .= '        $table->add_index(' . "'" . $index->getName() . "', ";
                                $result .= $index->getPHP();
                                $result .= ');' . XMLDB_LINEFEED;
            }
        }

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch create table for ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if (!$dbman->table_exists($table)) {' . XMLDB_LINEFEED;
        $result .= '            $dbman->create_table($table);' . XMLDB_LINEFEED;
        $result .= '        }' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function drop_table_php($structure, $table) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define table ' . $table->getName() . ' to be dropped.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Conditionally launch drop table for ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        if ($dbman->table_exists($table)) {' . XMLDB_LINEFEED;
        $result .= '            $dbman->drop_table($table);' . XMLDB_LINEFEED;
        $result .= '        }' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

    
    function rename_table_php($structure, $table) {

        $result = '';
                if (!$table = $structure->getTable($table)) {
            return false;
        }
        if ($table->getAllErrors()) {
            return false;
        }

                $result .= XMLDB_PHP_HEADER;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Define table ' . $table->getName() . ' to be renamed to NEWNAMEGOESHERE.' . XMLDB_LINEFEED;
        $result .= '        $table = new xmldb_table(' . "'" . $table->getName() . "'" . ');' . XMLDB_LINEFEED;

                $result .= XMLDB_LINEFEED;
        $result .= '        // Launch rename table for ' . $table->getName() . '.' . XMLDB_LINEFEED;
        $result .= '        $dbman->rename_table($table, ' . "'NEWNAMEGOESHERE'" . ');' . XMLDB_LINEFEED;

                $result .= $this->upgrade_savepoint_php ($structure);

                $result .= XMLDB_PHP_FOOTER;

        return $result;
    }

}

