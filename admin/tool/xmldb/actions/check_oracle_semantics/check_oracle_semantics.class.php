<?php





class check_oracle_semantics extends XMLDBCheckAction {

    
    function init() {
        $this->introstr = 'confirmcheckoraclesemantics';
        parent::init();

        
        
                $this->loadStrings(array(
            'wrongoraclesemantics' => 'tool_xmldb',
            'nowrongoraclesemanticsfound' => 'tool_xmldb',
            'yeswrongoraclesemanticsfound' => 'tool_xmldb',
            'expected' => 'tool_xmldb',
            'actual' => 'tool_xmldb',
        ));
    }

    protected function check_table(xmldb_table $xmldb_table, array $metacolumns) {
        global $DB;
        $o = '';
        $wrong_fields = array();

                if ($xmldb_fields = $xmldb_table->getFields()) {
            $o .= '<ul>';
            foreach ($xmldb_fields as $xmldb_field) {

                                if ($xmldb_field->getType() != XMLDB_TYPE_CHAR) {
                    continue;
                }

                $o.='<li>' . $this->str['field'] . ': ' . $xmldb_field->getName() . ' ';

                                                $params = array(
                    'table_name' => core_text::strtoupper($DB->get_prefix() . $xmldb_table->getName()),
                    'column_name' => core_text::strtoupper($xmldb_field->getName()),
                    'data_type' => 'VARCHAR2');
                $currentsemantic = $DB->get_field_sql('
                    SELECT char_used
                      FROM user_tab_columns
                     WHERE table_name = :table_name
                       AND column_name = :column_name
                       AND data_type = :data_type', $params);

                                if ($currentsemantic == 'B') {
                    $info = '(' . $this->str['expected'] . " 'CHAR', " . $this->str['actual'] . " 'BYTE')";
                    $o .= '<font color="red">' . $this->str['wrong'] . " $info</font>";
                                        $obj = new stdClass();
                    $obj->table = $xmldb_table;
                    $obj->field = $xmldb_field;
                    $wrong_fields[] = $obj;
                } else {
                    $o .= '<font color="green">' . $this->str['ok'] . '</font>';
                }
                $o .= '</li>';
            }
            $o .= '</ul>';
        }

        return array($o, $wrong_fields);
    }

    protected function display_results(array $wrong_fields) {
        global $DB;
        $dbman = $DB->get_manager();

        $s = '';
        $r = '<table class="generaltable boxaligncenter boxwidthwide" border="0" cellpadding="5" cellspacing="0" id="results">';
        $r.= '  <tr><td class="generalboxcontent">';
        $r.= '    <h2 class="main">' . $this->str['searchresults'] . '</h2>';
        $r.= '    <p class="centerpara">' . $this->str['wrongoraclesemantics'] . ': ' . count($wrong_fields) . '</p>';
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';

                if (count($wrong_fields)) {
            $r.= '    <p class="centerpara">' . $this->str['yeswrongoraclesemanticsfound'] . '</p>';
            $r.= '        <ul>';
            foreach ($wrong_fields as $obj) {
                $xmldb_table = $obj->table;
                $xmldb_field = $obj->field;

                $r.= '            <li>' . $this->str['table'] . ': ' . $xmldb_table->getName() . '. ' .
                                          $this->str['field'] . ': ' . $xmldb_field->getName() . ', ' .
                                          $this->str['expected'] . ' ' . "'CHAR'" . ' ' .
                                          $this->str['actual'] . ' ' . "'BYTE'" . '</li>';

                $sql = 'ALTER TABLE ' . $DB->get_prefix() . $xmldb_table->getName() . ' MODIFY ' .
                       $xmldb_field->getName() . ' VARCHAR2(' . $xmldb_field->getLength() . ' CHAR)';
                $sql = $dbman->generator->getEndedStatements($sql);
                $s.= '<code>' . str_replace("\n", '<br />', $sql) . '</code><br />';
            }
            $r.= '        </ul>';
                        $r.= '<hr />' . $s;
        } else {
            $r.= '    <p class="centerpara">' . $this->str['nowrongoraclesemanticsfound'] . '</p>';
        }
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';
                $r.= '    <p class="centerpara">' . $this->str['completelogbelow'] . '</p>';
        $r.= '  </td></tr>';
        $r.= '</table>';

        return $r;
    }
}
