<?php


class PHPExcel_Style extends PHPExcel_Style_Supervisor implements PHPExcel_IComparable
{
    
    protected $font;

    
    protected $fill;

    
    protected $borders;

    
    protected $alignment;

    
    protected $numberFormat;

    
    protected $conditionalStyles;

    
    protected $protection;

    
    protected $index;

    
    protected $quotePrefix = false;

    
    public function __construct($isSupervisor = false, $isConditional = false)
    {
                $this->isSupervisor = $isSupervisor;

                $this->conditionalStyles = array();
        $this->font         = new PHPExcel_Style_Font($isSupervisor, $isConditional);
        $this->fill         = new PHPExcel_Style_Fill($isSupervisor, $isConditional);
        $this->borders      = new PHPExcel_Style_Borders($isSupervisor, $isConditional);
        $this->alignment    = new PHPExcel_Style_Alignment($isSupervisor, $isConditional);
        $this->numberFormat = new PHPExcel_Style_NumberFormat($isSupervisor, $isConditional);
        $this->protection   = new PHPExcel_Style_Protection($isSupervisor, $isConditional);

                if ($isSupervisor) {
            $this->font->bindParent($this);
            $this->fill->bindParent($this);
            $this->borders->bindParent($this);
            $this->alignment->bindParent($this);
            $this->numberFormat->bindParent($this);
            $this->protection->bindParent($this);
        }
    }

    
    public function getSharedComponent()
    {
        $activeSheet = $this->getActiveSheet();
        $selectedCell = $this->getActiveCell(); 
        if ($activeSheet->cellExists($selectedCell)) {
            $xfIndex = $activeSheet->getCell($selectedCell)->getXfIndex();
        } else {
            $xfIndex = 0;
        }

        return $this->parent->getCellXfByIndex($xfIndex);
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function getStyleArray($array)
    {
        return array('quotePrefix' => $array);
    }

    
    public function applyFromArray($pStyles = null, $pAdvanced = true)
    {
        if (is_array($pStyles)) {
            if ($this->isSupervisor) {
                $pRange = $this->getSelectedCells();

                                $pRange = strtoupper($pRange);

                                if (strpos($pRange, ':') === false) {
                    $rangeA = $pRange;
                    $rangeB = $pRange;
                } else {
                    list($rangeA, $rangeB) = explode(':', $pRange);
                }

                                $rangeStart = PHPExcel_Cell::coordinateFromString($rangeA);
                $rangeEnd   = PHPExcel_Cell::coordinateFromString($rangeB);

                                $rangeStart[0]    = PHPExcel_Cell::columnIndexFromString($rangeStart[0]) - 1;
                $rangeEnd[0]    = PHPExcel_Cell::columnIndexFromString($rangeEnd[0]) - 1;

                                if ($rangeStart[0] > $rangeEnd[0] && $rangeStart[1] > $rangeEnd[1]) {
                    $tmp = $rangeStart;
                    $rangeStart = $rangeEnd;
                    $rangeEnd = $tmp;
                }

                                if ($pAdvanced && isset($pStyles['borders'])) {
                                                            if (isset($pStyles['borders']['allborders'])) {
                        foreach (array('outline', 'inside') as $component) {
                            if (!isset($pStyles['borders'][$component])) {
                                $pStyles['borders'][$component] = $pStyles['borders']['allborders'];
                            }
                        }
                        unset($pStyles['borders']['allborders']);                     }
                                                            if (isset($pStyles['borders']['outline'])) {
                        foreach (array('top', 'right', 'bottom', 'left') as $component) {
                            if (!isset($pStyles['borders'][$component])) {
                                $pStyles['borders'][$component] = $pStyles['borders']['outline'];
                            }
                        }
                        unset($pStyles['borders']['outline']);                     }
                                                            if (isset($pStyles['borders']['inside'])) {
                        foreach (array('vertical', 'horizontal') as $component) {
                            if (!isset($pStyles['borders'][$component])) {
                                $pStyles['borders'][$component] = $pStyles['borders']['inside'];
                            }
                        }
                        unset($pStyles['borders']['inside']);                     }
                                        $xMax = min($rangeEnd[0] - $rangeStart[0] + 1, 3);
                    $yMax = min($rangeEnd[1] - $rangeStart[1] + 1, 3);

                                        for ($x = 1; $x <= $xMax; ++$x) {
                                                $colStart = ($x == 3) ?
                            PHPExcel_Cell::stringFromColumnIndex($rangeEnd[0])
                                : PHPExcel_Cell::stringFromColumnIndex($rangeStart[0] + $x - 1);
                                                $colEnd = ($x == 1) ?
                            PHPExcel_Cell::stringFromColumnIndex($rangeStart[0])
                                : PHPExcel_Cell::stringFromColumnIndex($rangeEnd[0] - $xMax + $x);

                        for ($y = 1; $y <= $yMax; ++$y) {
                                                        $edges = array();
                            if ($x == 1) {
                                                                $edges[] = 'left';
                            }
                            if ($x == $xMax) {
                                                                $edges[] = 'right';
                            }
                            if ($y == 1) {
                                                                $edges[] = 'top';
                            }
                            if ($y == $yMax) {
                                                                $edges[] = 'bottom';
                            }

                                                        $rowStart = ($y == 3) ?
                                $rangeEnd[1] : $rangeStart[1] + $y - 1;

                                                        $rowEnd = ($y == 1) ?
                                $rangeStart[1] : $rangeEnd[1] - $yMax + $y;

                                                        $range = $colStart . $rowStart . ':' . $colEnd . $rowEnd;

                                                        $regionStyles = $pStyles;
                            unset($regionStyles['borders']['inside']);

                                                        $innerEdges = array_diff(array('top', 'right', 'bottom', 'left'), $edges);

                                                        foreach ($innerEdges as $innerEdge) {
                                switch ($innerEdge) {
                                    case 'top':
                                    case 'bottom':
                                                                                if (isset($pStyles['borders']['horizontal'])) {
                                            $regionStyles['borders'][$innerEdge] = $pStyles['borders']['horizontal'];
                                        } else {
                                            unset($regionStyles['borders'][$innerEdge]);
                                        }
                                        break;
                                    case 'left':
                                    case 'right':
                                                                                if (isset($pStyles['borders']['vertical'])) {
                                            $regionStyles['borders'][$innerEdge] = $pStyles['borders']['vertical'];
                                        } else {
                                            unset($regionStyles['borders'][$innerEdge]);
                                        }
                                        break;
                                }
                            }

                                                        $this->getActiveSheet()->getStyle($range)->applyFromArray($regionStyles, false);
                        }
                    }
                    return $this;
                }

                                                if (preg_match('/^[A-Z]+1:[A-Z]+1048576$/', $pRange)) {
                    $selectionType = 'COLUMN';
                } elseif (preg_match('/^A[0-9]+:XFD[0-9]+$/', $pRange)) {
                    $selectionType = 'ROW';
                } else {
                    $selectionType = 'CELL';
                }

                                switch ($selectionType) {
                    case 'COLUMN':
                        $oldXfIndexes = array();
                        for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
                            $oldXfIndexes[$this->getActiveSheet()->getColumnDimensionByColumn($col)->getXfIndex()] = true;
                        }
                        break;
                    case 'ROW':
                        $oldXfIndexes = array();
                        for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                            if ($this->getActiveSheet()->getRowDimension($row)->getXfIndex() == null) {
                                $oldXfIndexes[0] = true;                             } else {
                                $oldXfIndexes[$this->getActiveSheet()->getRowDimension($row)->getXfIndex()] = true;
                            }
                        }
                        break;
                    case 'CELL':
                        $oldXfIndexes = array();
                        for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
                            for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                                $oldXfIndexes[$this->getActiveSheet()->getCellByColumnAndRow($col, $row)->getXfIndex()] = true;
                            }
                        }
                        break;
                }

