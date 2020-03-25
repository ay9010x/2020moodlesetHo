<?php



defined('MOODLE_INTERNAL') || die();


class component_action {

    
    public $event;

    
    public $jsfunction = false;

    
    public $jsfunctionargs = array();

    
    public function __construct($event, $jsfunction, $jsfunctionargs=array()) {
        $this->event = $event;

        $this->jsfunction = $jsfunction;
        $this->jsfunctionargs = $jsfunctionargs;

        if (!empty($this->jsfunctionargs)) {
            if (empty($this->jsfunction)) {
                throw new coding_exception('The component_action object needs a jsfunction value to pass the jsfunctionargs to.');
            }
        }
    }
}



class confirm_action extends component_action {
    
    public function __construct($message, $callback = null, $continuelabel = null, $cancellabel = null) {
        if ($callback !== null) {
            debugging('The callback argument to new confirm_action() has been deprecated.' .
                    ' If you need to use a callback, please write Javascript to use moodle-core-notification-confirmation ' .
                    'and attach to the provided events.',
                    DEBUG_DEVELOPER);
        }
        parent::__construct('click', 'M.util.show_confirm_dialog', array(
                'message' => $message,
                'continuelabel' => $continuelabel, 'cancellabel' => $cancellabel));
    }
}



class popup_action extends component_action {

    
    public $jsfunction = 'openpopup';

    
    public $params = array(
            'height' =>  400,
            'width' => 500,
            'top' => 0,
            'left' => 0,
            'menubar' => false,
            'location' => false,
            'scrollbars' => true,
            'resizable' => true,
            'toolbar' => true,
            'status' => true,
            'directories' => false,
            'fullscreen' => false,
            'dependent' => true);

    
    public function __construct($event, $url, $name='popup', $params=array()) {
        global $CFG;
        $this->name = $name;

        $url = new moodle_url($url);

        if ($this->name) {
            $_name = $this->name;
            if (($_name = preg_replace("/\s/", '_', $_name)) != $this->name) {
                throw new coding_exception('The $name of a popup window shouldn\'t contain spaces - string modified. '. $this->name .' changed to '. $_name);
                $this->name = $_name;
            }
        } else {
            $this->name = 'popup';
        }

        foreach ($this->params as $var => $val) {
            if (array_key_exists($var, $params)) {
                $this->params[$var] = $params[$var];
            }
        }

        $attributes = array('url' => $url->out(false), 'name' => $name, 'options' => $this->get_js_options($params));
        if (!empty($params['fullscreen'])) {
            $attributes['fullscreen'] = 1;
        }
        parent::__construct($event, $this->jsfunction, $attributes);
    }

    
    public function get_js_options() {
        $jsoptions = '';

        foreach ($this->params as $var => $val) {
            if (is_string($val) || is_int($val)) {
                $jsoptions .= "$var=$val,";
            } elseif (is_bool($val)) {
                $jsoptions .= ($val) ? "$var," : "$var=0,";
            }
        }

        $jsoptions = substr($jsoptions, 0, strlen($jsoptions) - 1);

        return $jsoptions;
    }
}
