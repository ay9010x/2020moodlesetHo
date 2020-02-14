<?php



class Minify_Cache_WinCache
{
    
    
    public function __construct($expire = 0)
    {
        if (!function_exists('wincache_ucache_info')) {
            throw new Exception("WinCache for PHP is not installed to be able to use Minify_Cache_WinCache!");
        }
        $this->_exp = $expire;
    }
    
    
    public function store($id, $data)
    {
        return wincache_ucache_set($id, "{$_SERVER['REQUEST_TIME']}|{$data}", $this->_exp);
    }
    
    
    public function getSize($id)
    {
        if (!$this->_fetch($id)) {
            return false;
        }
        return (function_exists('mb_strlen') && ((int) ini_get('mbstring.func_overload') & 2)) ? mb_strlen($this->_data, '8bit') : strlen($this->_data);
    }
    
    
    public function isValid($id, $srcMtime)
    {
        return ($this->_fetch($id) && ($this->_lm >= $srcMtime));
    }
    
    
    public function display($id)
    {
        echo $this->_fetch($id) ? $this->_data : '';
    }
    
    
    public function fetch($id)
    {
        return $this->_fetch($id) ? $this->_data : '';
    }
    
    private $_exp = NULL;
    
        private $_lm = NULL;
    private $_data = NULL;
    private $_id = NULL;
    
    
    private function _fetch($id)
    {
        if ($this->_id === $id) {
            return true;
        }
        $suc = false;
        $ret = wincache_ucache_get($id, $suc);
        if (!$suc) {
            $this->_id = NULL;
            return false;
        }
        list($this->_lm, $this->_data) = explode('|', $ret, 2);
        $this->_id = $id;
        return true;
    }
}