<?php






class PHPExcel_Calculation_FormulaToken
{
    
    const TOKEN_TYPE_NOOP            = 'Noop';
    const TOKEN_TYPE_OPERAND         = 'Operand';
    const TOKEN_TYPE_FUNCTION        = 'Function';
    const TOKEN_TYPE_SUBEXPRESSION   = 'Subexpression';
    const TOKEN_TYPE_ARGUMENT        = 'Argument';
    const TOKEN_TYPE_OPERATORPREFIX  = 'OperatorPrefix';
    const TOKEN_TYPE_OPERATORINFIX   = 'OperatorInfix';
    const TOKEN_TYPE_OPERATORPOSTFIX = 'OperatorPostfix';
    const TOKEN_TYPE_WHITESPACE      = 'Whitespace';
    const TOKEN_TYPE_UNKNOWN         = 'Unknown';

    
    const TOKEN_SUBTYPE_NOTHING       = 'Nothing';
    const TOKEN_SUBTYPE_START         = 'Start';
    const TOKEN_SUBTYPE_STOP          = 'Stop';
    const TOKEN_SUBTYPE_TEXT          = 'Text';
    const TOKEN_SUBTYPE_NUMBER        = 'Number';
    const TOKEN_SUBTYPE_LOGICAL       = 'Logical';
    const TOKEN_SUBTYPE_ERROR         = 'Error';
    const TOKEN_SUBTYPE_RANGE         = 'Range';
    const TOKEN_SUBTYPE_MATH          = 'Math';
    const TOKEN_SUBTYPE_CONCATENATION = 'Concatenation';
    const TOKEN_SUBTYPE_INTERSECTION  = 'Intersection';
    const TOKEN_SUBTYPE_UNION         = 'Union';

    
    private $value;

    
    private $tokenType;

    
    private $tokenSubType;

    
    public function __construct($pValue, $pTokenType = PHPExcel_Calculation_FormulaToken::TOKEN_TYPE_UNKNOWN, $pTokenSubType = PHPExcel_Calculation_FormulaToken::TOKEN_SUBTYPE_NOTHING)
    {
                $this->value       = $pValue;
        $this->tokenType    = $pTokenType;
        $this->tokenSubType = $pTokenSubType;
    }

    
    public function getValue()
    {
        return $this->value;
    }

    
    public function setValue($value)
    {
        $this->value = $value;
    }

    
    public function getTokenType()
    {
        return $this->tokenType;
    }

    
    public function setTokenType($value = PHPExcel_Calculation_FormulaToken::TOKEN_TYPE_UNKNOWN)
    {
        $this->tokenType = $value;
    }

    
    public function getTokenSubType()
    {
        return $this->tokenSubType;
    }

    
    public function setTokenSubType($value = PHPExcel_Calculation_FormulaToken::TOKEN_SUBTYPE_NOTHING)
    {
        $this->tokenSubType = $value;
    }
}
