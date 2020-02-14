<?php




class check_bigints extends XMLDBCheckAction {
    
    function init() {
        global $DB;

        $this->introstr = 'confirmcheckbigints';
        parent::init();

        
        
                $this->loadStrings(array(
            'wrongints' => 'tool_xmldb',
            'nowrongintsfound' => 'tool_xmldb',
            'yeswrongintsfound' => 'tool_xmldb',
        ));
    }

    protected function check_table(xmldb_table $xmldb_table, array $metacolumns) {
        $o = '';
        $wrong_fields = array();

                if ($xmldb_fields = $xmldb_table->getFields()) {
            $o.='        <ul>';
            foreach ($xmldb_fields as $xmldb_field) {
                                if ($xmldb_field->getType() != XMLDB_TYPE_INTEGER) {
                    continue;
                }
                                if (!isset($metacolumns[$xmldb_field->getName()])) {
                    continue;
                }
                $minlength = $xmldb_field->getLength();
                if ($minlength > 18) {
                                        $minlength = 18;
                }
                                $metacolumn = $metacolumns[$xmldb_field->getName()];
                                $o.='            <li>' . $this->str['field'] . ': ' . $xmldb_field->getName() . ' ';
                                if (($metacolumn->meta_type != 'I' and $metacolumn->meta_type != 'R') or $metacolumn->max_length < $minlength) {
                    $o.='<font color="red">' . $this->str['wrong'] . '</font>';
                                        $obj = new stdClass();
                    $obj->table = $xmldb_table;
                    $obj->field = $xmldb_field;
                    $wrong_fields[] = $obj;
                } else {
                    $o.='<font color="green">' . $this->str['ok'] . '</font>';
                }
                $o.='</li>';
            }
            $o.='        </ul>';
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
        $r.= '    <p class="centerpara">' . $this->str['wrongints'] . ': ' . count($wrong_fields) . '</p>';
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';

                if (count($wrong_fields)) {
            $r.= '    <p class="centerpara">' . $this->str['yeswrongintsfound'] . '</p>';
            $r.= '        <ul>';
            foreach ($wrong_fields as $obj) {
                $xmldb_table = $obj->table;
                $xmldb_field = $obj->field;
                $sqlarr = $dbman->generator->getAlterFieldSQL($xmldb_table, $xmldb_field);
                $r.= '            <li>' . $this->str['table'] . ': ' . $xmldb_table->getName() . '. ' .
                                          $this->str['field'] . ': ' . $xmldb_field->getName() . '</li>';
                                if ($sqlarr) {
                    $sqlarr = $dbman->generator->getEndedStatements($sqlarr);
                    $s.= '<code>' . str_replace("\n", '<br />', implode('<br />', $sqlarr)). '</code><br />';
                }
            }
            $r.= '        </ul>';
                        $r.= '<hr />' . $s;
        } else {
            $r.= '    <p class="centerpara">' . $this->str['nowrongintsfound'] . '</p>';
        }
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';
                $r.= '    <p class="centerpara">' . $this->str['completelogbelow'] . '</p>';
        $r.= '  </td></tr>';
        $r.= '</table>';

        return $r;
    }
}
