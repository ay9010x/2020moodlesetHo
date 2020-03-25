<?php




class view_xml extends XMLDBAction {

    
    function init() {
        parent::init();
                $this->can_subaction = ACTION_NONE;
        
                $this->sesskey_protected = false; 
                $this->loadStrings(array(
                    ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_XML;

                global $CFG, $XMLDB;

        
                $file = required_param('file', PARAM_PATH);
        $file = $CFG->dirroot . $file;
                        if (substr($file, 0, strlen($CFG->dirroot)) == $CFG->dirroot &&
            substr(dirname($file), -2, 2) == 'db') {
                        $this->output = file_get_contents($file);
        } else {
                        $this->does_generate = ACTION_GENERATE_HTML;
            $this->errormsg = 'File not viewable (' . $file .')';
            $result = false;
        }

                return $result;
    }
}

