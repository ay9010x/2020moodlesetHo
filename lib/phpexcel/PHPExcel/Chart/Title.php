<?php


class PHPExcel_Chart_Title
{

    
    private $caption = null;

    
    private $layout = null;

    
    public function __construct($caption = null, PHPExcel_Chart_Layout $layout = null)
    {
        $this->caption = $caption;
        $this->layout = $layout;
    }

    
    public function getCaption()
    {
        return $this->caption;
    }

    
    public function setCaption($caption = null)
    {
        $this->caption = $caption;
        
        return $this;
    }

    
    public function getLayout()
    {
        return $this->layout;
    }
}
