<?php




class XMLDBAction {

    
    protected $does_generate;

    
    protected $title;

    
    protected $str;

    
    protected $output;

    
    protected $errormsg;

    
    protected $postaction;

    
    protected $sesskey_protected;

    
    function __construct() {
        $this->init();
    }

    
    function init() {
        $this->does_generate = ACTION_NONE;
        $this->title     = strtolower(get_class($this));
        $this->str       = array();
        $this->output    = NULL;
        $this->errormsg  = NULL;
        $this->subaction = NULL;
        $this->sesskey_protected = true;
    }

    
    function getDoesGenerate() {
        return $this->does_generate;
    }

    
    function getError() {
        return $this->errormsg;
    }

    
    function getOutput() {
        return $this->output;
    }

    
    function getPostAction() {
        return $this->postaction;
    }

    
    function getTitle() {
        return $this->str['title'];
    }

    
    function loadStrings($strings) {
                if (get_string_manager()->string_exists($this->title, 'tool_xmldb')) {
            $this->str['title'] = get_string($this->title, 'tool_xmldb');
        } else {
            $this->str['title'] = $this->title;
        }

                if ($strings) {
            foreach ($strings as $key => $module) {
                $this->str[$key] = get_string($key, $module);
            }
        }
    }

    
    function invoke() {

        global $SESSION;

                if ($this->sesskey_protected) {
            require_sesskey();
        }

                        if ($lastused = optional_param ('dir', NULL, PARAM_PATH)) {
            $SESSION->lastused = $lastused;
        }

        $this->postaction = optional_param ('postaction', NULL, PARAM_ALPHAEXT);
                if ($this->title == $this->postaction) {
            $this->postaction = NULL;
        }
    }

    
    function launch($action) {

        global $CFG;

                $actionsroot = "$CFG->dirroot/$CFG->admin/tool/xmldb/actions";
        $actionclass = $action . '.class.php';
        $actionpath = "$actionsroot/$action/$actionclass";

                $result = false;
        if (file_exists($actionpath) && is_readable($actionpath)) {
            require_once($actionpath);
            if ($xmldb_action = new $action) {
                $result = $xmldb_action->invoke();
                if ($result) {
                    if ($xmldb_action->does_generate != ACTION_NONE &&
                        $xmldb_action->getOutput()) {
                        $this->does_generate = $xmldb_action->does_generate;
                        $this->title = $xmldb_action->title;
                        $this->str = $xmldb_action->str;
                        $this->output .= $xmldb_action->getOutput();
                    }
                } else {
                    $this->errormsg = $xmldb_action->getError();
                }
            } else {
                $this->errormsg = "Error: cannot instantiate class (actions/$action/$actionclass)";
            }
        } else {
            $this->errormsg = "Error: wrong action specified ($action)";
        }
        return $result;
    }

    
    function upgrade_savepoint_php($structure) {
        global $CFG;

        
        $path = $structure->getPath();
        $plugintype = 'error';

        if ($path === 'lib/db') {
            $plugintype = 'lib';
            $pluginname = null;

        } else {
            $path = dirname($path);
            $pluginname = basename($path);
            $path = dirname($path);
            $plugintypes = core_component::get_plugin_types();
            foreach ($plugintypes as $type => $fulldir) {
                if ($CFG->dirroot.'/'.$path === $fulldir) {
                    $plugintype = $type;
                    break;
                }
            }
        }

        $result = '';

        switch ($plugintype ) {
            case 'lib':                 $result = XMLDB_LINEFEED .
                          '        // Main savepoint reached.' . XMLDB_LINEFEED .
                          '        upgrade_main_savepoint(true, XXXXXXXXXX);' . XMLDB_LINEFEED;
                break;
            case 'mod':                 $result = XMLDB_LINEFEED .
                          '        // ' . ucfirst($pluginname) . ' savepoint reached.' . XMLDB_LINEFEED .
                          '        upgrade_mod_savepoint(true, XXXXXXXXXX, ' . "'$pluginname'" . ');' . XMLDB_LINEFEED;
                break;
            case 'block':                 $result = XMLDB_LINEFEED .
                          '        // ' . ucfirst($pluginname) . ' savepoint reached.' . XMLDB_LINEFEED .
                          '        upgrade_block_savepoint(true, XXXXXXXXXX, ' . "'$pluginname'" . ');' . XMLDB_LINEFEED;
                break;
            default:                 $result = XMLDB_LINEFEED .
                          '        // ' . ucfirst($pluginname) . ' savepoint reached.' . XMLDB_LINEFEED .
                          '        upgrade_plugin_savepoint(true, XXXXXXXXXX, ' . "'$plugintype'" . ', ' . "'$pluginname'" . ');' . XMLDB_LINEFEED;
        }
        return $result;
    }
}
