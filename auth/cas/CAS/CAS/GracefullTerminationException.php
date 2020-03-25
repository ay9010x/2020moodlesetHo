<?php





class CAS_GracefullTerminationException
extends RuntimeException
implements CAS_Exception
{

    
    public function __construct ($message = 'Terminate Gracefully', $code = 0)
    {
                if (self::$_exitWhenThrown) {
            exit;
        } else {
                        parent::__construct($message, $code);
        }
    }

    private static $_exitWhenThrown = true;
    
    public static function throwInsteadOfExiting()
    {
        self::$_exitWhenThrown = false;
    }

}
?>