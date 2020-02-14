<?php


class PHPExcel_Cell_Hyperlink
{
    
    private $url;

    
    private $tooltip;

    
    public function __construct($pUrl = '', $pTooltip = '')
    {
                $this->url     = $pUrl;
        $this->tooltip = $pTooltip;
    }

    
    public function getUrl()
    {
        return $this->url;
    }

    
    public function setUrl($value = '')
    {
        $this->url = $value;
        return $this;
    }

    
    public function getTooltip()
    {
        return $this->tooltip;
    }

    
    public function setTooltip($value = '')
    {
        $this->tooltip = $value;
        return $this;
    }

    
    public function isInternal()
    {
        return strpos($this->url, 'sheet://') !== false;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->url .
            $this->tooltip .
            __CLASS__
        );
    }
}
