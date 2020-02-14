<?php




class Minify_Cache_ZendPlatform {


    
    public function __construct($expire = 0)
    {
        $this->_exp = $expire;
    }


    
    public function store($id, $data)
    {
        return output_cache_put($id, "{$_SERVER['REQUEST_TIME']}|{$data}");
    }


    
    public function getSize($id)
    {
        return $this->_fetch($id)
            ? strlen($this->_data)
            : false;
    }


    
    public function isValid($id, $srcMtime)
    {
        $ret = ($this->_fetch($id) && ($this->_lm >= $srcMtime));
        return $ret;
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
        $ret = output_cache_get($id, $this->_exp);
        if (false === $ret) {
            $this->_id = null;
            return false;
        }
        list($this->_lm, $this->_data) = explode('|', $ret, 2);
        $this->_id = $id;
        return true;
    }
}
