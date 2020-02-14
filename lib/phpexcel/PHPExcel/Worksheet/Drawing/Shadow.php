<?php


class PHPExcel_Worksheet_Drawing_Shadow implements PHPExcel_IComparable
{
    
    const SHADOW_BOTTOM       = 'b';
    const SHADOW_BOTTOM_LEFT  = 'bl';
    const SHADOW_BOTTOM_RIGHT = 'br';
    const SHADOW_CENTER       = 'ctr';
    const SHADOW_LEFT         = 'l';
    const SHADOW_TOP          = 't';
    const SHADOW_TOP_LEFT     = 'tl';
    const SHADOW_TOP_RIGHT    = 'tr';

    
    private $visible;

    
    private $blurRadius;

    
    private $distance;

    
    private $direction;

    
    private $alignment;

    
    private $color;

    
    private $alpha;

    
    public function __construct()
    {
                $this->visible     = false;
        $this->blurRadius  = 6;
        $this->distance    = 2;
        $this->direction   = 0;
        $this->alignment   = PHPExcel_Worksheet_Drawing_Shadow::SHADOW_BOTTOM_RIGHT;
        $this->color       = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK);
        $this->alpha       = 50;
    }

    
    public function getVisible()
    {
        return $this->visible;
    }

    
    public function setVisible($pValue = false)
    {
        $this->visible = $pValue;
        return $this;
    }

    
    public function getBlurRadius()
    {
        return $this->blurRadius;
    }

    
    public function setBlurRadius($pValue = 6)
    {
        $this->blurRadius = $pValue;
        return $this;
    }

    
    public function getDistance()
    {
        return $this->distance;
    }

    
    public function setDistance($pValue = 2)
    {
        $this->distance = $pValue;
        return $this;
    }

    
    public function getDirection()
    {
        return $this->direction;
    }

    
    public function setDirection($pValue = 0)
    {
        $this->direction = $pValue;
        return $this;
    }

   
    public function getAlignment()
    {
        return $this->alignment;
    }

    
    public function setAlignment($pValue = 0)
    {
        $this->alignment = $pValue;
        return $this;
    }

   
    public function getColor()
    {
        return $this->color;
    }

    
    public function setColor(PHPExcel_Style_Color $pValue = null)
    {
           $this->color = $pValue;
           return $this;
    }

   
    public function getAlpha()
    {
        return $this->alpha;
    }

    
    public function setAlpha($pValue = 0)
    {
        $this->alpha = $pValue;
        return $this;
    }

    
    public function getHashCode()
    {
        return md5(
            ($this->visible ? 't' : 'f') .
            $this->blurRadius .
            $this->distance .
            $this->direction .
            $this->alignment .
            $this->color->getHashCode() .
            $this->alpha .
            __CLASS__
        );
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
