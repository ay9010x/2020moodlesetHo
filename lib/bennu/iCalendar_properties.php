<?php



class iCalendar_property {
    
    var $parent_component = NULL;
    var $value            = NULL;
    var $parameters       = NULL;
    var $valid_parameters = NULL;

        var $val_multi        = false;
    var $val_default      = NULL;

    function __construct() {
        $this->parameters = array();
    }

            function is_valid_parameter($parameter, $value) {

        if(is_array($value)) {
            if(!iCalendar_parameter::multiple_values_allowed($parameter)) {
                return false;
            }
            foreach($value as $item) {
                if(!iCalendar_parameter::is_valid_value($this, $parameter, $item)) {
                    return false;
                }
            }
            return true;
        }

        return iCalendar_parameter::is_valid_value($this, $parameter, $value);
    }

    function invariant_holds() {
        return true;
    }

            function is_valid_value($value) {
        if(is_array($value)) {
            if(!$this->val_multi) {
                return false;
            }
            else {
                foreach($value as $oneval) {
                    if(!rfc2445_is_valid_value($oneval, $this->val_type)) {
                        return false;
                    }
                }
            }
            return true;
        }
        return rfc2445_is_valid_value($value, $this->val_type);
    }

    function default_value() {
        return $this->val_default;
    }

    function set_parent_component($componentname) {
        if(class_exists('iCalendar_'.strtolower(substr($componentname, 1)))) {
            $this->parent_component = strtoupper($componentname);
            return true;
        }

        return false;
    }

    function set_value($value) {
        if($this->is_valid_value($value)) {
                        if(is_array($value)) {
                foreach($value as $key => $item) {
                    $value[$key] = rfc2445_do_value_formatting($item, $this->val_type);
                }
                $this->value = implode(',', $value);
            }
            else {
                $this->value = rfc2445_do_value_formatting($value, $this->val_type);
            }
            
            return true;
        }
        return false;
    }

    function get_value() {
                $valarray = explode('\\,', $this->value);

                $replace_function = create_function('$a', 'return rfc2445_undo_value_formatting($a, '.$this->val_type.');');
        $valarray = array_map($replace_function, $valarray);

                if(!$this->val_multi) {
            return $valarray[0];
        }

                return $valarray;

    }

    function set_parameter($name, $value) {

                $name = strtoupper($name);

                $xname = false;
        if(!isset($this->valid_parameters[$name])) {
                        if(!rfc2445_is_xname($name)) {
                return false;
            }
                        $xname = true;
        }

        if(!$this->is_valid_parameter($name, $value)) {
            return false;
        }

        if(is_array($value)) {
            foreach($value as $key => $element) {
                $value[$key] = iCalendar_parameter::do_value_formatting($name, $element);
            }
        }
        else {
            $value = iCalendar_parameter::do_value_formatting($name, $value);
        }

        $this->parameters[$name] = $value;

                        if($name == 'VALUE') {
                        $this->val_type = constant('RFC2445_TYPE_'.str_replace('-', '_', $value));
        }

        return true;

    }

    function get_parameter($name) {

                $name = strtoupper($name);

        if(isset($this->parameters[$name])) {
                        if(is_array($this->parameters[$name])) {
                foreach($this->parameters[$name] as $key => $value) {
                    if(substr($value, 0, 1) == '"') {
                       $this->parameters[$name][$key] = substr($value, 1, strlen($value) - 2);
                    }
                }
                return $this->parameters[$name];
            }

            else {
                if(substr($this->parameters[$name], 0, 1) == '"') {
                    return substr($this->parameters[$name], 1, strlen($this->parameters[$name]) - 2);
                }
            }
        }

        return NULL;
    }

    function serialize() {
        $string = $this->name;

        if(!empty($this->parameters)) {
            foreach($this->parameters as $name => $value) {
                $string .= ';'.$name.'=';
                if(is_array($value)) {
                    $string .= implode(',', $value);
                }
                else {
                    $string .= $value;
                }
            }
        }

        $string .= ':'.$this->value;

        return rfc2445_fold($string) . RFC2445_CRLF;
    }
}


class iCalendar_property_calscale extends iCalendar_property {

    var $name        = 'CALSCALE';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                return ($value === 'GREGORIAN');
    }
}

class iCalendar_property_method extends iCalendar_property {

    var $name        = 'METHOD';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                        $methods = array('PUBLISH', 'REQUEST', 'REPLY', 'ADD', 'CANCEL', 'REFRESH', 'COUNTER', 'DECLINECOUNTER');
        return in_array($value, $methods);
    }
}

class iCalendar_property_prodid extends iCalendar_property {

    var $name        = 'PRODID';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_default = NULL;

