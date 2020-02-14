<?php



class Horde_Imap_Client_Password_Xoauth2
implements Horde_Imap_Client_Base_Password
{
    
    public $access_token;

    
    public $username;

    
    public function __construct($username, $access_token)
    {
        $this->username = $username;
        $this->access_token = $access_token;
    }

    
    public function getPassword()
    {
                        return base64_encode(
            'user=' . $this->username . "\1" .
            'auth=Bearer ' . $this->access_token . "\1\1"
        );
    }

}
