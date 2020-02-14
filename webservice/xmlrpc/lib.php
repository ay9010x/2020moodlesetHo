<?php





class webservice_xmlrpc_client {

    
    protected $serverurl;

    
    protected $token;

    
    public function __construct($serverurl, $token) {
        $this->serverurl = new moodle_url($serverurl);
        $this->token = $token;
    }

    
    public function set_token($token) {
        $this->token = $token;
    }

    
    public function call($functionname, $params = array()) {
        if ($this->token) {
            $this->serverurl->param('wstoken', $this->token);
        }

                $outputoptions = array(
            'encoding' => 'utf-8'
        );

                        $params = array_values($params);
        $request = xmlrpc_encode_request($functionname, $params, $outputoptions);

                $headers = array(
            'Content-Length' => strlen($request),
            'Content-Type' => 'text/xml; charset=utf-8',
            'Host' => $this->serverurl->get_host(),
            'User-Agent' => 'Moodle XML-RPC Client/1.0',
        );

                $response = download_file_content($this->serverurl, $headers, $request);

                $result = xmlrpc_decode($response);
        if (is_array($result) && xmlrpc_is_fault($result)) {
            throw new Exception($result['faultString'], $result['faultCode']);
        }

        return $result;
    }
}
