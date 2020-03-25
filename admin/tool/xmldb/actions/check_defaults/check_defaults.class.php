<?php




class check_defaults extends XMLDBCheckAction {

    
    function init() {
        $this->introstr = 'confirmcheckdefaults';
        parent::init();

        
        
                $this->loadStrings(array(
            'wrongdefaults' => 'tool_xmldb',
            'nowrongdefaultsfound' => 'tool_xmldb',
            'yeswrongdefaultsfound' => 'tool_xmldb',
            'expected' => 'tool_xmldb',
            'actual' => 'tool_xmldb',
        ));
    }

    protected function check_table(xmldb_table $xmldb_table, array $metacolumns) {
        $o = '';
        $wrong_fields = array();

                if ($xmldb_fields = $xmldb_table->getFields()) {
            $o.='        <ul>';
            foreach ($xmldb_fields as $xmldb_field) {

                                $xmldbdefault = $xmldb_field->getDefault();

                                if (!isset($metacolumns[$xmldb_field->getName()]) or $xmldb_field->getName() == 'id') {
                    continue;
                }

                                $metacolumn = $metacolumns[$xmldb_field->getName()];

                                $o.='            <li>' . $this->str['field'] . ': ' . $xmldb_field->getName() . ' ';

                                if ($metacolumn->has_default==1) {
                    $physicaldefault = $metacolumn->default_value;
                }
                else {
                    $physicaldefault = '';
                }

                                if ($physicaldefault != $xmldbdefault) {
                    $info = '('.$this->str['expected']." '$xmldbdefault', ".$this->str['actual'].
                    " '$physicaldefault')";
                    $o.='<font color="red">' . $this->str['wrong'] . " $info</font>";
                                        $obj = new stdClass();
                    $obj->table = $xmldb_table;
                    $obj->field = $xmldb_field;
                    $obj->physicaldefault = $physicaldefault;
                    $obj->xmldbdefault = $xmldbdefault;
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
        $r.= '    <p class="centerpara">' . $this->str['wrongdefaults'] . ': ' . count($wrong_fields) . '</p>';
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';

                if (count($wrong_fields)) {
            $r.= '    <p class="centerpara">' . $this->str['yeswrongdefaultsfound'] . '</p>';
            $r.= '        <ul>';
            foreach ($wrong_fields as $obj) {
                $xmldb_table = $obj->table;
                $xmldb_field = $obj->field;
                $physicaldefault = $obj->physicaldefault;
                $xmldbdefault = $obj->xmldbdefault;

                                $sqlarr = $dbman->generator->getAlterFieldSQL($xmldb_table, $xmldb_field);

                $r.= '            <li>' . $this->str['table'] . ': ' . $xmldb_table->getName() . '. ' .
                                          $this->str['field'] . ': ' . $xmldb_field->getName() . ', ' .
                                          $this->str['expected'] . ' ' . "'$xmldbdefault'" . ' ' .
                                          $this->str['actual'] . ' ' . "'$physicaldefault'" . '</li>';
                                if ($sqlarr) {
                    $sqlarr = $dbman->generator->getEndedStatements($sqlarr);
                    $s.= '<code>' . str_replace("\n", '<br />', implode('<br />', $sqlarr)) . '</code><br />';
                }
            }
            $r.= '        </ul>';
                        $r.= '<hr />' . $s;
        } else {
            $r.= '    <p class="centerpara">' . $this->str['nowrongdefaultsfound'] . '</p>';
        }
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';
                $r.= '    <p class="centerpara">' . $this->str['completelogbelow'] . '</p>';
        $r.= '  </td></tr>';
        $r.= '</table>';

        return $r;
    }
}
