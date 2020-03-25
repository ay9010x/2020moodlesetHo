<?php




class check_foreign_keys extends XMLDBCheckAction {

    
    function init() {
        $this->introstr = 'confirmcheckforeignkeys';
        parent::init();

        
        
                $this->loadStrings(array(
            'key' => 'tool_xmldb',
            'violatedforeignkeys' => 'tool_xmldb',
            'noviolatedforeignkeysfound' => 'tool_xmldb',
            'violatedforeignkeysfound' => 'tool_xmldb',
            'violations' => 'tool_xmldb',
            'unknowntable' => 'tool_xmldb',
            'unknownfield' => 'tool_xmldb',
        ));
    }

    protected function check_table(xmldb_table $xmldb_table, array $metacolumns) {
        global $DB;
        $dbman = $DB->get_manager();

        $strictchecks = optional_param('strict', false, PARAM_BOOL);

        $o = '';
        $violatedkeys = array();

                if ($xmldb_keys = $xmldb_table->getKeys()) {
            $o.='        <ul>';
            foreach ($xmldb_keys as $xmldb_key) {
                                if (!in_array($xmldb_key->getType(), array(XMLDB_KEY_FOREIGN, XMLDB_KEY_FOREIGN_UNIQUE))) {
                    continue;
                }
                $o.='            <li>' . $this->str['key'] . ': ' . $xmldb_key->readableInfo() . ' ';

                $reftable = $xmldb_key->getRefTable();
                if (!$dbman->table_exists($reftable)) {
                    $o.='<font color="red">' . $this->str['unknowntable'] . '</font>';
                                        $violation = new stdClass();
                    $violation->string = 'fkunknowntable';
                    $violation->table = $xmldb_table;
                    $violation->key = $xmldb_key;
                    $violation->reftable = $reftable;
                    $violatedkeys[] = $violation;
                    continue;
                }

                                $keyfields = $xmldb_key->getFields();
                $reffields = $xmldb_key->getRefFields();
                $joinconditions = array();
                $nullnessconditions = array();
                $params = array();
                foreach ($keyfields as $i => $field) {
                    if (!$dbman->field_exists($reftable, $reffields[$i])) {
                        $o.='<font color="red">' . $this->str['unknownfield'] . '</font>';
                                                $violation = new stdClass();
                        $violation->string = 'fkunknownfield';
                        $violation->table = $xmldb_table;
                        $violation->key = $xmldb_key;
                        $violation->reftable = $reftable;
                        $violation->reffield = $reffields[$i];
                        $violatedkeys[] = $violation;
                        continue 2;
                    }

                    $joinconditions[] = 't1.' . $field . ' = t2.' . $reffields[$i];
                    $xmldb_field = $xmldb_table->getField($field);
                    $default = $xmldb_field->getDefault();
                    if (!$xmldb_field->getNotNull()) {
                        $nullnessconditions[] = 't1.' . $field . ' IS NOT NULL';
                    } else if (!$strictchecks && ($default == '0' || !$default)) {
                                                                                                $nullnessconditions[] = 't1.' . $field . ' <> ?';
                        $params[] = $xmldb_field->getDefault();
                    }
                }
                $nullnessconditions[] = 't2.id IS NULL';
                $sql = 'SELECT count(1) FROM {' . $xmldb_table->getName() .
                        '} t1 LEFT JOIN {' . $reftable . '} t2 ON ' .
                        implode(' AND ', $joinconditions) . ' WHERE ' .
                        implode(' AND ', $nullnessconditions);

                                $violations = $DB->count_records_sql($sql, $params);
                if ($violations == 0) {
                    $o.='<font color="green">' . $this->str['ok'] . '</font>';
                } else {
                    $o.='<font color="red">' . $this->str['violations'] . '</font>';
                                        $violation = new stdClass;
                    $violation->string = 'fkviolationdetails';
                    $violation->table = $xmldb_table;
                    $violation->key = $xmldb_key;
                    $violation->numviolations = $violations;
                    $violation->numrows = $DB->count_records($xmldb_table->getName());
                    $violation->sql = str_replace('count(1)', '*', $sql);
                    if (!empty($params)) {
                        $violation->sqlparams = '(' . implode(', ', $params) . ')';
                    } else {
                        $violation->sqlparams = '';
                    }
                    $violatedkeys[] = $violation;
                }
                $o.='</li>';
            }
            $o.='        </ul>';
        }

        return array($o, $violatedkeys);
    }

    protected function display_results(array $violatedkeys) {
        $r = '<table class="generaltable boxaligncenter boxwidthwide" border="0" cellpadding="5" cellspacing="0" id="results">';
        $r.= '  <tr><td class="generalboxcontent">';
        $r.= '    <h2 class="main">' . $this->str['searchresults'] . '</h2>';
        $r.= '    <p class="centerpara">' . $this->str['violatedforeignkeys'] . ': ' . count($violatedkeys) . '</p>';
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';

                if (count($violatedkeys)) {
            $r.= '    <p class="centerpara">' . $this->str['violatedforeignkeysfound'] . '</p>';
            $r.= '        <ul>';
            foreach ($violatedkeys as $violation) {
                $violation->tablename = $violation->table->getName();
                $violation->keyname = $violation->key->getName();

                $r.= '            <li>' .get_string($violation->string, 'tool_xmldb', $violation);
                if (!empty($violation->sql)) {
                    $r.= '<pre>' . s($violation->sql) . '; ' . s($violation->sqlparams) . '</pre>';
                }
                $r.= '</li>';
            }
            $r.= '        </ul>';
        } else {
            $r.= '    <p class="centerpara">' . $this->str['noviolatedforeignkeysfound'] . '</p>';
        }
        $r.= '  </td></tr>';
        $r.= '  <tr><td class="generalboxcontent">';
                $r.= '    <p class="centerpara">' . $this->str['completelogbelow'] . '</p>';
        $r.= '  </td></tr>';
        $r.= '</table>';

        return $r;
    }
}
