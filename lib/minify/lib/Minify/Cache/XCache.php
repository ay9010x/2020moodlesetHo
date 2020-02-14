<?php



class Minify_Cache_XCache {

    
    public function __construct($expire = 0)
    {
        $this->_exp = $expire;
    }

    
    public function store($id, $data)
    {
        return xcache_set($id, "{$_SERVER['REQUEST_TIME']}|{$data}", $this->_exp);
    }

    
    public function getSize($id)
    {
        if (! $this->_fetch($id)) {
            return false;
        }
        return (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2))
            ? mb_strlen($this->_data, '8bit')
            : strlen($this->_data);
    }

    
    public function isValid($id, $srcMtime)
    {
        return ($this->_fetch($id) && ($this->_lm >= $srcMtime));
    }

    
    public function display($id)
    {
        echo $this->_fetch($id)
            ? $this->_data
            : '';
    }

    
    public function fetch($id)
    {
        return $this->_fetch($id)
            ? $this->_data
            : '';
    }

    private $_exp = null;

        private $_lm = null;
    private $_data = null;
    private $_id = null;

    
    private function _fetch($id)
    {
        if ($this->_id === $id) {
            return true;
        }
        $ret = xcache_get($id);
        if (false === $ret) {
            $this->_id = null;
            return false;
        }
        list($this->_lm, $this->_data) = explode('|', $ret, 2);
        $this->_id = $id;
        return true;
    }
}
