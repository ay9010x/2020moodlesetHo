<?php




class CAS_TypeMismatchException
extends CAS_InvalidArgumentException
{
    
    public function __construct (
        $argument, $argumentName, $type, $message = '', $code = 0
    ) {
        if (is_object($argument)) {
            $foundType = get_class($argument).' object';
        } else {
            $foundType = gettype($argument);
        }

        parent::__construct(
            'type mismatched for parameter '
            . $argumentName . ' (should be \'' . $type .' \'), '
            . $foundType . ' given. ' . $message, $code
        );
    }
}
?>
