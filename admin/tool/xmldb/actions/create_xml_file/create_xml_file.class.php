<?php





class create_xml_file extends XMLDBAction {

    
    function init() {
        parent::init();
                $this->can_subaction = ACTION_NONE;
        
        
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_NONE;
        
                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $plugintype = $this->get_plugin_type($dirpath);
        $dirpath = $CFG->dirroot . $dirpath;
        $file = $dirpath . '/install.xml';

                $xmlpath = dirname(str_replace($CFG->dirroot . '/', '', $file));
        $xmlversion = userdate(time(), '%Y%m%d', 99, false);
        $xmlcomment = 'XMLDB file for Moodle ' . dirname($xmlpath);

        $xmltable = strtolower(basename(dirname($xmlpath)));
        if ($plugintype && $plugintype != 'mod') {
            $xmltable = $plugintype.'_'.$xmltable;
        }

                $c = '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        $c.= '  <XMLDB PATH="' . $xmlpath . '" VERSION="' . $xmlversion .'" COMMENT="' . $xmlcomment .'">' . "\n";
        $c.= '    <TABLES>' . "\n";
        $c.= '      <TABLE NAME="' . $xmltable . '" COMMENT="Default comment for ' . $xmltable .', please edit me">' . "\n";
        $c.= '        <FIELDS>' . "\n";
        $c.= '          <FIELD NAME="id" TYPE="int" LENGTH="10" NOTNULL="true" SEQUENCE="true" />' . "\n";
        $c.= '        </FIELDS>' . "\n";
        $c.= '        <KEYS>' . "\n";
        $c.= '          <KEY NAME="primary" TYPE="primary" FIELDS="id" />' . "\n";
        $c.= '        </KEYS>' . "\n";
        $c.= '      </TABLE>' . "\n";
        $c.= '    </TABLES>' . "\n";
        $c.= '  </XMLDB>';

        if (!file_put_contents($file, $c)) {
            $errormsg = 'Error creando fichero ' . $file;
            $result = false;
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

                return $result;
    }

    
    function get_plugin_type($dirpath) {
        global $CFG;
        $dirpath = $CFG->dirroot.$dirpath;
                $plugintypes = array_reverse(core_component::get_plugin_types());
        foreach ($plugintypes as $plugintype => $pluginbasedir) {
            if (substr($dirpath, 0, strlen($pluginbasedir)) == $pluginbasedir) {
                return $plugintype;
            }
        }
        return null;
    }
}

