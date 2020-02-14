<?php




class Mustache_Exception_SyntaxException extends LogicException implements Mustache_Exception
{
    protected $token;

    
    public function __construct($msg, array $token)
    {
        $this->token = $token;
        parent::__construct($msg);
    }

    
    public function getToken()
    {
        return $this->token;
    }
}
