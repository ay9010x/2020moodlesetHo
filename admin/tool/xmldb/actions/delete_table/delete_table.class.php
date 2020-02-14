<?php




class delete_table extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'confirmdeletetable' => 'tool_xmldb',
            'yes' => '',
            'no' => ''
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;
        $tableparam = required_param('table', PARAM_CLEAN);

        $confirmed = optional_param('confirmed', false, PARAM_BOOL);

                if (!$confirmed) {
            $o = '<table width="60" class="generaltable" border="0" cellpadding="5" cellspacing="0" id="notice">';
            $o.= '  <tr><td class="generalboxcontent">';
            $o.= '    <p class="centerpara">' . $this->str['confirmdeletetable'] . '<br /><br />' . $tableparam . '</p>';
            $o.= '    <table class="boxaligncenter" cellpadding="20"><tr><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=delete_table&amp;sesskey=' . sesskey() . '&amp;confirmed=yes&amp;postaction=edit_xml_file&amp;table=' . $tableparam . '&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['yes'] .'" /></fieldset></form></div>';
            $o.= '      </td><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=edit_xml_file&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['no'] .'" /></fieldset></form></div>';
            $o.= '      </td></tr>';
            $o.= '    </table>';
            $o.= '  </td></tr>';
            $o.= '</table>';

            $this->output = $o;
        } else {
                        if (!empty($XMLDB->editeddirs)) {
                if (isset($XMLDB->editeddirs[$dirpath])) {
                    $dbdir = $XMLDB->dbdirs[$dirpath];
                    $editeddir = $XMLDB->editeddirs[$dirpath];
                    if ($editeddir) {
                        $structure = $editeddir->xml_file->getStructure();
                                                $structure->deleteTable($tableparam);
                    }
                }
            }
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }
}

