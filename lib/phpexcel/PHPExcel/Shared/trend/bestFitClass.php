<?php


class PHPExcel_Best_Fit
{
    
    protected $error = false;

    
    protected $bestFitType = 'undetermined';

    
    protected $valueCount = 0;

    
    protected $xValues = array();

    
    protected $yValues = array();

    
    protected $adjustToZero = false;

    
    protected $yBestFitValues = array();

    protected $goodnessOfFit = 1;

    protected $stdevOfResiduals = 0;

    protected $covariance = 0;

    protected $correlation = 0;

    protected $SSRegression = 0;

    protected $SSResiduals = 0;

    protected $DFResiduals = 0;

    protected $f = 0;

    protected $slope = 0;

    protected $slopeSE = 0;

    protected $intersect = 0;

    protected $intersectSE = 0;

    protected $xOffset = 0;

    protected $yOffset = 0;


    public function getError()
    {
        return $this->error;
    }


    public function getBestFitType()
    {
        return $this->bestFitType;
    }

    
    public function getValueOfYForX($xValue)
    {
        return false;
    }

    
    public function getValueOfXForY($yValue)
    {
        return false;
    }

    
    public function getXValues()
    {
        return $this->xValues;
    }

    
    public function getEquation($dp = 0)
    {
        return false;
    }

    
    public function getSlope($dp = 0)
    {
        if ($dp != 0) {
            return round($this->slope, $dp);
        }
        return $this->slope;
    }

    
    public function getSlopeSE($dp = 0)
    {
        if ($dp != 0) {
            return round($this->slopeSE, $dp);
        }
        return $this->slopeSE;
    }

    
    public function getIntersect($dp = 0)
    {
        if ($dp != 0) {
            return round($this->intersect, $dp);
        }
        return $this->intersect;
    }

    
    public function getIntersectSE($dp = 0)
    {
        if ($dp != 0) {
            return round($this->intersectSE, $dp);
        }
        return $this->intersectSE;
    }

    
    public function getGoodnessOfFit($dp = 0)
    {
        if ($dp != 0) {
            return round($this->goodnessOfFit, $dp);
        }
        return $this->goodnessOfFit;
    }

    public function getGoodnessOfFitPercent($dp = 0)
    {
        if ($dp != 0) {
            return round($this->goodnessOfFit * 100, $dp);
        }
        return $this->goodnessOfFit * 100;
    }

    
    public function getStdevOfResiduals($dp = 0)
    {
        if ($dp != 0) {
            return round($this->stdevOfResiduals, $dp);
        }
        return $this->stdevOfResiduals;
    }

    public function getSSRegression($dp = 0)
    {
        if ($dp != 0) {
            return round($this->SSRegression, $dp);
        }
        return $this->SSRegression;
    }

    public function getSSResiduals($dp = 0)
    {
        if ($dp != 0) {
            return round($this->SSResiduals, $dp);
        }
        return $this->SSResiduals;
    }

    public function getDFResiduals($dp = 0)
    {
        if ($dp != 0) {
            return round($this->DFResiduals, $dp);
        }
        return $this->DFResiduals;
    }

    public function getF($dp = 0)
    {
        if ($dp != 0) {
            return round($this->f, $dp);
        }
        return $this->f;
    }

    public function getCovariance($dp = 0)
    {
        if ($dp != 0) {
            return round($this->covariance, $dp);
        }
        return $this->covariance;
    }

    public function getCorrelation($dp = 0)
    {
        if ($dp != 0) {
            return round($this->correlation, $dp);
        }
        return $this->correlation;
    }

    public function getYBestFitValues()
    {
        return $this->yBestFitValues;
    }

