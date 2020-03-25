<?php




require_once("$CFG->dirroot/webservice/lib.php");


class webservice_xmlrpc_server extends webservice_base_server {

    
    private $response;

    
    public function __construct($authmethod) {
        parent::__construct($authmethod);
        $this->wsname = 'xmlrpc';
    }

    
    protected function parse_request() {
                parent::set_web_service_call_settings();

        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = isset($_GET['wsusername']) ? $_GET['wsusername'] : null;
            $this->password = isset($_GET['wspassword']) ? $_GET['wspassword'] : null;
        } else {
            $this->token = isset($_GET['wstoken']) ? $_GET['wstoken'] : null;
        }

                $rawpostdata = $this->fetch_input_content();
        $methodname = null;

                $decodedparams = xmlrpc_decode_request($rawpostdata, $methodname, 'UTF-8');
        $methodinfo = external_api::external_function_info($methodname);
        $methodparams = array_keys($methodinfo->parameters_desc->keys);
        $methodvariables = [];

                if (is_array($decodedparams)) {
            foreach ($decodedparams as $index => $param) {
                                                $methodvariables[$methodparams[$index]] = $param;
            }
        }

        $this->functionname = $methodname;
        $this->parameters = $methodvariables;
    }

    
    protected function fetch_input_content() {
        return file_get_contents('php://input');
    }

    
    protected function prepare_response() {
        try {
            if (!empty($this->function->returns_desc)) {
                $validatedvalues = external_api::clean_returnvalue($this->function->returns_desc, $this->returns);
                $encodingoptions = array(
                    "encoding" => "UTF-8",
                    "verbosity" => "no_white_space",
                                        "escaping" => ["markup"]
                );
                                $this->response = xmlrpc_encode_request(null, $validatedvalues, $encodingoptions);
            }
        } catch (invalid_response_exception $ex) {
            $this->response = $this->generate_error($ex);
        }
    }

    
    protected function send_response() {
        $this->prepare_response();
        $this->send_headers();
        echo $this->response;
    }

    
    protected function send_error($ex = null) {
        $this->send_headers();
        echo $this->generate_error($ex);
    }

    
    protected function send_headers() {
                header('HTTP/1.1 200 OK');
        header('Connection: close');
        header('Content-Length: ' . strlen($this->response));
        header('Content-Type: text/xml; charset=utf-8');
        header('Date: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header('Server: Moodle XML-RPC Server/1.0');
                header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
                        header('Access-Control-Allow-Origin: *');
    }

    
    protected function generate_error(Exception $ex, $faultcode = 404) {
        $error = $ex->getMessage();

        if (!empty($ex->errorcode)) {
                        $faultcode = base_convert(md5($ex->errorcode), 16, 10);

                                                $faultcode = substr($faultcode, 0, 8);

                        if (debugging() and isset($ex->debuginfo)) {
                $error .= ' | DEBUG INFO: ' . $ex->debuginfo . ' | ERRORCODE: ' . $ex->errorcode;
            } else {
                $error .= ' | ERRORCODE: ' . $ex->errorcode;
            }
        }

        $fault = array(
            'faultCode' => (int) $faultcode,
            'faultString' => $error
        );

        $encodingoptions = array(
            "encoding" => "UTF-8",
            "verbosity" => "no_white_space",
                        "escaping" => ["markup"]
        );

        return xmlrpc_encode_request(null, $fault, $encodingoptions);
    }
}


class webservice_xmlrpc_test_client implements webservice_test_client_interface {
    
    public function simpletest($serverurl, $function, $params) {
        global $CFG;

        $url = new moodle_url($serverurl);
        $token = $url->get_param('wstoken');
        require_once($CFG->dirroot . '/webservice/xmlrpc/lib.php');
        $client = new webservice_xmlrpc_client($serverurl, $token);
        return $client->call($function, $params);
    }
}
