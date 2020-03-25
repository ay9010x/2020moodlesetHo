<?php


class PHPExcel_Worksheet_AutoFilter_Column
{
    const AUTOFILTER_FILTERTYPE_FILTER         = 'filters';
    const AUTOFILTER_FILTERTYPE_CUSTOMFILTER   = 'customFilters';
            const AUTOFILTER_FILTERTYPE_DYNAMICFILTER  = 'dynamicFilter';
            const AUTOFILTER_FILTERTYPE_TOPTENFILTER   = 'top10';

    
    private static $filterTypes = array(
                                        self::AUTOFILTER_FILTERTYPE_FILTER,
        self::AUTOFILTER_FILTERTYPE_CUSTOMFILTER,
        self::AUTOFILTER_FILTERTYPE_DYNAMICFILTER,
        self::AUTOFILTER_FILTERTYPE_TOPTENFILTER,
    );

    
    const AUTOFILTER_COLUMN_JOIN_AND = 'and';
    const AUTOFILTER_COLUMN_JOIN_OR  = 'or';

    
    private static $ruleJoins = array(
        self::AUTOFILTER_COLUMN_JOIN_AND,
        self::AUTOFILTER_COLUMN_JOIN_OR,
    );

    
    private $parent;


    
    private $columnIndex = '';


    
    private $filterType = self::AUTOFILTER_FILTERTYPE_FILTER;


    
    private $join = self::AUTOFILTER_COLUMN_JOIN_OR;


    
    private $ruleset = array();


    
    private $attributes = array();


    
    public function __construct($pColumn, PHPExcel_Worksheet_AutoFilter $pParent = null)
    {
        $this->columnIndex = $pColumn;
        $this->parent = $pParent;
    }

    
    public function getColumnIndex()
    {
        return $this->columnIndex;
    }

    
    public function setColumnIndex($pColumn)
    {
                $pColumn = strtoupper($pColumn);
        if ($this->parent !== null) {
            $this->parent->testColumnInRange($pColumn);
        }

        $this->columnIndex = $pColumn;

        return $this;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function setParent(PHPExcel_Worksheet_AutoFilter $pParent = null)
    {
        $this->parent = $pParent;

        return $this;
    }

    
    public function getFilterType()
    {
        return $this->filterType;
    }

    
    public function setFilterType($pFilterType = self::AUTOFILTER_FILTERTYPE_FILTER)
    {
        if (!in_array($pFilterType, self::$filterTypes)) {
            throw new PHPExcel_Exception('Invalid filter type for column AutoFilter.');
        }

        $this->filterType = $pFilterType;

        return $this;
    }

    
    public function getJoin()
    {
        return $this->join;
    }

    
    public function setJoin($pJoin = self::AUTOFILTER_COLUMN_JOIN_OR)
    {
                $pJoin = strtolower($pJoin);
        if (!in_array($pJoin, self::$ruleJoins)) {
            throw new PHPExcel_Exception('Invalid rule connection for column AutoFilter.');
        }

        $this->join = $pJoin;

        return $this;
    }

    
    public function setAttributes($pAttributes = array())
    {
        $this->attributes = $pAttributes;

        return $this;
    }

    
    public function setAttribute($pName, $pValue)
    {
        $this->attributes[$pName] = $pValue;

        return $this;
    }

    
    public function getAttributes()
    {
        return $this->attributes;
    }

    
    public function getAttribute($pName)
    {
        if (isset($this->attributes[$pName])) {
            return $this->attributes[$pName];
        }
        return null;
    }

    
    public function getRules()
    {
        return $this->ruleset;
    }

    
    public function getRule($pIndex)
    {
        if (!isset($this->ruleset[$pIndex])) {
            $this->ruleset[$pIndex] = new PHPExcel_Worksheet_AutoFilter_Column_Rule($this);
        }
        return $this->ruleset[$pIndex];
    }

    
    public function createRule()
    {
        $this->ruleset[] = new PHPExcel_Worksheet_AutoFilter_Column_Rule($this);

        return end($this->ruleset);
    }

    
    public function addRule(PHPExcel_Worksheet_AutoFilter_Column_Rule $pRule, $returnRule = true)
    {
        $pRule->setParent($this);
        $this->ruleset[] = $pRule;

        return ($returnRule) ? $pRule : $this;
    }

    
    public function deleteRule($pIndex)
    {
        if (isset($this->ruleset[$pIndex])) {
            unset($this->ruleset[$pIndex]);
                        if (count($this->ruleset) <= 1) {
                $this->setJoin(self::AUTOFILTER_COLUMN_JOIN_OR);
            }
        }

        return $this;
    }

    
    public function clearRules()
    {
        $this->ruleset = array();
        $this->setJoin(self::AUTOFILTER_COLUMN_JOIN_OR);

        return $this;
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                if ($key == 'parent') {
                                        $this->$key = null;
                } else {
                    $this->$key = clone $value;
                }
            } elseif ((is_array($value)) && ($key == 'ruleset')) {
                                $this->$key = array();
                foreach ($value as $k => $v) {
                    $this->{$key[$k]} = clone $v;
                                        $this->{$key[$k]}->setParent($this);
                }
            } else {
                $this->$key = $value;
            }
        }
    }
}
