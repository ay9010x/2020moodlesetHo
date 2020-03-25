<?php




defined('MOODLE_INTERNAL') || die();


require_once $CFG->dirroot.'/lib/evalmath/evalmath.class.php';


class calc_formula {

        var $_em;
    var $_nfx   = false;       var $_error = false; 
    
    public function __construct($formula, $params=false) {
        $this->_em = new EvalMath();
        $this->_em->suppress_errors = true;         if (strpos($formula, '=') !== 0) {
            $this->_error = "missing leading '='";
            return;
        }
        $formula = substr($formula, 1);
        if (strpos($formula, '=') !== false) {
            $this->_error = "too many '='";
            return;
        }
        $this->_nfx = $this->_em->nfx($formula);
        if ($this->_nfx == false) {
            $this->_error = $this->_em->last_error;
            return;
        }
        if ($params != false) {
            $this->set_params($params);
        }
    }

    
    public function calc_formula($formula, $params=false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($formula, $params);
    }

    
    function set_params($params) {
        $this->_em->v = $params;
    }

    
    function evaluate() {
        if ($this->_nfx == false) {
            return false;
        }
        $res = $this->_em->pfx($this->_nfx);
        if ($res === false) {
            $this->_error = $this->_em->last_error;
            return false;
        } else {
            $this->_error = false;
            return $res;
        }
    }

    
    function get_error() {
        return $this->_error;
    }

    
    public static function localize($formula) {
        $formula = str_replace('.', '$', $formula);         $formula = str_replace(',', get_string('listsep', 'langconfig'), $formula);
        $formula = str_replace('$', get_string('decsep', 'langconfig'), $formula);
        return $formula;
    }

    
    public static function unlocalize($formula) {
        $formula = str_replace(get_string('decsep', 'langconfig'), '$', $formula);
        $formula = str_replace(get_string('listsep', 'langconfig'), ',', $formula);
        $formula = str_replace('$', '.', $formula);         return $formula;
    }
}
