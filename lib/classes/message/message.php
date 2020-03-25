<?php



namespace core\message;

defined('MOODLE_INTERNAL') || die();


class message {
    
    private $component;

    
    private $name;

    
    private $userfrom;

    
    private $userto;

    
    private $subject;

    
    private $fullmessage;

    
    private $fullmessageformat;

    
    private $fullmessagehtml;

    
    private $smallmessage;

    
    private $notification;

    
    private $contexturl;

    
    private $contexturlname;

    
    private $replyto;

    
    private $savedmessageid;

    
    private $attachment;

    
    private $attachname;

    
    private $properties = array('component', 'name', 'userfrom', 'userto', 'subject', 'fullmessage', 'fullmessageformat',
                                'fullmessagehtml', 'smallmessage', 'notification', 'contexturl', 'contexturlname', 'savedmessageid',
                                'replyto', 'attachment', 'attachname');

    
    private $additionalcontent = array();

    
    protected function get_fullmessagehtml($processorname = '') {
        if (!empty($processorname) && isset($this->additionalcontent[$processorname])) {
            return $this->get_message_with_additional_content($processorname, 'fullmessagehtml');
        } else {
            return $this->fullmessagehtml;
        }
    }

    
    protected function get_fullmessage($processorname = '') {
        if (!empty($processorname) && isset($this->additionalcontent[$processorname])) {
            return $this->get_message_with_additional_content($processorname, 'fullmessage');
        } else {
            return $this->fullmessage;
        }
    }

    
    protected function get_smallmessage($processorname = '') {
        if (!empty($processorname) && isset($this->additionalcontent[$processorname])) {
            return $this->get_message_with_additional_content($processorname, 'smallmessage');
        } else {
            return $this->smallmessage;
        }
    }

    
    protected function get_message_with_additional_content($processorname, $messagetype) {
        $message = $this->$messagetype;
        if (isset($this->additionalcontent[$processorname]['*'])) {
                        $pattern = $this->additionalcontent[$processorname]['*'];
            $message = empty($pattern['header']) ? $message : $pattern['header'] . $message;
            $message = empty($pattern['footer']) ? $message : $message . $pattern['footer'];
        }

        if (isset($this->additionalcontent[$processorname][$messagetype])) {
                        $pattern = $this->additionalcontent[$processorname][$messagetype];
            $message = empty($pattern['header']) ? $message : $pattern['header'] . $message;
            $message = empty($pattern['footer']) ? $message : $message . $pattern['footer'];
        }

        return $message;
    }

    
    public function __get($prop) {
        if (in_array($prop, $this->properties)) {
            return $this->$prop;
        }
        throw new \coding_exception("Invalid property $prop specified");
    }

    
    public function __set($prop, $value) {
        if (in_array($prop, $this->properties)) {
            return $this->$prop = $value;
        }
        throw new \coding_exception("Invalid property $prop specified");
    }

    
    public function __isset($prop) {
        if (in_array($prop, $this->properties)) {
            return isset($this->$prop);
        }
        throw new \coding_exception("Invalid property $prop specified");
    }

    
    public function set_additional_content($processorname, $content) {
        $this->additionalcontent[$processorname] = $content;
    }

    
    public function get_eventobject_for_processor($processorname) {
                
        $eventdata = new \stdClass();
        foreach ($this->properties as $prop) {
            $func = "get_$prop";
            $eventdata->$prop = method_exists($this, $func) ? $this->$func($processorname) : $this->$prop;
        }
        return $eventdata;
    }
}