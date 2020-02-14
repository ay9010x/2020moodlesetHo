<?php




namespace ltiservice_toolsettings\local\resource;

use ltiservice_toolsettings\local\resource\systemsettings;
use ltiservice_toolsettings\local\service\toolsettings;

defined('MOODLE_INTERNAL') || die();


class contextsettings extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolProxyBindingSettings';
        $this->template = '/{context_type}/{context_id}/bindings/{vendor_code}/{product_code}';
        $this->variables[] = 'ToolProxyBinding.custom.url';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolsettings+json';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolsettings.simple+json';
        $this->methods[] = 'GET';
        $this->methods[] = 'PUT';

    }

    
    public function execute($response) {

        $params = $this->parse_template();
        $contexttype = $params['context_type'];
        $contextid = $params['context_id'];
        $vendorcode = $params['vendor_code'];
        $productcode = $params['product_code'];
        $bubble = optional_param('bubble', '', PARAM_ALPHA);
        $ok = !empty($contexttype) && !empty($contextid) &&
              !empty($vendorcode) && !empty($productcode) &&
              $this->check_tool_proxy($productcode, $response->get_request_data());
        if (!$ok) {
            $response->set_code(401);
        }
        $contenttype = $response->get_accept();
        $simpleformat = !empty($contenttype) && ($contenttype == $this->formats[1]);
        if ($ok) {
            $ok = (empty($bubble) || ((($bubble == 'distinct') || ($bubble == 'all')))) &&
                 (!$simpleformat || empty($bubble) || ($bubble != 'all')) &&
                 (empty($bubble) || ($response->get_request_method() == 'GET'));
        }

        if (!$ok) {
            $response->set_code(404);
        } else {
            $systemsetting = null;
            $contextsettings = lti_get_tool_settings($this->get_service()->get_tool_proxy()->id, $contextid);
            if (!empty($bubble)) {
                $systemsetting = new systemsettings($this->get_service());
                $systemsetting->params['tool_proxy_id'] = $productcode;
                $systemsettings = lti_get_tool_settings($this->get_service()->get_tool_proxy()->id);
                if ($bubble == 'distinct') {
                    toolsettings::distinct_settings($systemsettings, $contextsettings, null);
                }
            } else {
                $systemsettings = null;
            }
            if ($response->get_request_method() == 'GET') {
                $json = '';
                if ($simpleformat) {
                    $response->set_content_type($this->formats[1]);
                    $json .= "{";
                } else {
                    $response->set_content_type($this->formats[0]);
                    $json .= "{\n  \"@context\":\"http://purl.imsglobal.org/ctx/lti/v2/ToolSettings\",\n  \"@graph\":[\n";
                }
                $settings = toolsettings::settings_to_json($systemsettings, $simpleformat, 'ToolProxy', $systemsetting);
                $json .= $settings;
                $isfirst = strlen($settings) <= 0;
                $settings = toolsettings::settings_to_json($contextsettings, $simpleformat, 'ToolProxyBinding', $this);
                if ((strlen($settings) > 0) && !$isfirst) {
                    $json .= ",";
                }
                $json .= $settings;
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
                              ($json->{"@graph"}[0]->{"@type"} == 'ToolProxyBinding');
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
                    lti_set_tool_settings($settings, $this->get_service()->get_tool_proxy()->id, $contextid);
                } else {
                    $response->set_code(406);
                }
            }
        }
    }

    
    public function parse_value($value) {
        global $COURSE;

        if ($COURSE->format == 'site') {
            $this->params['context_type'] = 'Group';
        } else {
            $this->params['context_type'] = 'CourseSection';
        }
        $this->params['context_id'] = $COURSE->id;
        $this->params['vendor_code'] = $this->get_service()->get_tool_proxy()->vendorcode;
        $this->params['product_code'] = $this->get_service()->get_tool_proxy()->guid;
        $value = str_replace('$ToolProxyBinding.custom.url', parent::get_endpoint(), $value);

        return $value;

    }

}
