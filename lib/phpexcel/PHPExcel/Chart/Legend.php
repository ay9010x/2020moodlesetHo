<?php


class PHPExcel_Chart_Legend
{
    
    const xlLegendPositionBottom = -4107;        const xlLegendPositionCorner = 2;            const xlLegendPositionCustom = -4161;        const xlLegendPositionLeft   = -4131;        const xlLegendPositionRight  = -4152;        const xlLegendPositionTop    = -4160;    
    const POSITION_RIGHT    = 'r';
    const POSITION_LEFT     = 'l';
    const POSITION_BOTTOM   = 'b';
    const POSITION_TOP      = 't';
    const POSITION_TOPRIGHT = 'tr';

    private static $positionXLref = array(
        self::xlLegendPositionBottom => self::POSITION_BOTTOM,
        self::xlLegendPositionCorner => self::POSITION_TOPRIGHT,
        self::xlLegendPositionCustom => '??',
        self::xlLegendPositionLeft   => self::POSITION_LEFT,
        self::xlLegendPositionRight  => self::POSITION_RIGHT,
        self::xlLegendPositionTop    => self::POSITION_TOP
    );

    
    private $position = self::POSITION_RIGHT;

    
    private $overlay = true;

    
    private $layout = null;


    
    public function __construct($position = self::POSITION_RIGHT, PHPExcel_Chart_Layout $layout = null, $overlay = false)
    {
        $this->setPosition($position);
        $this->layout = $layout;
        $this->setOverlay($overlay);
    }

    
    public function getPosition()
    {
        return $this->position;
    }

    
    public function setPosition($position = self::POSITION_RIGHT)
    {
        if (!in_array($position, self::$positionXLref)) {
            return false;
        }

        $this->position = $position;
        return true;
    }

    
    public function getPositionXL()
    {
        return array_search($this->position, self::$positionXLref);
    }

    
    public function setPositionXL($positionXL = self::xlLegendPositionRight)
    {
        if (!array_key_exists($positionXL, self::$positionXLref)) {
            return false;
        }

        $this->position = self::$positionXLref[$positionXL];
        return true;
    }

    
    public function getOverlay()
    {
        return $this->overlay;
    }

    
    public function setOverlay($overlay = false)
    {
        if (!is_bool($overlay)) {
            return false;
        }

        $this->overlay = $overlay;
        return true;
    }

    
    public function getLayout()
    {
        return $this->layout;
    }
}
