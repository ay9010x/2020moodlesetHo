<?php


class PHPExcel_Style_Protection extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const PROTECTION_INHERIT      = 'inherit';
    const PROTECTION_PROTECTED    = 'protected';
    const PROTECTION_UNPROTECTED  = 'unprotected';

    
    protected $locked;

    
    protected $hidden;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                if (!$isConditional) {
            $this->locked = self::PROTECTION_INHERIT;
            $this->hidden = self::PROTECTION_INHERIT;
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getProtection();
    }

    
    public function getStyleArray($array)
    {
        return array('protection' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (isset($pStyles['locked'])) {
                    $this->setLocked($pStyles['locked']);
                }
                if (isset($pStyles['hidden'])) {
                    $this->setHidden($pStyles['hidden']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getLocked()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getLocked();
        }
        return $this->locked;
    }

    
    public function setLocked($pValue = self::PROTECTION_INHERIT)
    {
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('locked' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->locked = $pValue;
        }
        return $this;
    }

    
    public function getHidden()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHidden();
        }
        return $this->hidden;
    }

    
    public function setHidden($pValue = self::PROTECTION_INHERIT)
    {
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('hidden' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->hidden = $pValue;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashCode();
        }
        return md5(
            $this->locked .
            $this->hidden .
            __CLASS__
        );
    }
}
