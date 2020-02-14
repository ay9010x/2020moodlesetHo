<?php




abstract class XMLDBCheckAction extends XMLDBAction {
    
    protected $introstr = '';

    
    function init() {
        parent::init();

        
        
                $this->loadStrings(array(
            $this->introstr => 'tool_xmldb',
            'ok' => '',
            'wrong' => 'tool_xmldb',
            'table' => 'tool_xmldb',
            'field' => 'tool_xmldb',
            'searchresults' => 'tool_xmldb',
            'completelogbelow' => 'tool_xmldb',
            'yes' => '',
            'no' => '',
            'error' => '',
            'back' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $DB, $OUTPUT;

                $dbman = $DB->get_manager();

                $problemsfound = array();

        
                $confirmed = optional_param('confirmed', false, PARAM_BOOL);

                if (!$confirmed) {
            $o = '<table class="generaltable" border="0" cellpadding="5" cellspacing="0" id="notice">';
            $o.= '  <tr><td class="generalboxcontent">';
            $o.= '    <p class="centerpara">' . $this->str[$this->introstr] . '</p>';
            $o.= '    <table class="boxaligncenter" cellpadding="20"><tr><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=' . $this->title . '&amp;confirmed=yes&amp;sesskey=' . sesskey() . '" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['yes'] .'" /></fieldset></form></div>';
            $o.= '      </td><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=main_view" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['no'] .'" /></fieldset></form></div>';
            $o.= '      </td></tr>';
            $o.= '    </table>';
            $o.= '  </td></tr>';
            $o.= '</table>';

            $this->output = $o;
        } else {
                        $b = ' <p class="centerpara buttons">';
            $b .= '<a href="index.php">[' . $this->str['back'] . ']</a>';
            $b .= '</p>';

                        if ($XMLDB->dbdirs) {
                $dbdirs = $XMLDB->dbdirs;
                $o='<ul>';
                foreach ($dbdirs as $dbdir) {
                                        if (!$dbdir->path_exists) {
                        continue;
                    }
                                        $xmldb_file = new xmldb_file($dbdir->path . '/install.xml');

                                        if (!$xmldb_file->fileExists()) {
                        continue;
                    }
                                        $loaded = $xmldb_file->loadXMLStructure();
                    if (!$loaded || !$xmldb_file->isLoaded()) {
                        echo $OUTPUT->notification('Errors found in XMLDB file: '. $dbdir->path . '/install.xml');
                        continue;
                    }
                                        $structure = $xmldb_file->getStructure();

                    $o.='    <li>' . str_replace($CFG->dirroot . '/', '', $dbdir->path . '/install.xml');
                                        if ($xmldb_tables = $structure->getTables()) {
                        $o.='        <ul>';
                                                foreach ($xmldb_tables as $xmldb_table) {
                                                        if (!$dbman->table_exists($xmldb_table)) {
                                continue;
                            }
                                                        if (!$metacolumns = $DB->get_columns($xmldb_table->getName())) {
                                                                continue;
                            }
                                                        $o.='            <li>' . $xmldb_table->getName();
                                                        list($output, $newproblems) = $this->check_table($xmldb_table, $metacolumns);
                            $o.=$output;
                            $problemsfound = array_merge($problemsfound, $newproblems);
                            $o.='    </li>';
                                                        if ($currenttl = @ini_get('max_execution_time')) {
                                @ini_set('max_execution_time',$currenttl);
                            }
                        }
                        $o.='        </ul>';
                    }
                    $o.='    </li>';
                }
                $o.='</ul>';
            }

                        $r = $this->display_results($problemsfound);

                        $this->output = $b . $r . $o;
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }

    
    abstract protected function check_table(xmldb_table $xmldb_table, array $metacolumns);

    
    abstract protected function display_results(array $problems_found);
}
