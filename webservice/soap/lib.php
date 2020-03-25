<?php




class webservice_soap_client {

    
    private $serverurl;

    
    private $token;

    
    private $options;

    
    public function __construct($serverurl, $token = null, array $options = null) {
        $this->serverurl = new moodle_url($serverurl);
        $this->token = $token ?: $this->serverurl->get_param('wstoken');
        $this->options = $options ?: array();
    }

    
    public function set_token($token) {
        $this->token = $token;
    }

    
    public function call($functionname, $params) {
        if ($this->token) {
            $this->serverurl->param('wstoken', $this->token);
        }
        $this->serverurl->param('wsdl', 1);

        $opts = array(
            'http' => array(
                'user_agent' => 'Moodle SOAP Client'
            )
        );
        $context = stream_context_create($opts);
        $this->options['stream_context'] = $context;
        $this->options['cache_wsdl'] = WSDL_CACHE_NONE;

        $client = new SoapClient($this->serverurl->out(false), $this->options);

        return $client->__soapCall($functionname, $params);
    }
}
