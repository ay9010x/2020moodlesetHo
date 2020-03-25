<?php




require_once("$CFG->dirroot/webservice/lib.php");


class webservice_rest_server extends webservice_base_server {

    
    protected $restformat;

    
    public function __construct($authmethod) {
        parent::__construct($authmethod);
        $this->wsname = 'rest';
    }

    
    protected function parse_request() {

                parent::set_web_service_call_settings();

                $methodvariables = array_merge($_GET, $_POST);

                $restformatisset = isset($methodvariables['moodlewsrestformat'])
                && (($methodvariables['moodlewsrestformat'] == 'xml' || $methodvariables['moodlewsrestformat'] == 'json'));
        $this->restformat = $restformatisset ? $methodvariables['moodlewsrestformat'] : 'xml';
        unset($methodvariables['moodlewsrestformat']);

        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = isset($methodvariables['wsusername']) ? $methodvariables['wsusername'] : null;
            unset($methodvariables['wsusername']);

            $this->password = isset($methodvariables['wspassword']) ? $methodvariables['wspassword'] : null;
            unset($methodvariables['wspassword']);

            $this->functionname = isset($methodvariables['wsfunction']) ? $methodvariables['wsfunction'] : null;
            unset($methodvariables['wsfunction']);

            $this->parameters = $methodvariables;

        } else {
            $this->token = isset($methodvariables['wstoken']) ? $methodvariables['wstoken'] : null;
            unset($methodvariables['wstoken']);

            $this->functionname = isset($methodvariables['wsfunction']) ? $methodvariables['wsfunction'] : null;
            unset($methodvariables['wsfunction']);

            $this->parameters = $methodvariables;
        }
    }

    
    protected function send_response() {

                try {
            if ($this->function->returns_desc != null) {
                $validatedvalues = external_api::clean_returnvalue($this->function->returns_desc, $this->returns);
            } else {
                $validatedvalues = null;
            }
        } catch (Exception $ex) {
            $exception = $ex;
        }

        if (!empty($exception)) {
            $response =  $this->generate_error($exception);
        } else {
                        if ($this->restformat == 'json') {
                $response = json_encode($validatedvalues);
            } else {
                $response = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
                $response .= '<RESPONSE>'."\n";
                $response .= self::xmlize_result($validatedvalues, $this->function->returns_desc);
                $response .= '</RESPONSE>'."\n";
            }
        }

        $this->send_headers();
        echo $response;
    }

    
    protected function send_error($ex=null) {
        $this->send_headers();
        echo $this->generate_error($ex);
    }

    
    protected function generate_error($ex) {
        if ($this->restformat == 'json') {
            $errorobject = new stdClass;
            $errorobject->exception = get_class($ex);
            $errorobject->errorcode = $ex->errorcode;
            $errorobject->message = $ex->getMessage();
            if (debugging() and isset($ex->debuginfo)) {
                $errorobject->debuginfo = $ex->debuginfo;
            }
            $error = json_encode($errorobject);
        } else {
            $error = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
            $error .= '<EXCEPTION class="'.get_class($ex).'">'."\n";
            $error .= '<ERRORCODE>' . htmlspecialchars($ex->errorcode, ENT_COMPAT, 'UTF-8')
                    . '</ERRORCODE>' . "\n";
            $error .= '<MESSAGE>'.htmlspecialchars($ex->getMessage(), ENT_COMPAT, 'UTF-8').'</MESSAGE>'."\n";
            if (debugging() and isset($ex->debuginfo)) {
                $error .= '<DEBUGINFO>'.htmlspecialchars($ex->debuginfo, ENT_COMPAT, 'UTF-8').'</DEBUGINFO>'."\n";
            }
            $error .= '</EXCEPTION>'."\n";
        }
        return $error;
    }

    
    protected function send_headers() {
        if ($this->restformat == 'json') {
            header('Content-type: application/json');
        } else {
            header('Content-Type: application/xml; charset=utf-8');
            header('Content-Disposition: inline; filename="response.xml"');
        }
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
                        header('Access-Control-Allow-Origin: *');
    }

    
    protected static function xmlize_result($returns, $desc) {
        if ($desc === null) {
            return '';

        } else if ($desc instanceof external_value) {
            if (is_bool($returns)) {
                                $returns = (int)$returns;
            }
            if (is_null($returns)) {
                return '<VALUE null="null"/>'."\n";
            } else {
                return '<VALUE>'.htmlspecialchars($returns, ENT_COMPAT, 'UTF-8').'</VALUE>'."\n";
            }

        } else if ($desc instanceof external_multiple_structure) {
            $mult = '<MULTIPLE>'."\n";
            if (!empty($returns)) {
                foreach ($returns as $val) {
                    $mult .= self::xmlize_result($val, $desc->content);
                }
            }
            $mult .= '</MULTIPLE>'."\n";
            return $mult;

        } else if ($desc instanceof external_single_structure) {
            $single = '<SINGLE>'."\n";
            foreach ($desc->keys as $key=>$subdesc) {
                $value = isset($returns[$key]) ? $returns[$key] : null;
                $single .= '<KEY name="'.$key.'">'.self::xmlize_result($value, $subdesc).'</KEY>'."\n";
            }
            $single .= '</SINGLE>'."\n";
            return $single;
        }
    }
}



class webservice_rest_test_client implements webservice_test_client_interface {
    
    public function simpletest($serverurl, $function, $params) {
        return download_file_content($serverurl.'&wsfunction='.$function, null, $params);
    }
}
