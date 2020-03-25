<?php


class PHPExcel_Worksheet_SheetView
{

    
    const SHEETVIEW_NORMAL             = 'normal';
    const SHEETVIEW_PAGE_LAYOUT        = 'pageLayout';
    const SHEETVIEW_PAGE_BREAK_PREVIEW = 'pageBreakPreview';

    private static $sheetViewTypes = array(
        self::SHEETVIEW_NORMAL,
        self::SHEETVIEW_PAGE_LAYOUT,
        self::SHEETVIEW_PAGE_BREAK_PREVIEW,
    );

    
    private $zoomScale = 100;

    
    private $zoomScaleNormal = 100;

    
    private $sheetviewType = self::SHEETVIEW_NORMAL;

    
    public function __construct()
    {
    }

    
    public function getZoomScale()
    {
        return $this->zoomScale;
    }

    
    public function setZoomScale($pValue = 100)
    {
                        if (($pValue >= 1) || is_null($pValue)) {
            $this->zoomScale = $pValue;
        } else {
            throw new PHPExcel_Exception("Scale must be greater than or equal to 1.");
        }
        return $this;
    }

    
    public function getZoomScaleNormal()
    {
        return $this->zoomScaleNormal;
    }

    
    public function setZoomScaleNormal($pValue = 100)
    {
        if (($pValue >= 1) || is_null($pValue)) {
            $this->zoomScaleNormal = $pValue;
        } else {
            throw new PHPExcel_Exception("Scale must be greater than or equal to 1.");
        }
        return $this;
    }

    
    public function getView()
    {
        return $this->sheetviewType;
    }

    
    public function setView($pValue = null)
    {
                if ($pValue === null) {
            $pValue = self::SHEETVIEW_NORMAL;
        }
        if (in_array($pValue, self::$sheetViewTypes)) {
            $this->sheetviewType = $pValue;
        } else {
            throw new PHPExcel_Exception("Invalid sheetview layout type.");
        }

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
