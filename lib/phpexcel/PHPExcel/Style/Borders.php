<?php


class PHPExcel_Style_Borders extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    const DIAGONAL_NONE = 0;
    const DIAGONAL_UP   = 1;
    const DIAGONAL_DOWN = 2;
    const DIAGONAL_BOTH = 3;

    
    protected $left;

    
    protected $right;

    
    protected $top;

    
    protected $bottom;

    
    protected $diagonal;

    
    protected $diagonalDirection;

    
    protected $allBorders;

    
    protected $outline;

    
    protected $inside;

    
    protected $vertical;

    
    protected $horizontal;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                parent::__construct($isSupervisor);

                $this->left = new PHPExcel_Style_Border($isSupervisor, $isConditional);
        $this->right = new PHPExcel_Style_Border($isSupervisor, $isConditional);
        $this->top = new PHPExcel_Style_Border($isSupervisor, $isConditional);
        $this->bottom = new PHPExcel_Style_Border($isSupervisor, $isConditional);
        $this->diagonal = new PHPExcel_Style_Border($isSupervisor, $isConditional);
        $this->diagonalDirection = PHPExcel_Style_Borders::DIAGONAL_NONE;

                if ($isSupervisor) {
                        $this->allBorders = new PHPExcel_Style_Border(true);
            $this->outline = new PHPExcel_Style_Border(true);
            $this->inside = new PHPExcel_Style_Border(true);
            $this->vertical = new PHPExcel_Style_Border(true);
            $this->horizontal = new PHPExcel_Style_Border(true);

                        $this->left->bindParent($this, 'left');
            $this->right->bindParent($this, 'right');
            $this->top->bindParent($this, 'top');
            $this->bottom->bindParent($this, 'bottom');
            $this->diagonal->bindParent($this, 'diagonal');
            $this->allBorders->bindParent($this, 'allBorders');
            $this->outline->bindParent($this, 'outline');
            $this->inside->bindParent($this, 'inside');
            $this->vertical->bindParent($this, 'vertical');
            $this->horizontal->bindParent($this, 'horizontal');
        }
    }

    
    public function getSharedComponent()
    {
        return $this->parent->getSharedComponent()->getBorders();
    }

    
    public function getStyleArray($array)
    {
        return array('borders' => $array);
    }

    
    public function applyFromArray($pStyles = null)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($this->getStyleArray($pStyles));
            } else {
                if (array_key_exists('left', $pStyles)) {
                    $this->getLeft()->applyFromArray($pStyles['left']);
                }
                if (array_key_exists('right', $pStyles)) {
                    $this->getRight()->applyFromArray($pStyles['right']);
                }
                if (array_key_exists('top', $pStyles)) {
                    $this->getTop()->applyFromArray($pStyles['top']);
                }
                if (array_key_exists('bottom', $pStyles)) {
                    $this->getBottom()->applyFromArray($pStyles['bottom']);
                }
                if (array_key_exists('diagonal', $pStyles)) {
                    $this->getDiagonal()->applyFromArray($pStyles['diagonal']);
                }
                if (array_key_exists('diagonaldirection', $pStyles)) {
                    $this->setDiagonalDirection($pStyles['diagonaldirection']);
                }
                if (array_key_exists('allborders', $pStyles)) {
                    $this->getLeft()->applyFromArray($pStyles['allborders']);
                    $this->getRight()->applyFromArray($pStyles['allborders']);
                    $this->getTop()->applyFromArray($pStyles['allborders']);
                    $this->getBottom()->applyFromArray($pStyles['allborders']);
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getLeft()
    {
        return $this->left;
    }

    
    public function getRight()
    {
        return $this->right;
    }

    
    public function getTop()
    {
        return $this->top;
    }

    
    public function getBottom()
    {
        return $this->bottom;
    }

    
    public function getDiagonal()
    {
        return $this->diagonal;
    }

    
    public function getAllBorders()
    {
        if (!$this->isSupervisor) {
            throw new PHPExcel_Exception('Can only get pseudo-border for supervisor.');
        }
        return $this->allBorders;
    }

    
    public function getOutline()
    {
        if (!$this->isSupervisor) {
            throw new PHPExcel_Exception('Can only get pseudo-border for supervisor.');
        }
        return $this->outline;
    }

    
    public function getInside()
    {
        if (!$this->isSupervisor) {
            throw new PHPExcel_Exception('Can only get pseudo-border for supervisor.');
        }
        return $this->inside;
    }

    
    public function getVertical()
    {
        if (!$this->isSupervisor) {
            throw new PHPExcel_Exception('Can only get pseudo-border for supervisor.');
        }
        return $this->vertical;
    }

    
    public function getHorizontal()
    {
        if (!$this->isSupervisor) {
            throw new PHPExcel_Exception('Can only get pseudo-border for supervisor.');
        }
        return $this->horizontal;
    }

    
    public function getDiagonalDirection()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getDiagonalDirection();
        }
        return $this->diagonalDirection;
    }

    
    public function setDiagonalDirection($pValue = PHPExcel_Style_Borders::DIAGONAL_NONE)
    {
        if ($pValue == '') {
            $pValue = PHPExcel_Style_Borders::DIAGONAL_NONE;
        }
        if ($this->isSupervisor) {
            $styleArray = $this->getStyleArray(array('diagonaldirection' => $pValue));
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->diagonalDirection = $pValue;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getHashcode();
        }
        return md5(
            $this->getLeft()->getHashCode() .
            $this->getRight()->getHashCode() .
            $this->getTop()->getHashCode() .
            $this->getBottom()->getHashCode() .
            $this->getDiagonal()->getHashCode() .
            $this->getDiagonalDirection() .
            __CLASS__
        );
    }
}
