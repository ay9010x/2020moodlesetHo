<?php



class iCalendar_component {
    var $name             = NULL;
    var $properties       = NULL;
    var $components       = NULL;
    var $valid_properties = NULL;
    var $valid_components = NULL;
    
    var $parser_errors = NULL;

    function __construct() {
                if(empty($this->components)) {
            $this->components = array();
            foreach($this->valid_components as $name) {
                $this->components[$name] = array();
            }
        }
    }

    function get_name() {
        return $this->name;
    }

    function add_property($name, $value = NULL, $parameters = NULL) {

                $name = strtoupper($name);

                $xname = false;
        if(!isset($this->valid_properties[$name])) {
                        if(!rfc2445_is_xname($name)) {
                return false;
            }
                        $xname = true;
        }

                if($xname) {
            $property = new iCalendar_property_x;
            $property->set_name($name);
        }
        else {
            $classname = 'iCalendar_property_'.strtolower(str_replace('-', '_', $name));
            $property = new $classname;
        }

                if($value === NULL) {
            $value = $property->default_value();
            if($value === NULL) {
                return false;
            }
        }

                        $property->set_parent_component($this->name);

                
                if(!empty($parameters)) {
            foreach($parameters as $paramname => $paramvalue) {
                if(!$property->set_parameter($paramname, $paramvalue)) {
                    return false;
                }
            }

                                    if(!$property->invariant_holds()) {
                return false;
            }
        }

                if(!$property->set_value($value)) {
            return false;
        }

                        if(!$xname && $this->valid_properties[$name] & RFC2445_ONCE && isset($this->properties[$name])) {
            return false;
        } 
		else {
                         $this->properties[$name][] = $property;
        }

                if(!$this->invariant_holds()) {
                        array_pop($this->properties[$name]);
            if(empty($this->properties[$name])) {
                unset($this->properties[$name]);
            }
            return false;
        }

        return true;        
        
    }

    function add_component($component) {

                if(!is_object($component) || !is_subclass_of($component, 'iCalendar_component')) {
            return false;
        }

        $name = $component->get_name();

                if(!in_array($name, $this->valid_components)) {
            return false;
        }

                $this->components[$name][] = $component;

        return true;
    }

    function get_property_list($name) {
    }

    function invariant_holds() {
        return true;
    }

