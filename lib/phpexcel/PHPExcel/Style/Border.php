<?php


class PHPExcel_Style_Border extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const BORDER_NONE             = 'none';
    const BORDER_DASHDOT          = 'dashDot';
    const BORDER_DASHDOTDOT       = 'dashDotDot';
    const BORDER_DASHED           = 'dashed';
    const BORDER_DOTTED           = 'dotted';
    const BORDER_DOUBLE           = 'double';
    const BORDER_HAIR             = 'hair';
    const BORDER_MEDIUM           = 'medium';
    const BORDER_MEDIUMDASHDOT    = 'mediumDashDot';
    const BORDER_MEDIUMDASHDOTDOT = 'mediumDashDotDot';
    const BORDER_MEDIUMDASHED     = 'mediumDashed';
    const BORDER_SLANTDASHDOT     = 'slantDashDot';
    const BORDER_THICK            = 'thick';
    const BORDER_THIN             = 'thin';

    
    protected $borderStyle = PHPExcel_Style_Border::BORDER_NONE;

    
    protected $color;

    
    protected $parentPropertyName;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                $this->color    = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK, $isSupervisor);

                if ($isSupervisor) {
            $this->color->bindParent($this, 'color');
        }
    }

    
    public function bindParent($parent, $parentPropertyName = null)
    {
        $this->parent = $parent;
        $this->parentPropertyName = $parentPropertyName;
        return $this;
    }

    
    public function getSharedComponent()
    {
        switch ($this->parentPropertyName) {
            case 'allBorders':
            case 'horizontal':
            case 'inside':
            case 'outline':
            case 'vertical':
                throw new PHPExcel_Exception('Cannot get shared component for a pseudo-border.');
                break;
            case 'bottom':
                return $this->parent->getSharedComponent()->getBottom();
            case 'diagonal':
                return $this->parent->getSharedComponent()->getDiagonal();
            case 'left':
                return $this->parent->getSharedComponent()->getLeft();
            case 'right':
                return $this->parent->getSharedComponent()->getRight();
            case 'top':
                return $this->parent->getSharedComponent()->getTop();
        }
    }

    
    public function getStyleArray($array)
    {
        switch ($this->parentPropertyName) {
            case 'allBorders':
            case 'bottom':
            case 'diagonal':
            case 'horizontal':
            case 'inside':
            case 'left':
            case 'outline':
            case 'right':
            case 'top':
            case 'vertical':
                $key = strtolower('vertical');
                break;
        }
        return $this->parent->getStyleArray(array($key => $array));
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (isset($pStyles['style'])) {
                    $this->setBorderStyle($pStyles['style']);
                }
                if (isset($pStyles['color'])) {
                    $this->getColor()->applyFromArray($pStyles['color']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getBorderStyle()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getBorderStyle();
        }
        return $this->borderStyle;
    }

    
    public function setBorderStyle($pValue = PHPExcel_Style_Border::BORDER_NONE)
    {

        if (empty($pValue)) {
            $pValue = PHPExcel_Style_Border::BORDER_NONE;
        } elseif (is_bool($pValue) && $pValue) {
            $pValue = PHPExcel_Style_Border::BORDER_MEDIUM;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('style' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->borderStyle = $pValue;
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
            $this->borderStyle .
            $this->color->getHashCode() .
            __CLASS__
        );
    }
}
