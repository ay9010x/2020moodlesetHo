<?php




class view_structure_sql extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'selectdb' => 'tool_xmldb',
            'back' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB, $DB;
        $dbman = $DB->get_manager();

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

                $b = ' <p class="centerpara buttons">';
        $b .= '<a href="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '">[' . $this->str['back'] . ']</a>';
        $b .= '</p>';
        $o = $b;

        $o.= '    <table id="formelements" class="boxaligncenter" cellpadding="5">';
        $o.= '      <tr><td><textarea cols="80" rows="32">';
                if ($starr = $DB->get_manager()->generator->getCreateStructureSQL($structure)) {
            $starr = $dbman->generator->getEndedStatements($starr);
            $sqltext = '';
            foreach ($starr as $st) {
                $sqltext .= s($st) . "\n\n";
            }
            $sqltext = trim($sqltext);
            $o.= $sqltext;
        }
        $o.= '</textarea></td></tr>';
        $o.= '    </table>';

        $this->output = $o;

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