    function is_valid() {
                if(!empty($this->components)) {
            foreach($this->components as $component => $instances) {
                foreach($instances as $number => $instance) {
                    if(!$instance->is_valid()) {
                        return false;
                    }
                }
            }
        }

                        foreach($this->valid_properties as $property => $propdata) {
            if(($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
                $classname = 'iCalendar_property_'.strtolower(str_replace('-', '_', $property));
                $object    = new $classname;
                if($object->default_value() === NULL) {
                    return false;
                }
                unset($object);
            }
        }

        return true;
    }
    
    function serialize() {
                if(!$this->is_valid()) {
            return false;
        }

                        foreach($this->valid_properties as $property => $propdata) {
            if(($propdata & RFC2445_REQUIRED) && empty($this->properties[$property])) {
                $this->add_property($property);
            }
        }

                $string = rfc2445_fold('BEGIN:'.$this->name) . RFC2445_CRLF;

                if(!empty($this->properties)) {
            foreach($this->properties as $name => $properties) {
                foreach($properties as $property) {
                    $string .= $property->serialize();
                }
            }
        }

                if(!empty($this->components)) {
            foreach($this->components as $name => $components) {
                foreach($components as $component) {
                    $string .= $component->serialize();
                }
            }
        }

                $string .= rfc2445_fold('END:'.$this->name) . RFC2445_CRLF;

        return $string;
    }
    
    
    
    function unserialize($string) {
        $string = rfc2445_unfold($string);         $lines = preg_split("<".RFC2445_CRLF."|\n|\r>", $string, 0, PREG_SPLIT_NO_EMPTY);         
        $components = array();         $this->clear_errors();
        foreach ($lines as $key => $line) {
                        if (trim($line) == '') {
                continue;
            }

                        if (!preg_match('#^(?P<label>[-[:alnum:]]+)(?P<params>(?:;(?:(?:[-[:alnum:]]+)=(?:[^[:cntrl:]";:,]+|"[^[:cntrl:]"]+")))*):(?P<data>.*)$#', $line, $match)) {
                $this->parser_error('Invalid line: '.$key.', ignoring');
                continue;
            }

                        $params = array();
            if (preg_match_all('#;(?P<param>[-[:alnum:]]+)=(?P<value>[^[:cntrl:]";:,]+|"[^[:cntrl:]"]+")#', $match['params'], $pmatch)) {
                $params = array_combine($pmatch['param'], $pmatch['value']);
            } 
            $label = $match['label'];
            $data  = $match['data'];
            unset($match, $pmatch);

            if ($label == 'BEGIN') {
                                $current_component = array_pop($components);                 if ($current_component == null) {                     $current_component = $this;                 }
                if (in_array($data, $current_component->valid_components)) {                     if($current_component != $this) {
                        array_push($components, $current_component);                     }
                    if(strpos($data, 'V') === 0) {
                        $data = substr($data, 1);
                    }
                    $cname = 'iCalendar_' . strtolower($data);
                    $new_component = new $cname;
                    array_push($components, $new_component);                 } else {
                    if($current_component != $this) {
                        array_push($components, $current_component);
                        $this->parser_error('Invalid component type on line '.$key);
                    }                        
                }
                unset($current_component, $new_component);
            } else if ($label == 'END') {
                                $component = array_pop($components);                 $parent_component = array_pop($components);                 if($parent_component == null) {
                    $parent_component = $this;                 }
                if ($component !== null) {
                    if ($parent_component->add_component($component) === false) {
                        $this->parser_error("Failed to add component on line $key");
                    }
                }
                if ($parent_component != $this) {                         array_push($components, $parent_component);                 }
                unset($parent_component, $component);
            } else {
                
                $component = array_pop($components);                 if ($component == null) {                     $component = $this;                 }

                if ($component->add_property($label, $data, $params) === false) {
                    $this->parser_error("Failed to add property '$label' on line $key");
                }

                if($component != $this) {                     array_push($components, $component);                 }
                unset($component);
            }

        }
        
    }

    function clear_errors() {
        $this->parser_errors = array();
    }

    function parser_error($error) {
        $this->parser_errors[] = $error;
    }

}

class iCalendar extends iCalendar_component {
    var $name = 'VCALENDAR';

    function __construct() {
        $this->valid_properties = array(
            'CALSCALE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'METHOD'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRODID'      => RFC2445_REQUIRED | RFC2445_ONCE,
            'VERSION'     => RFC2445_REQUIRED | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL 
        );

        $this->valid_components = array(
            'VEVENT', 'VTODO', 'VJOURNAL', 'VFREEBUSY', 'VTIMEZONE', 'VALARM'
        );
        parent::__construct();
    }

}

class iCalendar_event extends iCalendar_component {

    var $name       = 'VEVENT';
    var $properties;
    
    function __construct() {
        
        $this->valid_components = array('VALARM');

        $this->valid_properties = array(
            'CLASS'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CREATED'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
                                    'DTSTAMP'        => RFC2445_REQUIRED | RFC2445_ONCE,
                                    'DTSTART'        => RFC2445_REQUIRED | RFC2445_ONCE,
            'GEO'            => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LAST-MODIFIED'  => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LOCATION'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRIORITY'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SEQUENCE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STATUS'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SUMMARY'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TRANSP'         => RFC2445_OPTIONAL | RFC2445_ONCE,
                                    'UID'            => RFC2445_REQUIRED | RFC2445_ONCE,
            'URL'            => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RECURRENCE-ID'  => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTEND'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DURATION'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTACH'         => RFC2445_OPTIONAL,
            'ATTENDEE'       => RFC2445_OPTIONAL,
            'CATEGORIES'     => RFC2445_OPTIONAL,
            'COMMENT'        => RFC2445_OPTIONAL,
            'CONTACT'        => RFC2445_OPTIONAL,
            'EXDATE'         => RFC2445_OPTIONAL,
            'EXRULE'         => RFC2445_OPTIONAL,
            'REQUEST-STATUS' => RFC2445_OPTIONAL,
            'RELATED-TO'     => RFC2445_OPTIONAL,
            'RESOURCES'      => RFC2445_OPTIONAL,
            'RDATE'          => RFC2445_OPTIONAL,
            'RRULE'          => RFC2445_OPTIONAL,
            RFC2445_XNAME    => RFC2445_OPTIONAL
        );

        parent::__construct();
    }

