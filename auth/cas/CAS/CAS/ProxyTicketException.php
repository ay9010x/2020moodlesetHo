<?php




class CAS_ProxyTicketException
extends BadMethodCallException
implements CAS_Exception
{

    
    public function __construct ($message, $code = PHPCAS_SERVICE_PT_FAILURE)
    {
                $ptCodes = array(
        PHPCAS_SERVICE_PT_FAILURE,
        PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE,
        PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE,
        );
        if (!in_array($code, $ptCodes)) {
            trigger_error(
                'Invalid code '.$code
                .' passed. Must be one of PHPCAS_SERVICE_PT_FAILURE, PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE, or PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE.'
            );
        }

        parent::__construct($message, $code);
    }
}
