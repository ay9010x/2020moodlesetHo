<?php




class PHPExcel_Style_Conditional implements PHPExcel_IComparable
{
    
    const CONDITION_NONE         = 'none';
    const CONDITION_CELLIS       = 'cellIs';
    const CONDITION_CONTAINSTEXT = 'containsText';
    const CONDITION_EXPRESSION   = 'expression';

    
    const OPERATOR_NONE               = '';
    const OPERATOR_BEGINSWITH         = 'beginsWith';
    const OPERATOR_ENDSWITH           = 'endsWith';
    const OPERATOR_EQUAL              = 'equal';
    const OPERATOR_GREATERTHAN        = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN           = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL    = 'lessThanOrEqual';
    const OPERATOR_NOTEQUAL           = 'notEqual';
    const OPERATOR_CONTAINSTEXT       = 'containsText';
    const OPERATOR_NOTCONTAINS        = 'notContains';
    const OPERATOR_BETWEEN            = 'between';

    
    private $conditionType;

    
    private $operatorType;

    
    private $text;

    
    private $condition = array();

    
    private $style;

    
    public function __construct()
    {
                $this->conditionType = PHPExcel_Style_Conditional::CONDITION_NONE;
        $this->operatorType  = PHPExcel_Style_Conditional::OPERATOR_NONE;
        $this->text          = null;
        $this->condition     = array();
        $this->style         = new PHPExcel_Style(false, true);
    }

    
    public function getConditionType()
    {
        return $this->conditionType;
    }

    
    public function setConditionType($pValue = PHPExcel_Style_Conditional::CONDITION_NONE)
    {
        $this->conditionType = $pValue;
        return $this;
    }

    
    public function getOperatorType()
    {
        return $this->operatorType;
    }

    
    public function setOperatorType($pValue = PHPExcel_Style_Conditional::OPERATOR_NONE)
    {
        $this->operatorType = $pValue;
        return $this;
    }

    
    public function getText()
    {
        return $this->text;
    }

    
    public function setText($value = null)
    {
        $this->text = $value;
        return $this;
    }

    
    public function getCondition()
    {
        if (isset($this->condition[0])) {
            return $this->condition[0];
        }

        return '';
    }

    
    public function setCondition($pValue = '')
    {
        if (!is_array($pValue)) {
            $pValue = array($pValue);
        }

        return $this->setConditions($pValue);
    }

    
    public function getConditions()
    {
        return $this->condition;
    }

    
    public function setConditions($pValue)
    {
        if (!is_array($pValue)) {
            $pValue = array($pValue);
        }
        $this->condition = $pValue;
        return $this;
    }

    
    public function addCondition($pValue = '')
    {
        $this->condition[] = $pValue;
        return $this;
    }

    
    public function getStyle()
    {
        return $this->style;
    }

    
    public function setStyle(PHPExcel_Style $pValue = null)
    {
           $this->style = $pValue;
           return $this;
    }

    
    public function getHashCode()
    {
        return md5(
            $this->conditionType .
            $this->operatorType .
            implode(';', $this->condition) .
            $this->style->getHashCode() .
            __CLASS__
        );
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
