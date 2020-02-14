<?php




namespace mod_lti\local\ltiservice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/locallib.php');



abstract class resource_base {

    
    private $service;
    
    protected $type;
    
    protected $id;
    
    protected $template;
    
    protected $variables;
    
    protected $formats;
    
    protected $methods;
    
    protected $params;


    
    public function __construct($service) {

        $this->service = $service;
        $this->type = 'RestService';
        $this->id = null;
        $this->template = null;
        $this->methods = array();
        $this->variables = array();
        $this->formats = array();
        $this->methods = array();
        $this->params = null;

    }

    
    public function get_id() {

        return $this->id;

    }

    
    public function get_template() {

        return $this->template;

    }

    
    public function get_path() {

        return $this->get_template();

    }

    
    public function get_type() {

        return $this->type;

    }

    
    public function get_service() {

        return $this->service;

    }

    
    public function get_methods() {

        return $this->methods;

    }

    
    public function get_formats() {

        return $this->formats;

    }

    
    public function get_variables() {

        return $this->variables;

    }

    
    public function get_endpoint() {

        $this->parse_template();
        $url = $this->get_service()->get_service_path() . $this->get_template();
        foreach ($this->params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }
        $toolproxy = $this->get_service()->get_tool_proxy();
        if (!empty($toolproxy)) {
            $url = str_replace('{tool_proxy_id}', $toolproxy->guid, $url);
        }

        return $url;

    }

    
    public abstract function execute($response);

    
    public function check_tool_proxy($toolproxyguid, $body = null) {

        $ok = false;
        if ($this->get_service()->check_tool_proxy($toolproxyguid, $body)) {
            $toolproxyjson = $this->get_service()->get_tool_proxy()->toolproxy;
            if (empty($toolproxyjson)) {
                $ok = true;
            } else {
                $toolproxy = json_decode($toolproxyjson);
                if (!empty($toolproxy) && isset($toolproxy->security_contract->tool_service)) {
                    $contexts = lti_get_contexts($toolproxy);
                    $tpservices = $toolproxy->security_contract->tool_service;
                    foreach ($tpservices as $service) {
                        $fqid = lti_get_fqid($contexts, $service->service);
                        $id = explode('#', $fqid, 2);
                        if ($this->get_id() === $id[1]) {
                            $ok = true;
                            break;
                        }
                    }
                }
                if (!$ok) {
                    debugging('Requested service not included in tool proxy: ' . $this->get_id());
                }
            }
        }

        return $ok;

    }

    
    public function parse_value($value) {

        return $value;

    }

    
    protected function parse_template() {

        if (empty($this->params)) {
            $this->params = array();
            if (isset($_SERVER['PATH_INFO'])) {
                $path = explode('/', $_SERVER['PATH_INFO']);
                $parts = explode('/', $this->get_template());
                for ($i = 0; $i < count($parts); $i++) {
                    if ((substr($parts[$i], 0, 1) == '{') && (substr($parts[$i], -1) == '}')) {
                        $value = '';
                        if ($i < count($path)) {
                            $value = $path[$i];
                        }
                        $this->params[substr($parts[$i], 1, -1)] = $value;
                    }
                }
            }
        }

        return $this->params;

    }

}
