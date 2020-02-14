<?php



class Horde_Imap_Client_Interaction_Server
{
    
    const BAD = 1;
    const BYE = 2;
    const NO = 3;
    const OK = 4;
    const PREAUTH = 5;

    
    protected $_checkStatus = true;

    
    public $responseCode = null;

    
    public $status = null;

    
    public $token;

    
    static public function create(Horde_Imap_Client_Tokenize $t)
    {
        $t->rewind();
        $tag = $t->next();
        $t->next();

        switch ($tag) {
        case '+':
            return new Horde_Imap_Client_Interaction_Server_Continuation($t);

        case '*':
            return new Horde_Imap_Client_Interaction_Server_Untagged($t);

        default:
            return new Horde_Imap_Client_Interaction_Server_Tagged($t, $tag);
        }
    }

    
    public function __construct(Horde_Imap_Client_Tokenize $token)
    {
        $this->token = $token;

        
        $status = $token->current();
        $valid = array('BAD', 'BYE', 'NO', 'OK', 'PREAUTH');

        if (in_array($status, $valid)) {
            $this->status = constant(__CLASS__ . '::' . $status);
            $resp_text = $token->next();

            
            if (is_string($resp_text) && ($resp_text[0] === '[')) {
                $resp = new stdClass;
                $resp->data = array();

                if ($resp_text[strlen($resp_text) - 1] === ']') {
                    $resp->code = substr($resp_text, 1, -1);
                } else {
                    $resp->code = substr($resp_text, 1);

                    while (($elt = $token->next()) !== false) {
                        if (is_string($elt) && $elt[strlen($elt) - 1] === ']') {
                            $resp->data[] = substr($elt, 0, -1);
                            break;
                        }
                        $resp->data[] = is_string($elt)
                            ? $elt
                            : $token->flushIterator();
                    }
                }

                $token->next();
                $this->responseCode = $resp;
            }
        }
    }

    
    public function __toString()
    {
        return strval($this->token);
    }

}
