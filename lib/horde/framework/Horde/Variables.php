<?php



class Horde_Variables implements ArrayAccess, Countable, IteratorAggregate
{
    
    protected $_expected = array();

    
    protected $_sanitized = false;

    
    protected $_vars;

    
    static public function getDefaultVariables($sanitize = false)
    {
        return new self(null, $sanitize);
    }

    
    public function __construct($vars = array(), $sanitize = false)
    {
        if (is_null($vars)) {
            $request_copy = $_REQUEST;
            $vars = Horde_Util::dispelMagicQuotes($request_copy);
        }

        if (isset($vars['_formvars'])) {
            $this->_expected = @json_decode($vars['_formvars'], true);
            unset($vars['_formvars']);
        }

        $this->_vars = $vars;

        if ($sanitize) {
            $this->sanitize();
        }
    }

    
    public function sanitize()
    {
        if (!$this->_sanitized) {
            foreach (array_keys($this->_vars) as $key) {
                $this->$key = $this->filter($key);
            }
            $this->_sanitized = true;
        }
    }

    
    public function exists($varname)
    {
        return isset($this->$varname);
    }

    
    public function __isset($varname)
    {
        return count($this->_expected)
            ? $this->_getExists($this->_expected, $varname, $value)
            : $this->_getExists($this->_vars, $varname, $value);
    }

    
    public function offsetExists($field)
    {
        return $this->__isset($field);
    }

    
    public function get($varname, $default = null)
    {
        return $this->_getExists($this->_vars, $varname, $value)
            ? $value
            : $default;
    }

    
    public function __get($varname)
    {
        $this->_getExists($this->_vars, $varname, $value);
        return $value;
    }

    
    public function offsetGet($field)
    {
        return $this->__get($field);
    }

    
    public function getExists($varname, &$exists)
    {
        $exists = $this->_getExists($this->_vars, $varname, $value);
        return $value;
    }

    
    public function set($varname, $value)
    {
        $this->$varname = $value;
    }

    
    public function __set($varname, $value)
    {
        $keys = array();

        if (Horde_Array::getArrayParts($varname, $base, $keys)) {
            array_unshift($keys, $base);
            $place = &$this->_vars;
            $i = count($keys);

            while ($i--) {
                $key = array_shift($keys);
                if (!isset($place[$key])) {
                    $place[$key] = array();
                }
                $place = &$place[$key];
            }

            $place = $value;
        } else {
            $this->_vars[$varname] = $value;
        }
    }

    
    public function offsetSet($field, $value)
    {
        $this->__set($field, $value);
    }

    
    public function remove($varname)
    {
        unset($this->$varname);
    }

    
    public function __unset($varname)
    {
        Horde_Array::getArrayParts($varname, $base, $keys);

        if (is_null($base)) {
            unset($this->_vars[$varname]);
        } else {
            $ptr = &$this->_vars[$base];
            $end = count($keys) - 1;
            foreach ($keys as $key => $val) {
                if (!isset($ptr[$val])) {
                    break;
                }
                if ($end == $key) {
                    array_splice($ptr, array_search($val, array_keys($ptr)), 1);
                } else {
                    $ptr = &$ptr[$val];
                }
            }
        }
    }

    
    public function offsetUnset($field)
    {
        $this->__unset($field);
    }

    
    public function merge($vars)
    {
        foreach ($vars as $varname => $value) {
            $this->$varname = $value;
        }
    }

    
    public function add($varname, $value)
    {
        if ($this->exists($varname)) {
            return false;
        }

        $this->_vars[$varname] = $value;
        return true;
    }

    
    public function filter($varname)
    {
        $val = $this->$varname;

        if (is_null($val) || $this->_sanitized) {
            return $val;
        }

        return is_array($val)
            ? filter_var_array($val, FILTER_SANITIZE_STRING)
            : filter_var($val, FILTER_SANITIZE_STRING);
    }

    

    
    protected function _getExists($array, $varname, &$value)
    {
        if (Horde_Array::getArrayParts($varname, $base, $keys)) {
            if (!isset($array[$base])) {
                $value = null;
                return false;
            }

            $searchspace = &$array[$base];
            $i = count($keys);

            while ($i--) {
                $key = array_shift($keys);
                if (!isset($searchspace[$key])) {
                    $value = null;
                    return false;
                }
                $searchspace = &$searchspace[$key];
            }
            $value = $searchspace;

            return true;
        }

        $value = isset($array[$varname])
            ? $array[$varname]
            : null;

        return !is_null($value);
    }

    

    
    public function count()
    {
        return count($this->_vars);
    }

    

    public function getIterator()
    {
        return new ArrayIterator($this->_vars);
    }

}
