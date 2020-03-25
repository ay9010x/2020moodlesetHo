<?php




namespace ltiservice_toolsettings\local\resource;

use ltiservice_toolsettings\local\service\toolsettings;

defined('MOODLE_INTERNAL') || die();


class systemsettings extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolProxySettings';
        $this->template = '/toolproxy/{tool_proxy_id}';
        $this->variables[] = 'ToolProxy.custom.url';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolsettings+json';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolsettings.simple+json';
        $this->methods[] = 'GET';
        $this->methods[] = 'PUT';

    }

    
    public function execute($response) {

        $params = $this->parse_template();
        $tpid = $params['tool_proxy_id'];
        $bubble = optional_param('bubble', '', PARAM_ALPHA);
        $ok = !empty($tpid) && $this->check_tool_proxy($tpid, $response->get_request_data());
        if (!$ok) {
            $response->set_code(401);
        }
        $contenttype = $response->get_accept();
        $simpleformat = !empty($contenttype) && ($contenttype == $this->formats[1]);
        if ($ok) {
            $ok = (empty($bubble) || ((($bubble == 'distinct') || ($bubble == 'all')))) &&
               (!$simpleformat || empty($bubble) || ($bubble != 'all')) &&
               (empty($bubble) || ($response->get_request_method() == 'GET'));
            if (!$ok) {
                $response->set_code(406);
            }
        }

        if ($ok) {
            $systemsettings = lti_get_tool_settings($this->get_service()->get_tool_proxy()->id);
            if ($response->get_request_method() == 'GET') {
                $json = '';
                if ($simpleformat) {
                    $response->set_content_type($this->formats[1]);
                    $json .= "{";
                } else {
                    $response->set_content_type($this->formats[0]);
                    $json .= "{\n  \"@context\":\"http://purl.imsglobal.org/ctx/lti/v2/ToolSettings\",\n  \"@graph\":[\n";
                }
                $json .= toolsettings::settings_to_json($systemsettings, $simpleformat,
                    'ToolProxy', $this);
                if ($simpleformat) {
                    $json .= "\n}";
                } else {
                    $json .= "\n  ]\n}";
                }
                $response->set_body($json);
            } else {                 $settings = null;
                if ($response->get_content_type() == $this->formats[0]) {
                    $json = json_decode($response->get_request_data());
                    $ok = !empty($json);
                    if ($ok) {
                        $ok = isset($json->{"@graph"}) && is_array($json->{"@graph"}) && (count($json->{"@graph"}) == 1) &&
                              ($json->{"@graph"}[0]->{"@type"} == 'ToolProxy');
                    }
                    if ($ok) {
                        $settings = $json->{"@graph"}[0]->custom;
                        unset($settings->{'@id'});
                    }
                } else {                      $json = json_decode($response->get_request_data(), true);
                    $ok = !empty($json);
                    if ($ok) {
                        $ok = is_array($json);
                    }
                    if ($ok) {
                        $settings = $json;
                    }
                }
                if ($ok) {
                    lti_set_tool_settings($settings, $this->get_service()->get_tool_proxy()->id);
                } else {
                    $response->set_code(406);
                }
            }
        }

    }

    
    public function parse_value($value) {

        $value = str_replace('$ToolProxy.custom.url', parent::get_endpoint(), $value);

        return $value;

    }

}
