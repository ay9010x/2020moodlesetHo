<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');


class moodle_google_curlio extends Google_IO_Curl {

    
    private static $constants = null;

    
    private $options = array();

    
    private function do_request($curl, $request) {
        $url = $request->getUrl();
        $method = $request->getRequestMethod();
        switch (strtoupper($method)) {
            case 'POST':
                $ret = $curl->post($url, $request->getPostBody());
                break;
            case 'GET':
                $ret = $curl->get($url);
                break;
            case 'HEAD':
                $ret = $curl->head($url);
                break;
            case 'PUT':
                $ret = $curl->put($url);
                break;
            default:
                throw new coding_exception('Unknown request type: ' . $method);
                break;
        }
        return $ret;
    }

    
    public function executeRequest(Google_Http_Request $request) {
        $curl = new curl();

        if ($request->getPostBody()) {
            $curl->setopt(array('CURLOPT_POSTFIELDS' => $request->getPostBody()));
        }

        $requestHeaders = $request->getRequestHeaders();
        if ($requestHeaders && is_array($requestHeaders)) {
            $curlHeaders = array();
            foreach ($requestHeaders as $k => $v) {
                $curlHeaders[] = "$k: $v";
            }
            $curl->setopt(array('CURLOPT_HTTPHEADER' => $curlHeaders));
        }

        $curl->setopt(array('CURLOPT_URL' => $request->getUrl()));

        $curl->setopt(array('CURLOPT_CUSTOMREQUEST' => $request->getRequestMethod()));
        $curl->setopt(array('CURLOPT_USERAGENT' => $request->getUserAgent()));

        $curl->setopt(array('CURLOPT_FOLLOWLOCATION' => false));
        $curl->setopt(array('CURLOPT_SSL_VERIFYPEER' => true));
        $curl->setopt(array('CURLOPT_RETURNTRANSFER' => true));
        $curl->setopt(array('CURLOPT_HEADER' => true));

        if ($request->canGzip()) {
            $curl->setopt(array('CURLOPT_ENCODING' => 'gzip,deflate'));
        }

        $curl->setopt($this->options);
        $respdata = $this->do_request($curl, $request);

        $infos = $curl->get_info();
        $respheadersize = $infos['header_size'];
        $resphttpcode = (int) $infos['http_code'];
        $curlerrornum = $curl->get_errno();
        $curlerror = $curl->error;

        if ($respdata != CURLE_OK) {
            throw new Google_IO_Exception($curlerror);
        }

        list($responseHeaders, $responseBody) = $this->parseHttpResponse($respdata, $respheadersize);
        return array($responseBody, $responseHeaders, $resphttpcode);
    }

    
    public function setOptions($optparams) {
        $safeparams = array();
        foreach ($optparams as $name => $value) {
            if (!is_string($name)) {
                $name = $this->get_option_name_from_constant($name);
            }
            $safeparams[$name] = $value;
        }
        $this->options = $options + $this->options;
    }

    
    public function setTimeout($timeout) {
                                        $this->options['CURLOPT_CONNECTTIMEOUT'] = $timeout;
        $this->options['CURLOPT_TIMEOUT'] = $timeout;
    }

    
    public function getTimeout() {
       return $this->options['CURLOPT_TIMEOUT'];
    }

    
    public function get_option_name_from_constant($constant) {
        if (is_null(self::$constants)) {
            $constants = get_defined_constants(true);
            $constants = isset($constants['curl']) ? $constants['curl'] : array();
            $constants = array_flip($constants);
            self::$constants = $constants;
        }
        if (isset(self::$constants[$constant])) {
            return self::$constants[$constant];
        }
        throw new coding_exception('Unknown curl constant value: ' . $constant);
    }

}