    function __construct() {
        parent::__construct();
        $this->val_default = '-//John Papaioannou/NONSGML Bennu '._BENNU_VERSION.'//EN';

        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_version extends iCalendar_property {

    var $name        = 'VERSION';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_default = '2.0';

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        return($value === '2.0' || $value === 2.0);
    }

}


class iCalendar_property_attach extends iCalendar_property {

    var $name        = 'ATTACH';
    var $val_type    = RFC2445_TYPE_URI;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'FMTTYPE'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ENCODING'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function invariant_holds() {
        if(isset($this->parameters['ENCODING']) && !isset($this->parameters['VALUE'])) {
            return false;
        }
        if(isset($this->parameters['VALUE']) && !isset($this->parameters['ENCODING'])) {
            return false;
        }

        return true;
    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }

        if($parameter === 'ENCODING' && strtoupper($value) != 'BASE64') {
            return false;
        }

        if($parameter === 'VALUE' && strtoupper($value) != 'BINARY') {
            return false;
        }

        return true;
    }
}

class iCalendar_property_categories extends iCalendar_property {

    var $name        = 'CATEGORIES';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_multi   = true;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_class extends iCalendar_property {

    var $name        = 'CLASS';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_default = 'PUBLIC';

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                return ($value === 'PUBLIC' || $value === 'PRIVATE' || $value === 'CONFIDENTIAL' || rfc2445_is_xname(strtoupper($value)));
    }
}

class iCalendar_property_comment extends iCalendar_property {

    var $name        = 'COMMENT';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_description extends iCalendar_property {

    var $name        = 'DESCRIPTION';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_geo extends iCalendar_property {

    var $name        = 'GEO';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                if(!is_string($value)) {
            return false;
        }

        $floats = explode(';', $value);
        if(count($floats) != 2) {
            return false;
        }

        return rfc2445_is_valid_value($floats[0], RFC2445_TYPE_FLOAT) && rfc2445_is_valid_value($floats[1], RFC2445_TYPE_FLOAT);
    }

    function set_value($value) {
                        if($this->is_valid_value($value)) {
            $this->value = $value;
            return true;
        }

        return false;
    }

}

class iCalendar_property_location extends iCalendar_property {

    var $name        = 'LOCATION';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_percent_complete extends iCalendar_property {

    var $name        = 'PERCENT-COMPLETE';
    var $val_type    = RFC2445_TYPE_INTEGER;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                if(!parent::is_valid_value($value)) {
            return false;
        }
        $value = intval($value);
        return ($value >= 0 && $value <= 100);
    }

}

class iCalendar_property_priority extends iCalendar_property {

    var $name        = 'PRIORITY';
    var $val_type    = RFC2445_TYPE_INTEGER;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                if(!parent::is_valid_value($value)) {
            return false;
        }

        $value = intval($value);
        return ($value >= 0 && $value <= 9);
    }
}

class iCalendar_property_resources extends iCalendar_property {

    var $name        = 'RESOURCES';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_multi   = true;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_status extends iCalendar_property {

    var $name        = 'STATUS';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
                switch ($this->parent_component) {
            case 'VEVENT':
                $allowed = array('TENTATIVE', 'CONFIRMED', 'CANCELLED');
            break;
            case 'VTODO':
                $allowed = array('NEEDS-ACTION', 'COMPLETED', 'IN-PROCESS', 'CANCELLED');
            break;
            case 'VJOURNAL':
                $allowed = array('DRAFT', 'FINAL', 'CANCELLED');
            break;
        }
        return in_array($value, $allowed);

    }

}

class iCalendar_property_summary extends iCalendar_property {

    var $name        = 'SUMMARY';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}


class iCalendar_property_completed extends iCalendar_property {

    var $name        = 'COMPLETED';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
                return(substr($value, -1) == 'Z');
    }
}

class iCalendar_property_dtend extends iCalendar_property {

    var $name        = 'DTEND';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }

                if($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
            return false;
        }

        return true;

    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }
}

class iCalendar_property_due extends iCalendar_property {

    var $name        = 'DUE';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }

                if($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
            return false;
        }

        return true;

    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }
}

class iCalendar_property_dtstart extends iCalendar_property {

    var $name        = 'DTSTART';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    
    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }

                if($this->parent_component == 'VFREEBUSY' && substr($value, -1) != 'Z') {
            return false;
        }

        return true;
    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }
}

class iCalendar_property_duration extends iCalendar_property {

    var $name        = 'DURATION';
    var $val_type    = RFC2445_TYPE_DURATION;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }

                return ($value{0} != '-');
    }
}

class iCalendar_property_freebusy extends iCalendar_property {

    var $name        = 'FREEBUSY';
    var $val_type    = RFC2445_TYPE_PERIOD;
    var $val_multi   = true;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'FBTYPE'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }

        $pos = strpos($value, '/');         if($value{$pos - 1} != 'Z') {
                        return false;
        }
        if($value{$pos + 1} != 'P' && substr($value, -1) != 'Z') {
                        return false;
        }

        return true;
    }

    }

