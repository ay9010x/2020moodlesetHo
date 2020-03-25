<?php




namespace mod_lti\local\ltiservice;

defined('MOODLE_INTERNAL') || die;


class response {

    
    private $code;
    
    private $reason;
    
    private $requestmethod;
    
    private $accept;
    
    private $contenttype;
    
    private $data;
    
    private $body;
    
    private $responsecodes;

    
    public function __construct() {

        $this->code = 200;
        $this->reason = '';
        $this->requestmethod = $_SERVER['REQUEST_METHOD'];
        $this->accept = '';
        $this->contenttype = '';
        $this->data = '';
        $this->body = '';
        $this->responsecodes = array(
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            300 => 'Multiple Choices',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            415 => 'Unsupported Media Type',
            500 => 'Internal Server Error',
            501 => 'Not Implemented'
        );

    }

    
    public function get_code() {
        return $this->code;
    }

    
    public function set_code($code) {
        $this->code = $code;
        $this->reason = '';
    }

    
    public function get_reason() {
        if (empty($this->reason)) {
            $this->reason = $this->responsecodes[$this->code];
        }
                if (empty($this->reason)) {
            $this->reason = $this->responsecodes[intval($this->code / 100) * 100];
        }
        return $this->reason;
    }

    
    public function set_reason($reason) {
        $this->reason = $reason;
    }

    
    public function get_request_method() {
        return $this->requestmethod;
    }

    
    public function get_accept() {
        return $this->accept;
    }

    
    public function set_accept($accept) {
        $this->accept = $accept;
    }

    
    public function get_content_type() {
        return $this->contenttype;
    }

    
    public function set_content_type($contenttype) {
        $this->contenttype = $contenttype;
    }

    
    public function get_request_data() {
        return $this->data;
    }

    
    public function set_request_data($data) {
        $this->data = $data;
    }

    
    public function set_body($body) {
        $this->body = $body;
    }

    
    public function send() {
        header("HTTP/1.0 {$this->code} {$this->get_reason()}");
        if (($this->code >= 200) && ($this->code < 300)) {
            if (!empty($this->contenttype)) {
                header("Content-Type: {$this->contenttype};charset=UTF-8");
            }
            if (!empty($this->body)) {
                echo $this->body;
            }
        }
    }

}
