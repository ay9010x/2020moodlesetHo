<?php


class PHPExcel_Worksheet_AutoFilter
{
    
    private $workSheet;


    
    private $range = '';


    
    private $columns = array();


    
    public function __construct($pRange = '', PHPExcel_Worksheet $pSheet = null)
    {
        $this->range = $pRange;
        $this->workSheet = $pSheet;
    }

    
    public function getParent()
    {
        return $this->workSheet;
    }

    
    public function setParent(PHPExcel_Worksheet $pSheet = null)
    {
        $this->workSheet = $pSheet;

        return $this;
    }

    
    public function getRange()
    {
        return $this->range;
    }

    
    public function setRange($pRange = '')
    {
                $cellAddress = explode('!', strtoupper($pRange));
        if (count($cellAddress) > 1) {
            list($worksheet, $pRange) = $cellAddress;
        }

        if (strpos($pRange, ':') !== false) {
            $this->range = $pRange;
        } elseif (empty($pRange)) {
            $this->range = '';
        } else {
            throw new PHPExcel_Exception('Autofilter must be set on a range of cells.');
        }

        if (empty($pRange)) {
                        $this->columns = array();
        } else {
                        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($this->range);
            foreach ($this->columns as $key => $value) {
                $colIndex = PHPExcel_Cell::columnIndexFromString($key);
                if (($rangeStart[0] > $colIndex) || ($rangeEnd[0] < $colIndex)) {
                    unset($this->columns[$key]);
                }
            }
        }

        return $this;
    }

    
    public function getColumns()
    {
        return $this->columns;
    }

    
    public function testColumnInRange($column)
    {
        if (empty($this->range)) {
            throw new PHPExcel_Exception("No autofilter range is defined.");
        }

        $columnIndex = PHPExcel_Cell::columnIndexFromString($column);
        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($this->range);
        if (($rangeStart[0] > $columnIndex) || ($rangeEnd[0] < $columnIndex)) {
            throw new PHPExcel_Exception("Column is outside of current autofilter range.");
        }

        return $columnIndex - $rangeStart[0];
    }

    
    public function getColumnOffset($pColumn)
    {
        return $this->testColumnInRange($pColumn);
    }

    
    public function getColumn($pColumn)
    {
        $this->testColumnInRange($pColumn);

        if (!isset($this->columns[$pColumn])) {
            $this->columns[$pColumn] = new PHPExcel_Worksheet_AutoFilter_Column($pColumn, $this);
        }

        return $this->columns[$pColumn];
    }

    
    public function getColumnByOffset($pColumnOffset = 0)
    {
        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($this->range);
        $pColumn = PHPExcel_Cell::stringFromColumnIndex($rangeStart[0] + $pColumnOffset - 1);

        return $this->getColumn($pColumn);
    }

    
    public function setColumn($pColumn)
    {
        if ((is_string($pColumn)) && (!empty($pColumn))) {
            $column = $pColumn;
        } elseif (is_object($pColumn) && ($pColumn instanceof PHPExcel_Worksheet_AutoFilter_Column)) {
            $column = $pColumn->getColumnIndex();
        } else {
            throw new PHPExcel_Exception("Column is not within the autofilter range.");
        }
        $this->testColumnInRange($column);

        if (is_string($pColumn)) {
            $this->columns[$pColumn] = new PHPExcel_Worksheet_AutoFilter_Column($pColumn, $this);
        } elseif (is_object($pColumn) && ($pColumn instanceof PHPExcel_Worksheet_AutoFilter_Column)) {
            $pColumn->setParent($this);
            $this->columns[$column] = $pColumn;
        }
        ksort($this->columns);

        return $this;
    }

    
    public function clearColumn($pColumn)
    {
        $this->testColumnInRange($pColumn);

        if (isset($this->columns[$pColumn])) {
            unset($this->columns[$pColumn]);
        }

        return $this;
    }

    
    public function shiftColumn($fromColumn = null, $toColumn = null)
    {
        $fromColumn = strtoupper($fromColumn);
        $toColumn = strtoupper($toColumn);

        if (($fromColumn !== null) && (isset($this->columns[$fromColumn])) && ($toColumn !== null)) {
            $this->columns[$fromColumn]->setParent();
            $this->columns[$fromColumn]->setColumnIndex($toColumn);
            $this->columns[$toColumn] = $this->columns[$fromColumn];
            $this->columns[$toColumn]->setParent($this);
            unset($this->columns[$fromColumn]);

            ksort($this->columns);
        }

        return $this;
    }


    
    private static function filterTestInSimpleDataSet($cellValue, $dataSet)
    {
        $dataSetValues = $dataSet['filterValues'];
        $blanks = $dataSet['blanks'];
        if (($cellValue == '') || ($cellValue === null)) {
            return $blanks;
        }
        return in_array($cellValue, $dataSetValues);
    }

    
    private static function filterTestInDateGroupSet($cellValue, $dataSet)
    {
        $dateSet = $dataSet['filterValues'];
        $blanks = $dataSet['blanks'];
        if (($cellValue == '') || ($cellValue === null)) {
            return $blanks;
        }

        if (is_numeric($cellValue)) {
            $dateValue = PHPExcel_Shared_Date::ExcelToPHP($cellValue);
            if ($cellValue < 1) {
                                $dtVal = date('His', $dateValue);
                $dateSet = $dateSet['time'];
            } elseif ($cellValue == floor($cellValue)) {
                                $dtVal = date('Ymd', $dateValue);
                $dateSet = $dateSet['date'];
            } else {
                                $dtVal = date('YmdHis', $dateValue);
                $dateSet = $dateSet['dateTime'];
            }
            foreach ($dateSet as $dateValue) {
                                if (substr($dtVal, 0, strlen($dateValue)) == $dateValue) {
                    return true;
                }
            }
        }
        return false;
    }

    
    private static function filterTestInCustomDataSet($cellValue, $ruleSet)
    {
        $dataSet = $ruleSet['filterRules'];
        $join = $ruleSet['join'];
        $customRuleForBlanks = isset($ruleSet['customRuleForBlanks']) ? $ruleSet['customRuleForBlanks'] : false;

        if (!$customRuleForBlanks) {
                        if (($cellValue == '') || ($cellValue === null)) {
                return false;
            }
        }
        $returnVal = ($join == PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_AND);
        foreach ($dataSet as $rule) {
            if (is_numeric($rule['value'])) {
                                switch ($rule['operator']) {
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_EQUAL:
                        $retVal    = ($cellValue == $rule['value']);
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_NOTEQUAL:
                        $retVal    = ($cellValue != $rule['value']);
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_GREATERTHAN:
                        $retVal    = ($cellValue > $rule['value']);
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_GREATERTHANOREQUAL:
                        $retVal    = ($cellValue >= $rule['value']);
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_LESSTHAN:
                        $retVal    = ($cellValue < $rule['value']);
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_LESSTHANOREQUAL:
                        $retVal    = ($cellValue <= $rule['value']);
                        break;
                }
            } elseif ($rule['value'] == '') {
                switch ($rule['operator']) {
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_EQUAL:
                        $retVal    = (($cellValue == '') || ($cellValue === null));
                        break;
                    case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_NOTEQUAL:
                        $retVal    = (($cellValue != '') && ($cellValue !== null));
                        break;
                    default:
                        $retVal    = true;
                        break;
                }
            } else {
                                $retVal    = preg_match('/^'.$rule['value'].'$/i', $cellValue);
            }
                        switch ($join) {
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_OR:
                    $returnVal = $returnVal || $retVal;
                                                            if ($returnVal) {
                        return $returnVal;
                    }
                    break;
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_AND:
                    $returnVal = $returnVal && $retVal;
                    break;
            }
        }

        return $returnVal;
    }

    
    private static function filterTestInPeriodDateSet($cellValue, $monthSet)
    {
                if (($cellValue == '') || ($cellValue === null)) {
            return false;
        }

        if (is_numeric($cellValue)) {
            $dateValue = date('m', PHPExcel_Shared_Date::ExcelToPHP($cellValue));
            if (in_array($dateValue, $monthSet)) {
                return true;
            }
        }

        return false;
    }

    
    private static $fromReplace = array('\*', '\?', '~~', '~.*', '~.?');
    private static $toReplace   = array('.*', '.',  '~',  '\*',  '\?');


    
    private function dynamicFilterDateRange($dynamicRuleType, &$filterColumn)
    {
        $rDateType = PHPExcel_Calculation_Functions::getReturnDateType();
        PHPExcel_Calculation_Functions::setReturnDateType(PHPExcel_Calculation_Functions::RETURNDATE_PHP_NUMERIC);
        $val = $maxVal = null;

        $ruleValues = array();
        $baseDate = PHPExcel_Calculation_DateTime::DATENOW();
                switch ($dynamicRuleType) {
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTWEEK:
                $baseDate = strtotime('-7 days', $baseDate);
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTWEEK:
                $baseDate = strtotime('-7 days', $baseDate);
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTMONTH:
                $baseDate = strtotime('-1 month', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTMONTH:
                $baseDate = strtotime('+1 month', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTQUARTER:
                $baseDate = strtotime('-3 month', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTQUARTER:
                $baseDate = strtotime('+3 month', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTYEAR:
                $baseDate = strtotime('-1 year', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTYEAR:
                $baseDate = strtotime('+1 year', gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
        }

        switch ($dynamicRuleType) {
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_TODAY:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_YESTERDAY:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_TOMORROW:
                $maxVal = (int) PHPExcel_Shared_Date::PHPtoExcel(strtotime('+1 day', $baseDate));
                $val = (int) PHPExcel_Shared_Date::PHPToExcel($baseDate);
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_YEARTODATE:
                $maxVal = (int) PHPExcel_Shared_Date::PHPtoExcel(strtotime('+1 day', $baseDate));
                $val = (int) PHPExcel_Shared_Date::PHPToExcel(gmmktime(0, 0, 0, 1, 1, date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_THISYEAR:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTYEAR:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTYEAR:
                $maxVal = (int) PHPExcel_Shared_Date::PHPToExcel(gmmktime(0, 0, 0, 31, 12, date('Y', $baseDate)));
                ++$maxVal;
                $val = (int) PHPExcel_Shared_Date::PHPToExcel(gmmktime(0, 0, 0, 1, 1, date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_THISQUARTER:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTQUARTER:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTQUARTER:
                $thisMonth = date('m', $baseDate);
                $thisQuarter = floor(--$thisMonth / 3);
                $maxVal = (int) PHPExcel_Shared_Date::PHPtoExcel(gmmktime(0, 0, 0, date('t', $baseDate), (1+$thisQuarter)*3, date('Y', $baseDate)));
                ++$maxVal;
                $val = (int) PHPExcel_Shared_Date::PHPToExcel(gmmktime(0, 0, 0, 1, 1+$thisQuarter*3, date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_THISMONTH:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTMONTH:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTMONTH:
                $maxVal = (int) PHPExcel_Shared_Date::PHPtoExcel(gmmktime(0, 0, 0, date('t', $baseDate), date('m', $baseDate), date('Y', $baseDate)));
                ++$maxVal;
                $val = (int) PHPExcel_Shared_Date::PHPToExcel(gmmktime(0, 0, 0, 1, date('m', $baseDate), date('Y', $baseDate)));
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_THISWEEK:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_LASTWEEK:
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_NEXTWEEK:
                $dayOfWeek = date('w', $baseDate);
                $val = (int) PHPExcel_Shared_Date::PHPToExcel($baseDate) - $dayOfWeek;
                $maxVal = $val + 7;
                break;
        }

        switch ($dynamicRuleType) {
                        case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_YESTERDAY:
                --$maxVal;
                --$val;
                break;
            case PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_TOMORROW:
                ++$maxVal;
                ++$val;
                break;
        }

                $filterColumn->setAttributes(array('val' => $val, 'maxVal' => $maxVal));

                $ruleValues[] = array('operator' => PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_GREATERTHANOREQUAL, 'value' => $val);
        $ruleValues[] = array('operator' => PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_LESSTHAN, 'value' => $maxVal);
        PHPExcel_Calculation_Functions::setReturnDateType($rDateType);

        return array('method' => 'filterTestInCustomDataSet', 'arguments' => array('filterRules' => $ruleValues, 'join' => PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_AND));
    }

    private function calculateTopTenValue($columnID, $startRow, $endRow, $ruleType, $ruleValue)
    {
        $range = $columnID.$startRow.':'.$columnID.$endRow;
        $dataValues = PHPExcel_Calculation_Functions::flattenArray($this->workSheet->rangeToArray($range, null, true, false));

        $dataValues = array_filter($dataValues);
        if ($ruleType == PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_TOP) {
            rsort($dataValues);
        } else {
            sort($dataValues);
        }

        return array_pop(array_slice($dataValues, 0, $ruleValue));
    }

    
    public function showHideRows()
    {
        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($this->range);

                $this->workSheet->getRowDimension($rangeStart[1])->setVisible(true);

        $columnFilterTests = array();
        foreach ($this->columns as $columnID => $filterColumn) {
            $rules = $filterColumn->getRules();
            switch ($filterColumn->getFilterType()) {
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_FILTER:
                    $ruleValues = array();
                                        foreach ($rules as $rule) {
                        $ruleType = $rule->getRuleType();
                        $ruleValues[] = $rule->getValue();
                    }
                                        $blanks = false;
                    $ruleDataSet = array_filter($ruleValues);
                    if (count($ruleValues) != count($ruleDataSet)) {
                        $blanks = true;
                    }
                    if ($ruleType == PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_FILTER) {
                                                $columnFilterTests[$columnID] = array(
                            'method' => 'filterTestInSimpleDataSet',
                            'arguments' => array('filterValues' => $ruleDataSet, 'blanks' => $blanks)
                        );
                    } else {
                                                $arguments = array(
                            'date' => array(),
                            'time' => array(),
                            'dateTime' => array(),
                        );
                        foreach ($ruleDataSet as $ruleValue) {
                            $date = $time = '';
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_YEAR])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_YEAR] !== '')) {
                                $date .= sprintf('%04d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_YEAR]);
                            }
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MONTH])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MONTH] != '')) {
                                $date .= sprintf('%02d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MONTH]);
                            }
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_DAY])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_DAY] !== '')) {
                                $date .= sprintf('%02d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_DAY]);
                            }
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_HOUR])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_HOUR] !== '')) {
                                $time .= sprintf('%02d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_HOUR]);
                            }
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MINUTE])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MINUTE] !== '')) {
                                $time .= sprintf('%02d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_MINUTE]);
                            }
                            if ((isset($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_SECOND])) &&
                                ($ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_SECOND] !== '')) {
                                $time .= sprintf('%02d', $ruleValue[PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DATEGROUP_SECOND]);
                            }
                            $dateTime = $date . $time;
                            $arguments['date'][] = $date;
                            $arguments['time'][] = $time;
                            $arguments['dateTime'][] = $dateTime;
                        }
                                                $arguments['date'] = array_filter($arguments['date']);
                        $arguments['time'] = array_filter($arguments['time']);
                        $arguments['dateTime'] = array_filter($arguments['dateTime']);
                        $columnFilterTests[$columnID] = array(
                            'method' => 'filterTestInDateGroupSet',
                            'arguments' => array('filterValues' => $arguments, 'blanks' => $blanks)
                        );
                    }
                    break;
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_CUSTOMFILTER:
                    $customRuleForBlanks = false;
                    $ruleValues = array();
                                        foreach ($rules as $rule) {
                        $ruleType = $rule->getRuleType();
                        $ruleValue = $rule->getValue();
                        if (!is_numeric($ruleValue)) {
                                                        $ruleValue = preg_quote($ruleValue);
                            $ruleValue = str_replace(self::$fromReplace, self::$toReplace, $ruleValue);
                            if (trim($ruleValue) == '') {
                                $customRuleForBlanks = true;
                                $ruleValue = trim($ruleValue);
                            }
                        }
                        $ruleValues[] = array('operator' => $rule->getOperator(), 'value' => $ruleValue);
                    }
                    $join = $filterColumn->getJoin();
                    $columnFilterTests[$columnID] = array(
                        'method' => 'filterTestInCustomDataSet',
                        'arguments' => array('filterRules' => $ruleValues, 'join' => $join, 'customRuleForBlanks' => $customRuleForBlanks)
                    );
                    break;
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_DYNAMICFILTER:
                    $ruleValues = array();
                    foreach ($rules as $rule) {
                                                $dynamicRuleType = $rule->getGrouping();
                        if (($dynamicRuleType == PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_ABOVEAVERAGE) ||
                            ($dynamicRuleType == PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_BELOWAVERAGE)) {
                                                                                    $averageFormula = '=AVERAGE('.$columnID.($rangeStart[1]+1).':'.$columnID.$rangeEnd[1].')';
                            $average = PHPExcel_Calculation::getInstance()->calculateFormula($averageFormula, null, $this->workSheet->getCell('A1'));
                                                        $operator = ($dynamicRuleType === PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_DYNAMIC_ABOVEAVERAGE)
                                ? PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_GREATERTHAN
                                : PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_LESSTHAN;
                            $ruleValues[] = array('operator' => $operator,
                                                   'value' => $average
                                                 );
                            $columnFilterTests[$columnID] = array(
                                'method' => 'filterTestInCustomDataSet',
                                'arguments' => array('filterRules' => $ruleValues, 'join' => PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_OR)
                            );
                        } else {
                                                        if ($dynamicRuleType{0} == 'M' || $dynamicRuleType{0} == 'Q') {
                                                                sscanf($dynamicRuleType, '%[A-Z]%d', $periodType, $period);
                                if ($periodType == 'M') {
                                    $ruleValues = array($period);
                                } else {
                                    --$period;
                                    $periodEnd = (1+$period)*3;
                                    $periodStart = 1+$period*3;
                                    $ruleValues = range($periodStart, $periodEnd);
                                }
                                $columnFilterTests[$columnID] = array(
                                    'method' => 'filterTestInPeriodDateSet',
                                    'arguments' => $ruleValues
                                );
                                $filterColumn->setAttributes(array());
                            } else {
                                                                $columnFilterTests[$columnID] = $this->dynamicFilterDateRange($dynamicRuleType, $filterColumn);
                                break;
                            }
                        }
                    }
                    break;
                case PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_TOPTENFILTER:
                    $ruleValues = array();
                    $dataRowCount = $rangeEnd[1] - $rangeStart[1];
                    foreach ($rules as $rule) {
                                                $toptenRuleType = $rule->getGrouping();
                        $ruleValue = $rule->getValue();
                        $ruleOperator = $rule->getOperator();
                    }
                    if ($ruleOperator === PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_PERCENT) {
                        $ruleValue = floor($ruleValue * ($dataRowCount / 100));
                    }
                    if ($ruleValue < 1) {
                        $ruleValue = 1;
                    }
                    if ($ruleValue > 500) {
                        $ruleValue = 500;
                    }

                    $maxVal = $this->calculateTopTenValue($columnID, $rangeStart[1]+1, $rangeEnd[1], $toptenRuleType, $ruleValue);

                    $operator = ($toptenRuleType == PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_TOPTEN_TOP)
                        ? PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_GREATERTHANOREQUAL
                        : PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_LESSTHANOREQUAL;
                    $ruleValues[] = array('operator' => $operator, 'value' => $maxVal);
                    $columnFilterTests[$columnID] = array(
                        'method' => 'filterTestInCustomDataSet',
                        'arguments' => array('filterRules' => $ruleValues, 'join' => PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_COLUMN_JOIN_OR)
                    );
                    $filterColumn->setAttributes(array('maxVal' => $maxVal));
                    break;
            }
        }

                for ($row = $rangeStart[1]+1; $row <= $rangeEnd[1]; ++$row) {
            $result = true;
            foreach ($columnFilterTests as $columnID => $columnFilterTest) {
                $cellValue = $this->workSheet->getCell($columnID.$row)->getCalculatedValue();
                                $result = $result &&
                    call_user_func_array(
                        array('PHPExcel_Worksheet_AutoFilter', $columnFilterTest['method']),
                        array($cellValue, $columnFilterTest['arguments'])
                    );
                                if (!$result) {
                    break;
                }
            }
                        $this->workSheet->getRowDimension($row)->setVisible($result);
        }

        return $this;
    }


    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                if ($key == 'workSheet') {
                                        $this->{$key} = null;
                } else {
                    $this->{$key} = clone $value;
                }
            } elseif ((is_array($value)) && ($key == 'columns')) {
                                $this->{$key} = array();
                foreach ($value as $k => $v) {
                    $this->{$key}[$k] = clone $v;
                                        $this->{$key}[$k]->setParent($this);
                }
            } else {
                $this->{$key} = $value;
            }
        }
    }

    
    public function __toString()
    {
        return (string) $this->range;
    }
}
