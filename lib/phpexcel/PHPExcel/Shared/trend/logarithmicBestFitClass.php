<?php

require_once(PHPEXCEL_ROOT . 'PHPExcel/Shared/trend/bestFitClass.php');


class PHPExcel_Logarithmic_Best_Fit extends PHPExcel_Best_Fit
{
    
    protected $bestFitType        = 'logarithmic';

    
    public function getValueOfYForX($xValue)
    {
        return $this->getIntersect() + $this->getSlope() * log($xValue - $this->xOffset);
    }

    
    public function getValueOfXForY($yValue)
    {
        return exp(($yValue - $this->getIntersect()) / $this->getSlope());
    }

    
    public function getEquation($dp = 0)
    {
        $slope = $this->getSlope($dp);
        $intersect = $this->getIntersect($dp);

        return 'Y = '.$intersect.' + '.$slope.' * log(X)';
    }

    
    private function logarithmicRegression($yValues, $xValues, $const)
    {
        foreach ($xValues as &$value) {
            if ($value < 0.0) {
                $value = 0 - log(abs($value));
            } elseif ($value > 0.0) {
                $value = log($value);
            }
        }
        unset($value);

        $this->leastSquareFit($yValues, $xValues, $const);
    }

    
    public function __construct($yValues, $xValues = array(), $const = true)
    {
        if (parent::__construct($yValues, $xValues) !== false) {
            $this->logarithmicRegression($yValues, $xValues, $const);
        }
    }
}
