<?php




class delete_xml_file extends XMLDBAction {

    
    function init() {
        parent::init();

        
                $this->loadStrings(array(
            'confirmdeletexmlfile' => 'tool_xmldb',
            'yes' => '',
            'no' => ''
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_CLEAN);
        $dirpath = $CFG->dirroot . $dirpath;

        $confirmed = optional_param('confirmed', false, PARAM_BOOL);

                if (!$confirmed) {
            $o = '<table width="60" class="generaltable" border="0" cellpadding="5" cellspacing="0" id="notice">';
            $o.= '  <tr><td class="generalboxcontent">';
            $o.= '    <p class="centerpara">' . $this->str['confirmdeletexmlfile'] . '<br /><br />' . $dirpath . '/install.php</p>';
            $o.= '    <table class="boxaligncenter" cellpadding="20"><tr><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=delete_xml_file&amp;sesskey=' . sesskey() . '&amp;confirmed=yes&amp;dir=' . urlencode(str_replace($CFG->dirroot, '', $dirpath)) . '&amp;postaction=main_view#lastused" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['yes'] .'" /></fieldset></form></div>';
            $o.= '      </td><td>';
            $o.= '      <div class="singlebutton">';
            $o.= '        <form action="index.php?action=main_view#lastused" method="post"><fieldset class="invisiblefieldset">';
            $o.= '          <input type="submit" value="'. $this->str['no'] .'" /></fieldset></form></div>';
            $o.= '      </td></tr>';
            $o.= '    </table>';
            $o.= '  </td></tr>';
            $o.= '</table>';

            $this->output = $o;
        } else {
                        if (!empty($XMLDB->dbdirs)) {
                if (isset($XMLDB->dbdirs[$dirpath])) {
                    $dbdir = $XMLDB->dbdirs[$dirpath];
                    if ($dbdir) {
                        @unlink($dirpath . '/install.xml');
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