    function invariant_holds() {
                if(isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
            return false;
        }

        
        if(isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {
                                                if($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
                return false;
            }

                        if($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
                return false;
            }

        }
        return true;
    }

}

class iCalendar_todo extends iCalendar_component {
    var $name       = 'VTODO';
    var $properties;

    function __construct() {
        
        $this->valid_components = array('VALARM');

        $this->valid_properties = array(
            'CLASS'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'COMPLETED'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CREATED'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTAMP'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTAP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'GEO'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LOCATION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PERCENT'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PRIORITY'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RECURID'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SEQUENCE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STATUS'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SUMMARY'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'UID'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'URL'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DUE'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DURATION'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTACH'      => RFC2445_OPTIONAL,
            'ATTENDEE'    => RFC2445_OPTIONAL,
            'CATEGORIES'  => RFC2445_OPTIONAL,
            'COMMENT'     => RFC2445_OPTIONAL,
            'CONTACT'     => RFC2445_OPTIONAL,
            'EXDATE'      => RFC2445_OPTIONAL,
            'EXRULE'      => RFC2445_OPTIONAL,
            'RSTATUS'     => RFC2445_OPTIONAL,
            'RELATED'     => RFC2445_OPTIONAL,
            'RESOURCES'   => RFC2445_OPTIONAL,
            'RDATE'       => RFC2445_OPTIONAL,
            'RRULE'       => RFC2445_OPTIONAL,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );

        parent::__construct();
    }
    
    function invariant_holds() {
                if(isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
            return false;
        }

        
        if(isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {
                                                if($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
                return false;
            }

                        if($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
                return false;
            }

        }
        
        if(isset($this->properties['DUE']) && isset($this->properties['DTSTART'])) {
            if($this->properties['DUE'][0]->value <= $this->properties['DTSTART'][0]->value) {
                return false;
            }   
        }
        
        return true;
    }
    
}

class iCalendar_journal extends iCalendar_component {
    var $name = 'VJOURNAL';
    var $properties;
    
    function __construct() {
    	
        $this->valid_properties = array(
            'CLASS'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CREATED'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DESCRIPTION'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTART'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTAMP'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LAST-MODIFIED' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RECURRANCE-ID' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SEQUENCE'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'STATUS'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SUMMARY'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'UID'           => RFC2445_OPTIONAL | RFC2445_ONCE,
            'URL'           => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTACH'        => RFC2445_OPTIONAL,
            'ATTENDEE'      => RFC2445_OPTIONAL,
            'CATEGORIES'    => RFC2445_OPTIONAL,
            'COMMENT'       => RFC2445_OPTIONAL,
            'CONTACT'       => RFC2445_OPTIONAL,
            'EXDATE'        => RFC2445_OPTIONAL,
            'EXRULE'        => RFC2445_OPTIONAL,
            'RELATED-TO'    => RFC2445_OPTIONAL,
            'RDATE'         => RFC2445_OPTIONAL,
            'RRULE'         => RFC2445_OPTIONAL,
            RFC2445_XNAME   => RFC2445_OPTIONAL            
        );
        
         parent::__construct();
        
    }
}

class iCalendar_freebusy extends iCalendar_component {
    var $name       = 'VFREEBUSY';
    var $properties;

