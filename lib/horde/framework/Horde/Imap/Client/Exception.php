<?php



class Horde_Imap_Client_Exception extends Horde_Exception_Wrapped
{
    

    
    const UNSPECIFIED = 0;

    
    const UTF7IMAP_CONVERSION = 3;

    
    const DISCONNECT = 4;

    
    const BADCHARSET = 5;

    
    const PARSEERROR = 6;

    
    const UNKNOWNCTE = 7;

    
    const BADCOMPARATOR = 9;

    
    const MBOXNOMODSEQ = 10;

    
    const SERVER_CONNECT = 11;

    
    const SERVER_READERROR = 12;

    
    const SERVER_WRITEERROR = 16;

    
    const CATENATE_BADURL = 13;

    
    const CATENATE_TOOBIG = 14;

    
    const USEATTR = 15;

    
    const NOPERM = 17;

    
    const INUSE = 18;

    
    const CORRUPTION = 19;

    
    const LIMIT = 20;

    
    const OVERQUOTA = 21;

    
    const ALREADYEXISTS = 22;

    
    const NONEXISTENT = 23;

    
    const METADATA_MAXSIZE = 24;

    
    const METADATA_TOOMANY = 25;

    
    const METADATA_NOPRIVATE = 26;

    
    const METADATA_INVALID = 27;


    
    
    const LOGIN_TLSFAILURE = 100;

    
    const LOGIN_NOAUTHMETHOD = 101;

    
    const LOGIN_AUTHENTICATIONFAILED = 102;

    
    const LOGIN_UNAVAILABLE = 103;

    
    const LOGIN_AUTHORIZATIONFAILED = 104;

    
    const LOGIN_EXPIRED = 105;

    
    const LOGIN_PRIVACYREQUIRED = 106;


    
    
    const MAILBOX_NOOPEN = 200;

    
    const MAILBOX_READONLY = 201;


    
    
    const POP3_TEMP_ERROR = 300;

    
    const POP3_PERM_ERROR = 301;


    
    
    const NOT_SUPPORTED = 400;


    
    public $raw_msg = '';

    
    public function __construct($message = null, $code = null)
    {
        parent::__construct($message, $code);

        $this->raw_msg = $this->message;
        $this->message = Horde_Imap_Client_Translation::t($this->message);
    }

    
    public function setMessage($msg)
    {
        $this->message = strval($msg);
    }

    
    public function setCode($code)
    {
        $this->code = intval($code);
    }

}