    protected function calculateGoodnessOfFit($sumX, $sumY, $sumX2, $sumY2, $sumXY, $meanX, $meanY, $const)
    {
        $SSres = $SScov = $SScor = $SStot = $SSsex = 0.0;
        foreach ($this->xValues as $xKey => $xValue) {
            $bestFitY = $this->yBestFitValues[$xKey] = $this->getValueOfYForX($xValue);

            $SSres += ($this->yValues[$xKey] - $bestFitY) * ($this->yValues[$xKey] - $bestFitY);
            if ($const) {
                $SStot += ($this->yValues[$xKey] - $meanY) * ($this->yValues[$xKey] - $meanY);
            } else {
                $SStot += $this->yValues[$xKey] * $this->yValues[$xKey];
            }
            $SScov += ($this->xValues[$xKey] - $meanX) * ($this->yValues[$xKey] - $meanY);
            if ($const) {
                $SSsex += ($this->xValues[$xKey] - $meanX) * ($this->xValues[$xKey] - $meanX);
            } else {
                $SSsex += $this->xValues[$xKey] * $this->xValues[$xKey];
            }
        }

        $this->SSResiduals = $SSres;
        $this->DFResiduals = $this->valueCount - 1 - $const;

        if ($this->DFResiduals == 0.0) {
            $this->stdevOfResiduals = 0.0;
        } else {
            $this->stdevOfResiduals = sqrt($SSres / $this->DFResiduals);
        }
        if (($SStot == 0.0) || ($SSres == $SStot)) {
            $this->goodnessOfFit = 1;
        } else {
            $this->goodnessOfFit = 1 - ($SSres / $SStot);
        }

        $this->SSRegression = $this->goodnessOfFit * $SStot;
        $this->covariance = $SScov / $this->valueCount;
        $this->correlation = ($this->valueCount * $sumXY - $sumX * $sumY) / sqrt(($this->valueCount * $sumX2 - pow($sumX, 2)) * ($this->valueCount * $sumY2 - pow($sumY, 2)));
        $this->slopeSE = $this->stdevOfResiduals / sqrt($SSsex);
        $this->intersectSE = $this->stdevOfResiduals * sqrt(1 / ($this->valueCount - ($sumX * $sumX) / $sumX2));
        if ($this->SSResiduals != 0.0) {
            if ($this->DFResiduals == 0.0) {
                $this->f = 0.0;
            } else {
                $this->f = $this->SSRegression / ($this->SSResiduals / $this->DFResiduals);
            }
        } else {
            if ($this->DFResiduals == 0.0) {
                $this->f = 0.0;
            } else {
                $this->f = $this->SSRegression / $this->DFResiduals;
            }
        }
    }

    protected function leastSquareFit($yValues, $xValues, $const)
    {
                $x_sum = array_sum($xValues);
        $y_sum = array_sum($yValues);
        $meanX = $x_sum / $this->valueCount;
        $meanY = $y_sum / $this->valueCount;
        $mBase = $mDivisor = $xx_sum = $xy_sum = $yy_sum = 0.0;
        for ($i = 0; $i < $this->valueCount; ++$i) {
            $xy_sum += $xValues[$i] * $yValues[$i];
            $xx_sum += $xValues[$i] * $xValues[$i];
            $yy_sum += $yValues[$i] * $yValues[$i];

            if ($const) {
                $mBase += ($xValues[$i] - $meanX) * ($yValues[$i] - $meanY);
                $mDivisor += ($xValues[$i] - $meanX) * ($xValues[$i] - $meanX);
            } else {
                $mBase += $xValues[$i] * $yValues[$i];
                $mDivisor += $xValues[$i] * $xValues[$i];
            }
        }

                $this->slope = $mBase / $mDivisor;

                if ($const) {
            $this->intersect = $meanY - ($this->slope * $meanX);
        } else {
            $this->intersect = 0;
        }

        $this->calculateGoodnessOfFit($x_sum, $y_sum, $xx_sum, $yy_sum, $xy_sum, $meanX, $meanY, $const);
    }

    
    public function __construct($yValues, $xValues = array(), $const = true)
    {
                $nY = count($yValues);
        $nX = count($xValues);

                if ($nX == 0) {
            $xValues = range(1, $nY);
            $nX = $nY;
        } elseif ($nY != $nX) {
                        $this->error = true;
            return false;
        }

        $this->valueCount = $nY;
        $this->xValues = $xValues;
        $this->yValues = $yValues;
    }
}