class iCalendar_property_transp extends iCalendar_property {

    var $name        = 'TRANSP';
    var $val_type    = RFC2445_TYPE_TEXT;
    var $val_default = 'OPAQUE';

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        return ($value === 'TRANSPARENT' || $value === 'OPAQUE');
    }
}




class iCalendar_property_attendee extends iCalendar_property {

    var $name        = 'ATTENDEE';
    var $val_type    = RFC2445_TYPE_CAL_ADDRESS;

        
    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'LANGUAGE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CN'             => RFC2445_OPTIONAL | RFC2445_ONCE,
            'ROLE'           => RFC2445_OPTIONAL | RFC2445_ONCE,
            'PARTSTAT'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RSVP'           => RFC2445_OPTIONAL | RFC2445_ONCE,
            'CUTYPE'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'MEMBER'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DELEGATED-TO'   => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DELEGATED-FROM' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SENT-BY'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DIR'            => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME    => RFC2445_OPTIONAL
        );
    }

    function set_parent_component($componentname) {
        if(!parent::set_parent_component($componentname)) {
            return false;
        }

        if($this->parent_component == 'VFREEBUSY' || $this->parent_component == 'VALARM') {
                        $this->valid_parameters = array(
                'LANGUAGE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
                RFC2445_XNAME    => RFC2445_OPTIONAL
            );
        }

        return false;
    }

}

class iCalendar_property_contact extends iCalendar_property {

    var $name        = 'CONTACT';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'ALTREP'      => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_organizer extends iCalendar_property {

    var $name        = 'ORGANIZER';
    var $val_type    = RFC2445_TYPE_CAL_ADDRESS;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'CN'          => RFC2445_OPTIONAL | RFC2445_ONCE,
            'DIR'         => RFC2445_OPTIONAL | RFC2445_ONCE,
            'SENT-BY'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    

}

class iCalendar_property_recurrence_id extends iCalendar_property {

    

    var $name        = 'RECURRENCE-ID';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'RANGE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }

}

class iCalendar_property_related_to extends iCalendar_property {

    var $name        = 'RELATED-TO';
    var $val_type    = RFC2445_TYPE_TEXT;

    
    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'RELTYPE'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_url extends iCalendar_property {

    var $name        = 'URL';
    var $val_type    = RFC2445_TYPE_URI;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_uid extends iCalendar_property {

    var $name        = 'UID';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );

                                $this->val_default = Bennu::generate_guid();
    }
}


class iCalendar_property_exdate extends iCalendar_property {

    var $name        = 'EXDATE';
    var $val_type    = RFC2445_TYPE_DATE_TIME;
    var $val_multi   = true;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME')) {
            return false;
        }

        return true;
    }

}

class iCalendar_property_exrule extends iCalendar_property {

    var $name        = 'EXRULE';
    var $val_type    = RFC2445_TYPE_RECUR;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_rdate extends iCalendar_property {

    var $name        = 'RDATE';
    var $val_type    = RFC2445_TYPE_DATE_TIME;
    var $val_multi   = true;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'TZID'        => RFC2445_OPTIONAL | RFC2445_ONCE,
            'VALUE'       => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_parameter($parameter, $value) {

        $parameter = strtoupper($parameter);

        if(!parent::is_valid_parameter($parameter, $value)) {
            return false;
        }
        if($parameter == 'VALUE' && !($value == 'DATE' || $value == 'DATE-TIME' || $value == 'PERIOD')) {
            return false;
        }

        return true;
    }

}

class iCalendar_property_rrule extends iCalendar_property {

    var $name        = 'RRULE';
    var $val_type    = RFC2445_TYPE_RECUR;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
}

class iCalendar_property_action extends iCalendar_property {
	var $name      = 'ACTION';
    var $val_type   = RFC2445_TYPE_TEXT;
    
    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
    
    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
        
                $valid_values = array('ACTION', 'DISPLAY', 'EMAIL', 'PROCEDURE');
        return(in_array($value, $valid_values) || rfc2445_is_xname($value));        
        
    }
}

class iCalendar_property_repeat extends iCalendar_property {
    var $name      = 'REPEAT';
    var $val_type   = RFC2445_TYPE_INTEGER;
    
    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }   
}

class iCalendar_property_trigger extends iCalendar_property {
    var $name      = 'TRIGGER';
    var $val_type   = RFC2445_TYPE_TEXT;
    
    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'VALUE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            'RELATED' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }
    
    function is_valid_value($value) {        
    	if(!parent::is_valid_value($value)) {
            return false;
        }
                return(rfc2445_is_valid_value($value, RFC2445_TYPE_DURATION) 
            || rfc2445_is_valid_value($value, RFC2445_TYPE_DATE_TIME));
    }
}




