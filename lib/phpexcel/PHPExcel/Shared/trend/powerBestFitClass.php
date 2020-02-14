<?php

require_once PHPEXCEL_ROOT . 'PHPExcel/Shared/trend/bestFitClass.php';


class PHPExcel_Power_Best_Fit extends PHPExcel_Best_Fit
{
    
    protected $bestFitType        = 'power';


    
    public function getValueOfYForX($xValue)
    {
        return $this->getIntersect() * pow(($xValue - $this->xOffset), $this->getSlope());
    }


    
    public function getValueOfXForY($yValue)
    {
        return pow((($yValue + $this->yOffset) / $this->getIntersect()), (1 / $this->getSlope()));
    }


    
    public function getEquation($dp = 0)
    {
        $slope = $this->getSlope($dp);
        $intersect = $this->getIntersect($dp);

        return 'Y = ' . $intersect . ' * X^' . $slope;
    }


    
    public function getIntersect($dp = 0)
    {
        if ($dp != 0) {
            return round(exp($this->intersect), $dp);
        }
        return exp($this->intersect);
    }


    
    private function powerRegression($yValues, $xValues, $const)
    {
        foreach ($xValues as &$value) {
            if ($value < 0.0) {
                $value = 0 - log(abs($value));
            } elseif ($value > 0.0) {
                $value = log($value);
            }
        }
        unset($value);
        foreach ($yValues as &$value) {
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
            $this->powerRegression($yValues, $xValues, $const);
        }
    }
}