                                $workbook = $this->getActiveSheet()->getParent();
                foreach ($oldXfIndexes as $oldXfIndex => $dummy) {
                    $style = $workbook->getCellXfByIndex($oldXfIndex);
                    $newStyle = clone $style;
                    $newStyle->applyFromArray($pStyles);

                    if ($existingStyle = $workbook->getCellXfByHashCode($newStyle->getHashCode())) {
                                                $newXfIndexes[$oldXfIndex] = $existingStyle->getIndex();
                    } else {
                                                $workbook->addCellXf($newStyle);
                        $newXfIndexes[$oldXfIndex] = $newStyle->getIndex();
                    }
                }

                                switch ($selectionType) {
                    case 'COLUMN':
                        for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
                            $columnDimension = $this->getActiveSheet()->getColumnDimensionByColumn($col);
                            $oldXfIndex = $columnDimension->getXfIndex();
                            $columnDimension->setXfIndex($newXfIndexes[$oldXfIndex]);
                        }
                        break;

                    case 'ROW':
                        for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                            $rowDimension = $this->getActiveSheet()->getRowDimension($row);
                            $oldXfIndex = $rowDimension->getXfIndex() === null ?
                                0 : $rowDimension->getXfIndex();                             $rowDimension->setXfIndex($newXfIndexes[$oldXfIndex]);
                        }
                        break;

                    case 'CELL':
                        for ($col = $rangeStart[0]; $col <= $rangeEnd[0]; ++$col) {
                            for ($row = $rangeStart[1]; $row <= $rangeEnd[1]; ++$row) {
                                $cell = $this->getActiveSheet()->getCellByColumnAndRow($col, $row);
                                $oldXfIndex = $cell->getXfIndex();
                                $cell->setXfIndex($newXfIndexes[$oldXfIndex]);
                            }
                        }
                        break;
                }

            } else {
                                if (array_key_exists('fill', $pStyles)) {
                    $this->getFill()->applyFromArray($pStyles['fill']);
                }
                if (array_key_exists('font', $pStyles)) {
                    $this->getFont()->applyFromArray($pStyles['font']);
                }
                if (array_key_exists('borders', $pStyles)) {
                    $this->getBorders()->applyFromArray($pStyles['borders']);
                }
                if (array_key_exists('alignment', $pStyles)) {
                    $this->getAlignment()->applyFromArray($pStyles['alignment']);
                }
                if (array_key_exists('numberformat', $pStyles)) {
                    $this->getNumberFormat()->applyFromArray($pStyles['numberformat']);
                }
                if (array_key_exists('protection', $pStyles)) {
                    $this->getProtection()->applyFromArray($pStyles['protection']);
                }
                if (array_key_exists('quotePrefix', $pStyles)) {
                    $this->quotePrefix = $pStyles['quotePrefix'];
                }
            }
        } else {
            throw new PHPExcel_Exception("Invalid style array passed.");
        }
        return $this;
    }

    
    public function getFill()
    {
        return $this->fill;
    }

    
    public function getFont()
    {
        return $this->font;
    }

    
    public function setFont(PHPExcel_Style_Font $font)
    {
        $this->font = $font;
        return $this;
    }

    
    public function getBorders()
    {
        return $this->borders;
    }

    
    public function getAlignment()
    {
        return $this->alignment;
    }

    
    public function getNumberFormat()
    {
        return $this->numberFormat;
    }

    
    public function getConditionalStyles()
    {
        return $this->getActiveSheet()->getConditionalStyles($this->getActiveCell());
    }

    
    public function setConditionalStyles($pValue = null)
    {
        if (is_array($pValue)) {
            $this->getActiveSheet()->setConditionalStyles($this->getSelectedCells(), $pValue);
        }
        return $this;
    }

    
    public function getProtection()
    {
        return $this->protection;
    }

    
    public function getQuotePrefix()
    {
        if ($this->isSupervisor) {
            return $this->getSharedComponent()->getQuotePrefix();
        }
        return $this->quotePrefix;
    }

    
    public function setQuotePrefix($pValue)
    {
        if ($pValue == '') {
            $pValue = false;
        }
        if ($this->isSupervisor) {
            $styleArray = array('quotePrefix' => $pValue);
            $this->getActiveSheet()->getStyle($this->getSelectedCells())->applyFromArray($styleArray);
        } else {
            $this->quotePrefix = (boolean) $pValue;
        }
        return $this;
    }

    
    public function getHashCode()
    {
        $hashConditionals = '';
        foreach ($this->conditionalStyles as $conditional) {
            $hashConditionals .= $conditional->getHashCode();
        }

        return md5(
            $this->fill->getHashCode() .
            $this->font->getHashCode() .
            $this->borders->getHashCode() .
            $this->alignment->getHashCode() .
            $this->numberFormat->getHashCode() .
            $hashConditionals .
            $this->protection->getHashCode() .
            ($this->quotePrefix  ? 't' : 'f') .
            __CLASS__
        );
    }

    
    public function getIndex()
    {
        return $this->index;
    }

    
    public function setIndex($pValue)
    {
        $this->index = $pValue;
    }
}
