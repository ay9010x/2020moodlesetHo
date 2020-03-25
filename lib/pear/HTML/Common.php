<?php





class HTML_Common {

    
    var $_attributes = array();

    
    var $_tabOffset = 0;

    
    var $_tab = "\11";

    
    var $_lineEnd = "\12";

    
    var $_comment = '';

    
    public function __construct($attributes = null, $tabOffset = 0)
    {
        $this->setAttributes($attributes);
        $this->setTabOffset($tabOffset);
    } 
    
    public function HTML_Common($attributes = null, $tabOffset = 0) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($attributes, $tabOffset);
    }

    public static function raiseError($message = null,
                                       $code = null,
                                       $mode = null,
                                       $options = null,
                                       $userinfo = null,
                                       $error_class = null,
                                       $skipmsg = false) {
        $pear = new PEAR();
        return $pear->raiseError($message, $code, $mode, $options, $userinfo, $error_class, $skipmsg);
    }

    
    function apiVersion()
    {
        return 1.7;
    } 
    
    function _getLineEnd()
    {
        return $this->_lineEnd;
    } 
    
    function _getTab()
    {
        return $this->_tab;
    } 
    
    function _getTabs()
    {
        return str_repeat($this->_getTab(), $this->_tabOffset);
    } 
    
    function _getAttrString($attributes)
    {
        $strAttr = '';

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $strAttr .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        return $strAttr;
    } 
    
    function _parseAttributes($attributes)
    {
        if (is_array($attributes)) {
            $ret = array();
            foreach ($attributes as $key => $value) {
                if (is_int($key)) {
                    $key = $value = strtolower($value);
                } else {
                    $key = strtolower($key);
                }
                $ret[$key] = $value;
            }
            return $ret;

        } elseif (is_string($attributes)) {
            $preg = "/(([A-Za-z_:]|[^\\x00-\\x7F])([A-Za-z0-9_:.-]|[^\\x00-\\x7F])*)" .
                "([ \\n\\t\\r]+)?(=([ \\n\\t\\r]+)?(\"[^\"]*\"|'[^']*'|[^ \\n\\t\\r]*))?/";
            if (preg_match_all($preg, $attributes, $regs)) {
                for ($counter=0; $counter<count($regs[1]); $counter++) {
                    $name  = $regs[1][$counter];
                    $check = $regs[0][$counter];
                    $value = $regs[7][$counter];
                    if (trim($name) == trim($check)) {
                        $arrAttr[strtolower(trim($name))] = strtolower(trim($name));
                    } else {
                        if (substr($value, 0, 1) == "\"" || substr($value, 0, 1) == "'") {
                            $value = substr($value, 1, -1);
                        }
                        $arrAttr[strtolower(trim($name))] = trim($value);
                    }
                }
                return $arrAttr;
            }
        }
    } 
    
    function _getAttrKey($attr, $attributes)
    {
        if (isset($attributes[strtolower($attr)])) {
            return true;
        } else {
            return null;
        }
    } 
    
    function _updateAttrArray(&$attr1, $attr2)
    {
        if (!is_array($attr2)) {
            return false;
        }
        foreach ($attr2 as $key => $value) {
            $attr1[$key] = $value;
        }
    } 
    
    function _removeAttr($attr, &$attributes)
    {
        $attr = strtolower($attr);
        if (isset($attributes[$attr])) {
            unset($attributes[$attr]);
        }
    } 
    
    function getAttribute($attr)
    {
        $attr = strtolower($attr);
        if (isset($this->_attributes[$attr])) {
            return $this->_attributes[$attr];
        }
        return null;
    } 
    
    function setAttributes($attributes)
    {
        $this->_attributes = $this->_parseAttributes($attributes);
    } 
    
    function getAttributes($asString = false)
    {
        if ($asString) {
            return $this->_getAttrString($this->_attributes);
        } else {
            return $this->_attributes;
        }
    } 
    
    function updateAttributes($attributes)
    {
        $this->_updateAttrArray($this->_attributes, $this->_parseAttributes($attributes));
    } 
    
    function removeAttribute($attr)
    {
        $this->_removeAttr($attr, $this->_attributes);
    } 
    
    function setLineEnd($style)
    {
        switch ($style) {
            case 'win':
                $this->_lineEnd = "\15\12";
                break;
            case 'unix':
                $this->_lineEnd = "\12";
                break;
            case 'mac':
                $this->_lineEnd = "\15";
                break;
            default:
                $this->_lineEnd = $style;
        }
    } 
    
    function setTabOffset($offset)
    {
        $this->_tabOffset = $offset;
    } 
    
    function getTabOffset()
    {
        return $this->_tabOffset;
    } 
    
    function setTab($string)
    {
        $this->_tab = $string;
    } 
    
    function setComment($comment)
    {
        $this->_comment = $comment;
    } 
    
    function getComment()
    {
        return $this->_comment;
    } 
    
    function toHtml()
    {
        return '';
    } 
    
    function display()
    {
        print $this->toHtml();
    } 
} ?>