class iCalendar_property_created extends iCalendar_property {

    var $name        = 'CREATED';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
                return(substr($value, -1) == 'Z');
    }
}

class iCalendar_property_dtstamp extends iCalendar_property {

    var $name        = 'DTSTAMP';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
                return(substr($value, -1) == 'Z');
    }
}

class iCalendar_property_last_modified extends iCalendar_property {

    var $name        = 'LAST-MODIFIED';
    var $val_type    = RFC2445_TYPE_DATE_TIME;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
                return(substr($value, -1) == 'Z');
    }
}

class iCalendar_property_sequence extends iCalendar_property {

    var $name        = 'SEQUENCE';
    var $val_type    = RFC2445_TYPE_INTEGER;
    var $val_default = 0;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        }
        $value = intval($value);
        return ($value >= 0);
    }
}


class iCalendar_property_x extends iCalendar_property {

    var $name        = RFC2445_XNAME;
    var $val_type    = NULL;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
                        'FMTTYPE'     => RFC2445_OPTIONAL | RFC2445_ONCE,
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function set_name($name) {

        $name = strtoupper($name);

        if(rfc2445_is_xname($name)) {
            $this->name = $name;
            return true;
        }

        return false;
    }
}

class iCalendar_property_request_status extends iCalendar_property {

                
    var $name        = 'REQUEST-STATUS';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'LANGUAGE'    => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!is_string($value) || empty($value)) {
            return false;
        }

        $len   = strlen($value);
        $parts = array();
        $from  = 0;
        $escch = false;

        for($i = 0; $i < $len; ++$i) {
            if($value{$i} == ';' && !$escch) {
                                $parts[] = substr($value, $from, $i - $from);
                $from = $i + 1;
                continue;
            }
            $escch = ($value{$i} == '\\');
        }
                                $parts[] = substr($value, $from);

        $count = count($parts);

                if($count != 2 && $count != 3) {
            return false;
        }

        
                if(strlen($parts[0]) == 0) {
            return false;
        }

        if($parts[0]{0} < '1' || $parts[0]{0} > '4') {
            return false;
        }

        $len = strlen($parts[0]);

                if($len > 5 || $parts[0]{$len - 1} == '.') {
            return false;
        }

        for($i = 1; $i < $len; ++$i) {
            if(($i & 1) == 1 && $parts[0]{$i} != '.') {
                                return false;
            }
            else if(($i & 1) == 0 && ($parts[0]{$i} < '0' || $parts[0]{$i} > '9')) {
                                return false;
            }
        }

                        
        for($i = 1; $i < $count; ++$i) {
            if(strpos($parts[$i], "\n") !== false) {
                return false;
            }

            $len = strlen($parts[$i]);
            if($len == 0) {
                                return false;
            }

            $parts[$i] .= '#'; 
            for($j = 0; $j < $len; ++$j) {
                $thischar = $parts[$i]{$j};
                $nextchar = $parts[$i]{$j + 1};
                if($thischar == '\\') {
                                        if($nextchar != ';' && $nextchar != ',' && $nextchar != '\\' &&
                       $nextchar != 'n' && $nextchar != 'N') {
                        return false;
                    }

                                        ++$j;
                    continue;
                }
                if($thischar == ';' || $thischar == ',' || $thischar == '\\') {
                                        return false;
                }
            }
        }

        return true;
    }

    function set_value($value) {
                if($this->is_valid_value($value)) {
            $this->value = $value;
            return true;
        }

        return false;
    }

}

class iCalendar_property_tzid extends iCalendar_property {

    var $name        = 'TZID';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        } else {
            return true;
        }
    }
}

class iCalendar_property_tzname extends iCalendar_property {

    var $name        = 'TZNAME';
    var $val_type    = RFC2445_TYPE_TEXT;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            'LANGUAGE' => RFC2445_OPTIONAL | RFC2445_ONCE,
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        } else {
            return true;
        }
    }
}

class iCalendar_property_tzoffsetfrom extends iCalendar_property {

    var $name        = 'TZOFFSETFROM';
    var $val_type    = RFC2445_TYPE_UTC_OFFSET;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        } else {
            return true;
        }
    }
}

class iCalendar_property_tzoffsetto extends iCalendar_property {

    var $name        = 'TZOFFSETTO';
    var $val_type    = RFC2445_TYPE_UTC_OFFSET;

    function __construct() {
        parent::__construct();
        $this->valid_parameters = array(
            RFC2445_XNAME => RFC2445_OPTIONAL
        );
    }

    function is_valid_value($value) {
        if(!parent::is_valid_value($value)) {
            return false;
        } else {
        	return true;
        }
    }
}





