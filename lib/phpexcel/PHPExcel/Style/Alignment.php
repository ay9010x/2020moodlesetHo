<?php

class PHPExcel_Style_Alignment extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const HORIZONTAL_GENERAL           = 'general';
    const HORIZONTAL_LEFT              = 'left';
    const HORIZONTAL_RIGHT             = 'right';
    const HORIZONTAL_CENTER            = 'center';
    const HORIZONTAL_CENTER_CONTINUOUS = 'centerContinuous';
    const HORIZONTAL_JUSTIFY           = 'justify';
    const HORIZONTAL_FILL              = 'fill';
    const HORIZONTAL_DISTRIBUTED       = 'distributed';        
    
    const VERTICAL_BOTTOM      = 'bottom';
    const VERTICAL_TOP         = 'top';
    const VERTICAL_CENTER      = 'center';
    const VERTICAL_JUSTIFY     = 'justify';
    const VERTICAL_DISTRIBUTED = 'distributed';        
    
    const READORDER_CONTEXT = 0;
    const READORDER_LTR     = 1;
    const READORDER_RTL     = 2;

    
    protected $horizontal = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;

    
    protected $vertical = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;

    
    protected $textRotation = 0;

    
    protected $wrapText = false;

    
    protected $shrinkToFit = false;

    
    protected $indent = 0;

    
    protected $readorder = 0;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

        if ($isConditional) {
            $this->horizontal   = null;
            $this->vertical     = null;
            $this->textRotation = null;
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getAlignment();
    }

    
    public function getStyleArray($array)
    {
        return array('alignment' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())
                    ->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (isset($pStyles['horizontal'])) {
                    $this->setHorizontal($pStyles['horizontal']);
                }
                if (isset($pStyles['vertical'])) {
                    $this->setVertical($pStyles['vertical']);
                }
                if (isset($pStyles['rotation'])) {
                    $this->setTextRotation($pStyles['rotation']);
                }
                if (isset($pStyles['wrap'])) {
                    $this->setWrapText($pStyles['wrap']);
                }
                if (isset($pStyles['shrinkToFit'])) {
                    $this->setShrinkToFit($pStyles['shrinkToFit']);
                }
                if (isset($pStyles['indent'])) {
                    $this->setIndent($pStyles['indent']);
                }
                if (isset($pStyles['readorder'])) {
                    $this->setReadorder($pStyles['readorder']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getHorizontal()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHorizontal();
        }
        return $this->horizontal;
    }

    
    public function setHorizontal($pValue = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL)
    {
        if ($pValue == '') {
            $pValue = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
        }

        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('horizontal' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->horizontal = $pValue;
        }
        return $this;
    }

    
    public function getVertical()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getVertical();
        }
        return $this->vertical;
    }

    
    public function setVertical($pValue = PHPExcel_Style_Alignment::VERTICAL_BOTTOM)
    {
        if ($pValue == '') {
            $pValue = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
        }

        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('vertical' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->vertical = $pValue;
        }
        return $this;
    }

    
    public function getTextRotation()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getTextRotation();
        }
        return $this->textRotation;
    }

    
    public function setTextRotation($pValue = 0)
    {
                if ($pValue == 255) {
            $pValue = -165;
        }

                if (($pValue >= -90 && $pValue <= 90) || $pValue == -165) {
            if ($this->isSupervisor) {
                $styleArray = $this->getStyleArray(array('rotation' => $pValue));
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
            } else {
                $this->textRotation = $pValue;
            }
        } else {
            throw new PHPExcel_Exception("Text rotation should be a value between -90 and 90.");
        }

        return $this;
    }

    
    public function getWrapText()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getWrapText();
        }
        return $this->wrapText;
    }

    
    public function setWrapText($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('wrap' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->wrapText = $pValue;
        }
        return $this;
    }

    
    public function getShrinkToFit()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getShrinkToFit();
        }
        return $this->shrinkToFit;
    }

    
    public function setShrinkToFit($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('shrinkToFit' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->shrinkToFit = $pValue;
        }
        return $this;
    }

    
    public function getIndent()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getIndent();
        }
        return $this->indent;
    }

    
    public function setIndent($pValue = 0)
    {
        if ($pValue > 0) {
            if ($this->getHorizontal() != self::HORIZONTAL_GENERAL &&
                $this->getHorizontal() != self::HORIZONTAL_LEFT &&
                $this->getHorizontal() != self::HORIZONTAL_RIGHT) {
                $pValue = 0;             }
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('indent' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->indent = $pValue;
        }
        return $this;
    }

    
    public function getReadorder()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getReadorder();
        }
        return $this->readorder;
    }

    
    public function setReadorder($pValue = 0)
    {
        if ($pValue < 0 || $pValue > 2) {
            $pValue = 0;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('readorder' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->readorder = $pValue;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->horizontal .
            $this->vertical .
            $this->textRotation .
            ($this->wrapText ? 't' : 'f') .
            ($this->shrinkToFit ? 't' : 'f') .
            $this->indent .
            $this->readorder .
            __CLASS__
        );
    }
}
