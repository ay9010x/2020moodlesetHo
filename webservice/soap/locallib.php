<?php



global $CFG;
require_once($CFG->dirroot . '/webservice/lib.php');
use webservice_soap\wsdl;


class webservice_soap_server extends webservice_base_server {

    
    protected $serverurl;

    
    protected $soapserver;

    
    protected $response;

    
    protected $serviceclass;

    
    protected $wsdlmode;

    
    protected $wsdl;

    
    public function __construct($authmethod) {
        parent::__construct($authmethod);
                 ini_set('soap.wsdl_cache_enabled', '0');
        $this->wsname = 'soap';
        $this->wsdlmode = false;
    }

    
    protected function parse_request() {
                parent::set_web_service_call_settings();

        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = optional_param('wsusername', null, PARAM_RAW);
            $this->password = optional_param('wspassword', null, PARAM_RAW);

            if (!$this->username or !$this->password) {
                                $authdata = get_file_argument();
                $authdata = explode('/', trim($authdata, '/'));
                if (count($authdata) == 2) {
                    list($this->username, $this->password) = $authdata;
                }
            }
            $this->serverurl = new moodle_url('/webservice/soap/simpleserver.php/' . $this->username . '/' . $this->password);
        } else {
            $this->token = optional_param('wstoken', null, PARAM_RAW);

            $this->serverurl = new moodle_url('/webservice/soap/server.php');
            $this->serverurl->param('wstoken', $this->token);
        }

        if ($wsdl = optional_param('wsdl', 0, PARAM_INT)) {
            $this->wsdlmode = true;
        }
    }

    
    public function run() {
                raise_memory_limit(MEMORY_EXTRA);

                external_api::set_timeout();

                set_exception_handler(array($this, 'exception_handler'));

                $this->parse_request();

                $this->authenticate_user();

                $this->init_service_class();

        if ($this->wsdlmode) {
                        $this->generate_wsdl();
        }

                $params = array(
            'other' => array(
                'function' => 'unknown'
            )
        );
        $event = \core\event\webservice_function_called::create($params);
        $logdataparams = array(SITEID, 'webservice_soap', '', '', $this->serviceclass . ' ' . getremoteaddr(), 0, $this->userid);
        $event->set_legacy_logdata($logdataparams);
        $event->trigger();

                $this->handle();

                $this->session_cleanup();
        die;
    }

    
    protected function generate_wsdl() {
                $this->wsdl = new wsdl($this->serviceclass, $this->serverurl);
                foreach ($this->servicestructs as $structinfo) {
            $this->wsdl->add_complex_type($structinfo->classname, $structinfo->properties);
        }
                foreach ($this->servicemethods as $methodinfo) {
            $this->wsdl->register($methodinfo->name, $methodinfo->inputparams, $methodinfo->outputparams, $methodinfo->description);
        }
    }

    
    protected function handle() {
        if ($this->wsdlmode) {
                        $this->response = $this->wsdl->to_xml();

                        $this->send_response();
        } else {
            $wsdlurl = clone($this->serverurl);
            $wsdlurl->param('wsdl', 1);

            $options = array(
                'uri' => $this->serverurl->out(false)
            );
                        $this->soapserver = new SoapServer($wsdlurl->out(false), $options);
            if (!empty($this->serviceclass)) {
                $this->soapserver->setClass($this->serviceclass);
                                $functions = get_class_methods($this->serviceclass);
                $this->soapserver->addFunction($functions);
            }

                        $soaprequest = file_get_contents('php://input');
                        try {
                $this->soapserver->handle($soaprequest);
            } catch (Exception $e) {
                $this->fault($e);
            }
        }
    }

    
    protected function send_error($ex = null) {
        if ($ex) {
            $info = $ex->getMessage();
            if (debugging() and isset($ex->debuginfo)) {
                $info .= ' - '.$ex->debuginfo;
            }
        } else {
            $info = 'Unknown error';
        }

                $dom = new DOMDocument('1.0', 'UTF-8');

                $fault = $dom->createElement('SOAP-ENV:Fault');
                $fault->appendChild($dom->createElement('faultcode', 'MOODLE:error'));
                $fault->appendChild($dom->createElement('faultstring', $info));

                $body = $dom->createElement('SOAP-ENV:Body');
        $body->appendChild($fault);

                $envelope = $dom->createElement('SOAP-ENV:Envelope');
        $envelope->setAttribute('xmlns:SOAP-ENV', 'http://schemas.xmlsoap.org/soap/envelope/');
        $envelope->appendChild($body);
        $dom->appendChild($envelope);

        $this->response = $dom->saveXML();
        $this->send_response();
    }

    
    protected function send_response() {
        $this->send_headers();
        echo $this->response;
    }

    
    protected function send_headers() {
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: ' . gmdate('D, d M Y H:i:s', 0) . ' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
        header('Content-Length: ' . strlen($this->response));
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: inline; filename="response.xml"');
    }

    
    public function fault($fault = null, $code = 'Receiver') {
        $allowedfaultmodes = array(
            'VersionMismatch', 'MustUnderstand', 'DataEncodingUnknown',
            'Sender', 'Receiver', 'Server'
        );
        if (!in_array($code, $allowedfaultmodes)) {
            $code = 'Receiver';
        }

                $actor = null;
        $details = null;
        $errorcode = 'unknownerror';
        $message = get_string($errorcode);
        if ($fault instanceof Exception) {
                        $actor = isset($fault->errorcode) ? $fault->errorcode : null;
            $errorcode = $actor;
            if (debugging()) {
                $message = $fault->getMessage();
                $details = isset($fault->debuginfo) ? $fault->debuginfo : null;
            }
        } else if (is_string($fault)) {
            $message = $fault;
        }

        $this->soapserver->fault($code, $message . ' | ERRORCODE: ' . $errorcode, $actor, $details);
    }
}


class webservice_soap_test_client implements webservice_test_client_interface {

    
    public function simpletest($serverurl, $function, $params) {
        global $CFG;

        require_once($CFG->dirroot . '/webservice/soap/lib.php');
        $client = new webservice_soap_client($serverurl);
        return $client->call($function, $params);
    }
}
