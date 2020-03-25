<?php


class PHPExcel_Style_Fill extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const FILL_NONE                    = 'none';
    const FILL_SOLID                   = 'solid';
    const FILL_GRADIENT_LINEAR         = 'linear';
    const FILL_GRADIENT_PATH           = 'path';
    const FILL_PATTERN_DARKDOWN        = 'darkDown';
    const FILL_PATTERN_DARKGRAY        = 'darkGray';
    const FILL_PATTERN_DARKGRID        = 'darkGrid';
    const FILL_PATTERN_DARKHORIZONTAL  = 'darkHorizontal';
    const FILL_PATTERN_DARKTRELLIS     = 'darkTrellis';
    const FILL_PATTERN_DARKUP          = 'darkUp';
    const FILL_PATTERN_DARKVERTICAL    = 'darkVertical';
    const FILL_PATTERN_GRAY0625        = 'gray0625';
    const FILL_PATTERN_GRAY125         = 'gray125';
    const FILL_PATTERN_LIGHTDOWN       = 'lightDown';
    const FILL_PATTERN_LIGHTGRAY       = 'lightGray';
    const FILL_PATTERN_LIGHTGRID       = 'lightGrid';
    const FILL_PATTERN_LIGHTHORIZONTAL = 'lightHorizontal';
    const FILL_PATTERN_LIGHTTRELLIS    = 'lightTrellis';
    const FILL_PATTERN_LIGHTUP         = 'lightUp';
    const FILL_PATTERN_LIGHTVERTICAL   = 'lightVertical';
    const FILL_PATTERN_MEDIUMGRAY      = 'mediumGray';

    
    protected $fillType = PHPExcel_Style_Fill::FILL_NONE;

    
    protected $rotation = 0;

    
    protected $startColor;

    
    protected $endColor;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                if ($isConditional) {
            $this->fillType = null;
        }
        $this->startColor = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_WHITE, $isSupervisor, $isConditional);
        $this->endColor = new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLACK, $isSupervisor, $isConditional);

                if ($isSupervisor) {
            $this->startColor->bindParent($this, 'startColor');
            $this->endColor->bindParent($this, 'endColor');
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getFill();
    }

    
    public function getStyleArray($array)
    {
        return array('fill' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (array_key_exists('type', $pStyles)) {
                    $this->setFillType($pStyles['type']);
                }
                if (array_key_exists('rotation', $pStyles)) {
                    $this->setRotation($pStyles['rotation']);
                }
                if (array_key_exists('startcolor', $pStyles)) {
                    $this->getStartColor()->applyFromArray($pStyles['startcolor']);
                }
                if (array_key_exists('endcolor', $pStyles)) {
                    $this->getEndColor()->applyFromArray($pStyles['endcolor']);
                }
                if (array_key_exists('color', $pStyles)) {
                    $this->getStartColor()->applyFromArray($pStyles['color']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getFillType()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getFillType();
        }
        return $this->fillType;
    }

    
    public function setFillType($pValue = PHPExcel_Style_Fill::FILL_NONE)
    {
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('type' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->fillType = $pValue;
        }
        return $this;
    }

    
    public function getRotation()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getRotation();
        }
        return $this->rotation;
    }

    
    public function setRotation($pValue = 0)
    {
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('rotation' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->rotation = $pValue;
        }
        return $this;
    }

    
    public function getStartColor()
    {
        return $this->startColor;
    }

    
    public function setStartColor(PHPExcel_Style_Color $pValue = null)
    {
                $color = $pValue->getIsSupervisor() ? $pValue->getSharedComponent() : $pValue;

        if ($this->isSupervisor) {
            $styleArray = $this->getStartColor()->getStyleArray(array('argb' => $color->getARGB()));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->startColor = $color;
        }
        return $this;
    }

    
    public function getEndColor()
    {
        return $this->endColor;
    }

    
    public function setEndColor(PHPExcel_Style_Color $pValue = null)
    {
                $color = $pValue->getIsSupervisor() ? $pValue->getSharedComponent() : $pValue;

        if ($this->isSupervisor) {
            $styleArray = $this->getEndColor()->getStyleArray(array('argb' => $color->getARGB()));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->endColor = $color;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->getFillType() .
            $this->getRotation() .
            $this->getStartColor()->getHashCode() .
            $this->getEndColor()->getHashCode() .
            __CLASS__
        );
    }
}
