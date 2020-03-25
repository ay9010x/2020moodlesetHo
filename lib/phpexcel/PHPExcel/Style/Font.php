<?php


class PHPExcel_Style_Font extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const UNDERLINE_NONE             = 'none';
    const UNDERLINE_DOUBLE           = 'double';
    const UNDERLINE_DOUBLEACCOUNTING = 'doubleAccounting';
    const UNDERLINE_SINGLE           = 'single';
    const UNDERLINE_SINGLEACCOUNTING = 'singleAccounting';

    
    protected $name = 'Calibri';

    
    protected $size = 11;

    
    protected $bold = false;

    
    protected $italic = false;

    
    protected $superScript = false;

    
    protected $subScript = false;

    
    protected $underline = self::UNDERLINE_NONE;

    
    protected $strikethrough = false;

    
    protected $color;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                if ($isConditional) {
            $this->name = null;
            $this->size = null;
            $this->bold = null;
            $this->italic = null;
            $this->superScript = null;
            $this->subScript = null;
            $this->underline = null;
            $this->strikethrough = null;
            $this->color = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK, $isSupervisor, $isConditional);
        } else {
            $this->color = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK, $isSupervisor);
        }
                if ($isSupervisor) {
            $this->color->bindParent($this, 'color');
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getFont();
    }

    
    public function getStyleArray($array)
    {
        return array('font' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (array_key_exists('name', $pStyles)) {
                    $this->setName($pStyles['name']);
                }
                if (array_key_exists('bold', $pStyles)) {
                    $this->setBold($pStyles['bold']);
                }
                if (array_key_exists('italic', $pStyles)) {
                    $this->setItalic($pStyles['italic']);
                }
                if (array_key_exists('superScript', $pStyles)) {
                    $this->setSuperScript($pStyles['superScript']);
                }
                if (array_key_exists('subScript', $pStyles)) {
                    $this->setSubScript($pStyles['subScript']);
                }
                if (array_key_exists('underline', $pStyles)) {
                    $this->setUnderline($pStyles['underline']);
                }
                if (array_key_exists('strike', $pStyles)) {
                    $this->setStrikethrough($pStyles['strike']);
                }
                if (array_key_exists('color', $pStyles)) {
                    $this->getColor()->applyFromArray($pStyles['color']);
                }
                if (array_key_exists('size', $pStyles)) {
                    $this->setSize($pStyles['size']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getName()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getName();
        }
        return $this->name;
    }

    
    public function setName($pValue = 'Calibri')
    {
        if ($pValue == '') {
            $pValue = 'Calibri';
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('name' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->name = $pValue;
        }
        return $this;
    }

    
    public function getSize()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getSize();
        }
        return $this->size;
    }

    
    public function setSize($pValue = 10)
    {
        if ($pValue == '') {
            $pValue = 10;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('size' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->size = $pValue;
        }
        return $this;
    }

    
    public function getBold()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getBold();
        }
        return $this->bold;
    }

    
    public function setBold($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('bold' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->bold = $pValue;
        }
        return $this;
    }

    
    public function getItalic()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getItalic();
        }
        return $this->italic;
    }

    
    public function setItalic($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('italic' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->italic = $pValue;
        }
        return $this;
    }

    
    public function getSuperScript()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getSuperScript();
        }
        return $this->superScript;
    }

    
    public function setSuperScript($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('superScript' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->superScript = $pValue;
            $this->subScript = !$pValue;
        }
        return $this;
    }

        
    public function getSubScript()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getSubScript();
        }
        return $this->subScript;
    }

    
    public function setSubScript($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('subScript' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->subScript = $pValue;
            $this->superScript = !$pValue;
        }
        return $this;
    }

    
    public function getUnderline()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getUnderline();
        }
        return $this->underline;
    }

    
    public function setUnderline($pValue = self::UNDERLINE_NONE)
    {
        if (is_bool($pValue)) {
            $pValue = ($pValue) ? self::UNDERLINE_SINGLE : self::UNDERLINE_NONE;
        } elseif ($pValue == '') {
            $pValue = self::UNDERLINE_NONE;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('underline' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->underline = $pValue;
        }
        return $this;
    }

    
    public function getStrikethrough()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getStrikethrough();
        }
        return $this->strikethrough;
    }

    
    public function setStrikethrough($pValue = false)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('strike' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->strikethrough = $pValue;
        }
        return $this;
    }

    
    public function getColor()
    {
        return $this->color;
    }

    
    public function setColor(PHPExcel_Style_Color $pValue = null)
    {
                $color = $pValue->getIsSupervisor() ? $pValue->getSharedComponent() : $pValue;

        if ($this->isSupervisor) {
            $styleArray = $this->getColor()->getStyleArray(array('argb' => $color->getARGB()));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->color = $color;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->name .
            $this->size .
            ($this->bold ? 't' : 'f') .
            ($this->italic ? 't' : 'f') .
            ($this->superScript ? 't' : 'f') .
            ($this->subScript ? 't' : 'f') .
            $this->underline .
            ($this->strikethrough ? 't' : 'f') .
            $this->color->getHashCode() .
            __CLASS__
        );
    }
}
