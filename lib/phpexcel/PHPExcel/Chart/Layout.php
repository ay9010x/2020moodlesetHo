<?php




class PHPExcel_Chart_Layout
{
    
    private $layoutTarget;

    
    private $xMode;

    
    private $yMode;

    
    private $xPos;

    
    private $yPos;

    
    private $width;

    
    private $height;

    
    private $showLegendKey;

    
    private $showVal;

    
    private $showCatName;

    
    private $showSerName;

    
    private $showPercent;

    
    private $showBubbleSize;

    
    private $showLeaderLines;


    
    public function __construct($layout = array())
    {
        if (isset($layout['layoutTarget'])) {
            $this->layoutTarget = $layout['layoutTarget'];
        }
        if (isset($layout['xMode'])) {
            $this->xMode = $layout['xMode'];
        }
        if (isset($layout['yMode'])) {
            $this->yMode = $layout['yMode'];
        }
        if (isset($layout['x'])) {
            $this->xPos = (float) $layout['x'];
        }
        if (isset($layout['y'])) {
            $this->yPos = (float) $layout['y'];
        }
        if (isset($layout['w'])) {
            $this->width = (float) $layout['w'];
        }
        if (isset($layout['h'])) {
            $this->height = (float) $layout['h'];
        }
    }

    
    public function getLayoutTarget()
    {
        return $this->layoutTarget;
    }

    
    public function setLayoutTarget($value)
    {
        $this->layoutTarget = $value;
        return $this;
    }

    
    public function getXMode()
    {
        return $this->xMode;
    }

    
    public function setXMode($value)
    {
        $this->xMode = $value;
        return $this;
    }

    
    public function getYMode()
    {
        return $this->yMode;
    }

    
    public function setYMode($value)
    {
        $this->yMode = $value;
        return $this;
    }

    
    public function getXPosition()
    {
        return $this->xPos;
    }

    
    public function setXPosition($value)
    {
        $this->xPos = $value;
        return $this;
    }

    
    public function getYPosition()
    {
        return $this->yPos;
    }

    
    public function setYPosition($value)
    {
        $this->yPos = $value;
        return $this;
    }

    
    public function getWidth()
    {
        return $this->width;
    }

    
    public function setWidth($value)
    {
        $this->width = $value;
        return $this;
    }

    
    public function getHeight()
    {
        return $this->height;
    }

    
    public function setHeight($value)
    {
        $this->height = $value;
        return $this;
    }


    
    public function getShowLegendKey()
    {
        return $this->showLegendKey;
    }

    
    public function setShowLegendKey($value)
    {
        $this->showLegendKey = $value;
        return $this;
    }

    
    public function getShowVal()
    {
        return $this->showVal;
    }

    
    public function setShowVal($value)
    {
        $this->showVal = $value;
        return $this;
    }

    
    public function getShowCatName()
    {
        return $this->showCatName;
    }

    
    public function setShowCatName($value)
    {
        $this->showCatName = $value;
        return $this;
    }

    
    public function getShowSerName()
    {
        return $this->showSerName;
    }

    
    public function setShowSerName($value)
    {
        $this->showSerName = $value;
        return $this;
    }

    
    public function getShowPercent()
    {
        return $this->showPercent;
    }

    
    public function setShowPercent($value)
    {
        $this->showPercent = $value;
        return $this;
    }

    
    public function getShowBubbleSize()
    {
        return $this->showBubbleSize;
    }

    
    public function setShowBubbleSize($value)
    {
        $this->showBubbleSize = $value;
        return $this;
    }

    
    public function getShowLeaderLines()
    {
        return $this->showLeaderLines;
    }

    
    public function setShowLeaderLines($value)
    {
        $this->showLeaderLines = $value;
        return $this;
    }
}
