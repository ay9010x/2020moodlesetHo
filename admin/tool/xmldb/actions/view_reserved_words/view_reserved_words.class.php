<?php




class view_reserved_words extends XMLDBAction {

    
    function init() {
        parent::init();

            $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'listreservedwords' => 'tool_xmldb',
            'wrongreservedwords' => 'tool_xmldb',
            'table' => 'tool_xmldb',
            'field' => 'tool_xmldb',
            'back' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $DB;

                require_once("$CFG->libdir/ddl/sql_generator.php");
        $reserved_words = sql_generator::getAllReservedWords();

                        $wronguses = array();
        $dbtables = $DB->get_tables();
        if ($dbtables) {
            foreach ($dbtables as $table) {
                if (array_key_exists($table, $reserved_words)) {
                    $wronguses[] = $this->str['table'] . ' - ' . $table . ' (' . implode(', ',$reserved_words[$table]) . ')';

                }
                $dbfields = $DB->get_columns($table);
                if ($dbfields) {
                    foreach ($dbfields as $dbfield) {
                        if (array_key_exists($dbfield->name, $reserved_words)) {
                            $wronguses[] = $this->str['field'] . ' - ' . $table . '->' . $dbfield->name . ' (' . implode(', ',$reserved_words[$dbfield->name]) . ')';
                        }
                    }
                }
            }
        }

                sort($wronguses);

                $b = ' <p class="centerpara buttons">';
        $b .= '<a href="index.php">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o = $b;

                if ($wronguses) {
            $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
            $o.= '      <tr><td align="center"><font color="red">' . $this->str['wrongreservedwords'] . '</font></td></tr>';
            $o.= '      <tr><td>';
            $o.= '        <ul><li>' . implode('</li><li>', $wronguses) . '</li></ul>';
            $o.= '      </td></tr>';
            $o.= '    </table>';
        }

                $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td align="center">' . $this->str['listreservedwords'].'</td></tr>';
        $o.= '      <tr><td><textarea cols="80" rows="32">';
        $o.= s(implode(', ', array_keys($reserved_words)));
        $o.= '</textarea></td></tr>';
        $o.= '    </table>';

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

