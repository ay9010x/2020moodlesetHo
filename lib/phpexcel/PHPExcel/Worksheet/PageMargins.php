<?php




class PHPExcel_Worksheet_PageMargins
{
    
    private $left        = 0.7;

    
    private $right        = 0.7;

    
    private $top        = 0.75;

    
    private $bottom    = 0.75;

    
    private $header     = 0.3;

    
    private $footer     = 0.3;

    
    public function __construct()
    {
    }

    
    public function getLeft()
    {
        return $this->left;
    }

    
    public function setLeft($pValue)
    {
        $this->left = $pValue;
        return $this;
    }

    
    public function getRight()
    {
        return $this->right;
    }

    
    public function setRight($pValue)
    {
        $this->right = $pValue;
        return $this;
    }

    
    public function getTop()
    {
        return $this->top;
    }

    
    public function setTop($pValue)
    {
        $this->top = $pValue;
        return $this;
    }

    
    public function getBottom()
    {
        return $this->bottom;
    }

    
    public function setBottom($pValue)
    {
        $this->bottom = $pValue;
        return $this;
    }

    
    public function getHeader()
    {
        return $this->header;
    }

    
    public function setHeader($pValue)
    {
        $this->header = $pValue;
        return $this;
    }

    
    public function getFooter()
    {
        return $this->footer;
    }

    
    public function setFooter($pValue)
    {
        $this->footer = $pValue;
        return $this;
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
