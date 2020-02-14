<?php




namespace ltiservice_toolproxy\local\resource;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/lti/OAuth.php');
require_once($CFG->dirroot . '/mod/lti/TrivialStore.php');

use moodle\mod\lti as lti;


class toolproxy extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolProxy.collection';
        $this->template = '/toolproxy';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolproxy+json';
        $this->methods[] = 'POST';

    }

    
    public function execute($response) {

        $ok = $this->check_tool_proxy(null, $response->get_request_data());
        if ($ok) {
            $toolproxy = $this->get_service()->get_tool_proxy();
        } else {
            $toolproxy = null;
            $response->set_code(401);
        }
        $tools = array();

                if ($ok) {
            $toolproxyjson = json_decode($response->get_request_data());
            $ok = !empty($toolproxyjson);
            if (!$ok) {
                debugging('Tool proxy is not properly formed JSON');
            } else {
                $ok = isset($toolproxyjson->tool_profile->product_instance->product_info->product_family->vendor->code);
                $ok = $ok && isset($toolproxyjson->security_contract->shared_secret);
                $ok = $ok && isset($toolproxyjson->tool_profile->resource_handler);
                if (!$ok) {
                    debugging('One or more missing elements from tool proxy: vendor code, shared secret or resource handlers');
                }
            }
        }

                if ($ok) {
            $offeredcapabilities = explode("\n", $toolproxy->capabilityoffered);
            $resources = $toolproxyjson->tool_profile->resource_handler;
            $errors = array();
            foreach ($resources as $resource) {
                if (isset($resource->message)) {
                    foreach ($resource->message as $message) {
                        if (!in_array($message->message_type, $offeredcapabilities)) {
                            $errors[] = $message->message_type;
                        } else if (isset($resource->parameter)) {
                            foreach ($message->parameter as $parameter) {
                                if (isset($parameter->variable) && !in_array($parameter->variable, $offeredcapabilities)) {
                                    $errors[] = $parameter->variable;
                                }
                            }
                        }
                    }
                }
            }
            if (count($errors) > 0) {
                $ok = false;
                debugging('Tool proxy contains capabilities which were not offered: ' . implode(', ', $errors));
            }
        }

                if ($ok && isset($toolproxyjson->security_contract->tool_service)) {
            $contexts = lti_get_contexts($toolproxyjson);
            $profileservice = lti_get_service_by_name('profile');
            $profileservice->set_tool_proxy($toolproxy);
            $context = $profileservice->get_service_path() . $profileservice->get_resources()[0]->get_path() . '#';
            $offeredservices = explode("\n", $toolproxy->serviceoffered);
            $services = lti_get_services();
            $tpservices = $toolproxyjson->security_contract->tool_service;
            $errors = array();
            foreach ($tpservices as $service) {
                $fqid = lti_get_fqid($contexts, $service->service);
                if (substr($fqid, 0, strlen($context)) !== $context) {
                    $errors[] = $service->service;
                } else {
                    $id = explode('#', $fqid, 2);
                    $aservice = lti_get_service_by_resource_id($services, $id[1]);
                    $classname = explode('\\', get_class($aservice));
                    if (empty($aservice) || !in_array($classname[count($classname) - 1], $offeredservices)) {
                        $errors[] = $service->service;
                    }
                }
            }
            if (count($errors) > 0) {
                $ok = false;
                debugging('Tool proxy contains services which were not offered: ' . implode(', ', $errors));
            }
        }

                if ($ok) {
            $resources = $toolproxyjson->tool_profile->resource_handler;
            foreach ($resources as $resource) {
                $found = false;
                $tool = new \stdClass();

                $iconinfo = null;
                if (is_array($resource->icon_info)) {
                    $iconinfo = $resource->icon_info[0];
                } else {
                    $iconinfo = $resource->icon_info;
                }
                if (isset($iconinfo) && isset($iconinfo->default_location) && isset($iconinfo->default_location->path)) {
                    $tool->iconpath = $iconinfo->default_location->path;
                }

                foreach ($resource->message as $message) {
                    if ($message->message_type == 'basic-lti-launch-request') {
                        $found = true;
                        $tool->path = $message->path;
                        $tool->enabled_capability = $message->enabled_capability;
                        $tool->parameter = $message->parameter;
                        break;
                    }
                }
                if (!$found) {
                    continue;
                }

                $tool->name = $resource->resource_name->default_value;
                $tools[] = $tool;
            }
            $ok = count($tools) > 0;
            if (!$ok) {
                debugging('No launchable messages found in tool proxy');
            }
        }

                if ($ok) {
            $baseurl = '';
            if (isset($toolproxyjson->tool_profile->base_url_choice[0]->default_base_url)) {
                $baseurl = $toolproxyjson->tool_profile->base_url_choice[0]->default_base_url;
            }
            $securebaseurl = '';
            if (isset($toolproxyjson->tool_profile->base_url_choice[0]->secure_base_url)) {
                $securebaseurl = $toolproxyjson->tool_profile->base_url_choice[0]->secure_base_url;
            }
            foreach ($tools as $tool) {
                $config = new \stdClass();
                $config->lti_toolurl = "{$baseurl}{$tool->path}";
                $config->lti_typename = $tool->name;
                $config->lti_coursevisible = 1;
                $config->lti_forcessl = 0;

                $type = new \stdClass();
                $type->state = LTI_TOOL_STATE_PENDING;
                $type->toolproxyid = $toolproxy->id;
                $type->enabledcapability = implode("\n", $tool->enabled_capability);
                $type->parameter = self::lti_extract_parameters($tool->parameter);

                if (!empty($tool->iconpath)) {
                    $type->icon = "{$baseurl}{$tool->iconpath}";
                    if (!empty($securebaseurl)) {
                        $type->secureicon = "{$securebaseurl}{$tool->iconpath}";
                    }
                }

                $ok = $ok && (lti_add_type($type, $config) !== false);
            }
            if (isset($toolproxyjson->custom)) {
                lti_set_tool_settings($toolproxyjson->custom, $toolproxy->id);
            }
        }

        if (!empty($toolproxy)) {
            if ($ok) {
                                $toolproxy->state = LTI_TOOL_PROXY_STATE_ACCEPTED;
                $toolproxy->toolproxy = $response->get_request_data();
                $toolproxy->secret = $toolproxyjson->security_contract->shared_secret;
                $toolproxy->vendorcode = $toolproxyjson->tool_profile->product_instance->product_info->product_family->vendor->code;

                $url = $this->get_endpoint();
                $body = <<< EOD
{
  "@context" : "http://purl.imsglobal.org/ctx/lti/v2/ToolProxyId",
  "@type" : "ToolProxy",
  "@id" : "{$url}",
  "tool_proxy_guid" : "{$toolproxy->guid}"
}
EOD;
                $response->set_code(201);
                $response->set_content_type('application/vnd.ims.lti.v2.toolproxy.id+json');
                $response->set_body($body);
            } else {
                                $toolproxy->state = LTI_TOOL_PROXY_STATE_REJECTED;
                $response->set_code(400);
            }
            lti_update_tool_proxy($toolproxy);
        } else {
            $response->set_code(400);
        }
    }

    
    private static function lti_extract_parameters($parameters) {

        $params = array();
        foreach ($parameters as $parameter) {
            if (isset($parameter->variable)) {
                $value = '$' . $parameter->variable;
            } else {
                $value = $parameter->fixed;
                if (strlen($value) > 0) {
                    $first = substr($value, 0, 1);
                    if (($first == '$') || ($first == '\\')) {
                        $value = '\\' . $value;
                    }
                }
            }
            $params[] = "{$parameter->name}={$value}";
        }

        return implode("\n", $params);

    }

}
