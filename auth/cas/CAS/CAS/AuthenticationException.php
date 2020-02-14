<?php





class CAS_AuthenticationException
extends RuntimeException
implements CAS_Exception
{

    
    public function __construct($client,$failure,$cas_url,$no_response,
        $bad_response='',$cas_response='',$err_code='',$err_msg=''
    ) {
        phpCAS::traceBegin();
        $lang = $client->getLangObj();
        $client->printHTMLHeader($lang->getAuthenticationFailed());
        printf(
            $lang->getYouWereNotAuthenticated(),
            htmlentities($client->getURL()),
            isset($_SERVER['SERVER_ADMIN']) ? $_SERVER['SERVER_ADMIN']:''
        );
        phpCAS::trace('CAS URL: '.$cas_url);
        phpCAS::trace('Authentication failure: '.$failure);
        if ( $no_response ) {
            phpCAS::trace('Reason: no response from the CAS server');
        } else {
            if ( $bad_response ) {
                phpCAS::trace('Reason: bad response from the CAS server');
            } else {
                switch ($client->getServerVersion()) {
                case CAS_VERSION_1_0:
                    phpCAS::trace('Reason: CAS error');
                    break;
                case CAS_VERSION_2_0:
                case CAS_VERSION_3_0:
                    if ( empty($err_code) ) {
                        phpCAS::trace('Reason: no CAS error');
                    } else {
                        phpCAS::trace('Reason: ['.$err_code.'] CAS error: '.$err_msg);
                    }
                    break;
                }
            }
            phpCAS::trace('CAS response: '.$cas_response);
        }
        $client->printHTMLFooter();
        phpCAS::traceExit();
    }

}
?>
