<?php




class check_indexes extends XMLDBCheckAction {

    
    function init() {
        $this->introstr = 'confirmcheckindexes';
        parent::init();

        
        
                $this->loadStrings(array(
            'missing' => 'tool_xmldb',
            'key' => 'tool_xmldb',
            'index' => 'tool_xmldb',
            'missingindexes' => 'tool_xmldb',
            'nomissingindexesfound' => 'tool_xmldb',
            'yesmissingindexesfound' => 'tool_xmldb',
        ));
    }

    protected function check_table(xmldb_table $xmldb_table, array $metacolumns) {
        global $DB;
        $dbman = $DB->get_manager();

        $o = '';
        $missing_indexes = array();

                if ($xmldb_keys = $xmldb_table->getKeys()) {
            $o.='        <ul>';
            foreach ($xmldb_keys as $xmldb_key) {
                $o.='            <li>' . $this->str['key'] . ': ' . $xmldb_key->readableInfo() . ' ';
                                if ($xmldb_key->getType() == XMLDB_KEY_PRIMARY) {
                    $o.='<font color="green">' . $this->str['ok'] . '</font></li>';
                    continue;
                }
                                                if (!$dbman->generator->getKeySQL($xmldb_table, $xmldb_key) || $xmldb_key->getType() == XMLDB_KEY_FOREIGN) {
                                        $xmldb_index = new xmldb_index('anyname');
                    $xmldb_index->setFields($xmldb_key->getFields());
                    switch ($xmldb_key->getType()) {
                        case XMLDB_KEY_UNIQUE:
                        case XMLDB_KEY_FOREIGN_UNIQUE:
                            $xmldb_index->setUnique(true);
                            break;
                        case XMLDB_KEY_FOREIGN:
                            $xmldb_index->setUnique(false);
                            break;
                    }
                                        if ($dbman->index_exists($xmldb_table, $xmldb_index)) {
                        $o.='<font color="green">' . $this->str['ok'] . '</font>';
                    } else {
                        $o.='<font color="red">' . $this->str['missing'] . '</font>';
                                                $obj = new stdClass();
                        $obj->table = $xmldb_table;
                        $obj->index = $xmldb_index;
                        $missing_indexes[] = $obj;
                    }
                }
                $o.='</li>';
            }
            $o.='        </ul>';
        }
                if ($xmldb_indexes = $xmldb_table->getIndexes()) {
            $o.='        <ul>';
            foreach ($xmldb_indexes as $xmldb_index) {
                $o.='            <li>' . $this->str['index'] . ': ' . $xmldb_index->readableInfo() . ' ';
                                if ($dbman->index_exists($xmldb_table, $xmldb_index)) {
                    $o.='<font color="green">' . $this->str['ok'] . '</font>';
                } else {
                    $o.='<font color="red">' . $this->str['missing'] . '</font>';
                                        $obj = new stdClass();
                    $obj->table = $xmldb_table;
                    $obj->index = $xmldb_index;
                    $missing_indexes[] = $obj;
                }
                $o.='</li>';
            }
            $o.='        </ul>';
        }

        return array($o, $missing_indexes);
    }

    protected function display_results(array $missing_indexes) {
        global $DB;
        $dbman = $DB->get_manager();

        $s = '';
        $r = '<table class="generaltable boxaligncenter boxwidthwide" border="0" cellpadding="5" cellspacing="0" id="results">';
        $r.= '  <tr><td class="generalboxcontent">';
        $r.= '    <h2 class="main">' . $this->str['searchresults'] . '</h2>';
        $r.= '    <p class="centerpara">' . $this->str['missingindexes'] . ': ' . count($missing_indexes) . '</p>';
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';

                if (count($missing_indexes)) {
            $r.= '    <p class="centerpara">' . $this->str['yesmissingindexesfound'] . '</p>';
            $r.= '        <ul>';
            foreach ($missing_indexes as $obj) {
                $xmldb_table = $obj->table;
                $xmldb_index = $obj->index;
                $sqlarr = $dbman->generator->getAddIndexSQL($xmldb_table, $xmldb_index);
                $r.= '            <li>' . $this->str['table'] . ': ' . $xmldb_table->getName() . '. ' .
                                          $this->str['index'] . ': ' . $xmldb_index->readableInfo() . '</li>';
                $sqlarr = $dbman->generator->getEndedStatements($sqlarr);
                $s.= '<code>' . str_replace("\n", '<br />', implode('<br />', $sqlarr)) . '</code><br />';

            }
            $r.= '        </ul>';
                        $r.= '<hr />' . $s;
        } else {
            $r.= '    <p class="centerpara">' . $this->str['nomissingindexesfound'] . '</p>';
        }
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';
                $r.= '    <p class="centerpara">' . $this->str['completelogbelow'] . '</p>';
        $r.= '  </td></tr>';
        $r.= '</table>';

        return $r;
    }
}