    function __construct() {
        $this->valid_components = array();
        $this->valid_properties = array(
            'CONTACT'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTART'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTEND'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DURATION'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DTSTAMP'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ORGANIZER'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'UID'           => RFC2445_OPTIONAL | RFC2445_ONCE,
            'URL'           => RFC2445_OPTIONAL | RFC2445_ONCE,
                        'ATTENDEE'      => RFC2445_OPTIONAL,
            'COMMENT'       => RFC2445_OPTIONAL,
            'FREEBUSY'      => RFC2445_OPTIONAL,
            'RSTATUS'       => RFC2445_OPTIONAL,
            RFC2445_XNAME   => RFC2445_OPTIONAL
        );
        
        parent::__construct();
    }
    
    function invariant_holds() {
                if(isset($this->properties['DTEND']) && isset($this->properties['DURATION'])) {
            return false;
        }

        
        if(isset($this->properties['DTEND']) && isset($this->properties['DTSTART'])) {
                                                if($this->properties['DTEND'][0]->value <= $this->properties['DTSTART'][0]->value) {
                return false;
            }

                        if($this->properties['DTEND'][0]->val_type != $this->properties['DTSTART'][0]->val_type) {
                return false;
            }

        }
        return true;
    }
}

class iCalendar_alarm extends iCalendar_component {
    var $name       = 'VALARM';
    var $properties;

    function __construct() {
        $this->valid_components = array();
        $this->valid_properties = array(
            'ACTION'    => RFC2445_REQUIRED | RFC2445_ONCE,
            'TRIGGER'   => RFC2445_REQUIRED | RFC2445_ONCE,
                        'DURATION'  => RFC2445_OPTIONAL | RFC2445_ONCE,
            'REPEAT'    => RFC2445_OPTIONAL | RFC2445_ONCE, 
                        'ATTACH'    => RFC2445_OPTIONAL,
                        'DESCRIPTION'  => RFC2445_OPTIONAL | RFC2445_ONCE,
                        'SUMMARY'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ATTENDEE'  => RFC2445_OPTIONAL,
            RFC2445_XNAME   => RFC2445_OPTIONAL
        );
     
        parent::__construct();
    }
        
    function invariant_holds() {
                if(isset($this->properties['ACTION'])) {
            switch ($this->properties['ACTION'][0]->value) {
            	case 'AUDIO':
                    if (!isset($this->properties['ATTACH'])) {
                    	return false;
                    }
                    break;
                case 'DISPLAY':
                    if (!isset($this->properties['DESCRIPTION'])) {
                    	return false;
                    }
                    break;
                case 'EMAIL':
                    if (!isset($this->properties['DESCRIPTION']) || !isset($this->properties['SUMMARY']) || !isset($this->properties['ATTACH'])) {
                        return false;
                    }
                    break;
                case 'PROCEDURE':
                    if (!isset($this->properties['ATTACH']) || count($this->properties['ATTACH']) > 1) {
                    	return false;
                    }
                    break;
            }
        }
        return true;
    }
        
        
}

class iCalendar_timezone extends iCalendar_component {
    var $name       = 'VTIMEZONE';
    var $properties;

    function __construct() {

        $this->valid_components = array('STANDARD', 'DAYLIGHT');

        $this->valid_properties = array(
            'TZID'        => RFC2445_REQUIRED | RFC2445_ONCE,
            'LAST-MODIFIED'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZURL'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
        
        parent::__construct();
    }

}

class iCalendar_standard extends iCalendar_component {
    var $name       = 'STANDARD';
    var $properties;
    
    function __construct() {
        $this->valid_components = array();
        $this->valid_properties = array(
            'DTSTART'   =>  RFC2445_REQUIRED | RFC2445_ONCE,
            'TZOFFSETTO'    =>  RFC2445_REQUIRED | RFC2445_ONCE,
            'TZOFFSETFROM'  =>  RFC2445_REQUIRED | RFC2445_ONCE,
            'COMMENT'   =>  RFC2445_OPTIONAL,
            'RDATE'   =>  RFC2445_OPTIONAL,
            'RRULE'   =>  RFC2445_OPTIONAL,
            'TZNAME'   =>  RFC2445_OPTIONAL,
            RFC2445_XNAME   =>  RFC2445_OPTIONAL,
        ); 
        parent::__construct();
    }
}

class iCalendar_daylight extends iCalendar_standard {
    var $name   =   'DAYLIGHT';
}


