<?php




namespace ltiservice_profile\local\resource;

use \mod_lti\local\ltiservice\service_base;

defined('MOODLE_INTERNAL') || die();


class profile extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolConsumerProfile';
        $this->template = '/profile/{tool_proxy_id}';
        $this->variables[] = 'ToolConsumerProfile.url';
        $this->formats[] = 'application/vnd.ims.lti.v2.toolconsumerprofile+json';
        $this->methods[] = 'GET';

    }

    
    public function get_path() {

        $path = $this->template;
        $toolproxy = $this->get_service()->get_tool_proxy();
        if (!empty($toolproxy)) {
            $path = str_replace('{tool_proxy_id}', $toolproxy->guid, $path);
        }

        return $path;

    }

    
    public function execute($response) {

        global $CFG;

        $version = service_base::LTI_VERSION2P0;
        $params = $this->parse_template();
        $ok = $this->get_service()->check_tool_proxy($params['tool_proxy_id']);
        if (!$ok) {
            $response->set_code(404);
        } else if (optional_param('lti_version', '', PARAM_ALPHANUMEXT) != $version) {
            $response->set_code(400);
        } else {
            $toolproxy = $this->get_service()->get_tool_proxy();
            $response->set_content_type($this->formats[0]);

            $servicepath = $this->get_service()->get_service_path();
            $id = $servicepath . $this->get_path();
            $now = date('Y-m-d\TH:iO');
            $capabilityofferedarr = explode("\n", $toolproxy->capabilityoffered);
            $serviceofferedarr = explode("\n", $toolproxy->serviceoffered);
            $serviceoffered = '';
            $sep = '';
            $services = \core_component::get_plugin_list('ltiservice');
            foreach ($services as $name => $location) {
                if (in_array($name, $serviceofferedarr)) {
                    $classname = "\\ltiservice_{$name}\\local\\service\\{$name}";
                    $service = new $classname();
                    $service->set_tool_proxy($toolproxy);
                    $resources = $service->get_resources();
                    foreach ($resources as $resource) {
                        $formats = implode("\", \"", $resource->get_formats());
                        $methods = implode("\", \"", $resource->get_methods());
                        $capabilityofferedarr = array_merge($capabilityofferedarr, $resource->get_variables());
                        $path = $servicepath . preg_replace('/\{?.*\}$/', '', $resource->get_path());
                        $serviceoffered .= <<< EOD
{$sep}
    {
      "@type":"{$resource->get_type()}",
      "@id":"tcp:{$resource->get_id()}",
      "endpoint":"{$path}",
      "format":["{$formats}"],
      "action":["{$methods}"]
    }
EOD;
                        $sep = ',';
                    }
                }
            }
            $capabilityoffered = implode("\",\n    \"", $capabilityofferedarr);
            if (strlen($capabilityoffered) > 0) {
                $capabilityoffered = "\n    \"{$capabilityoffered}\"";
            }
            $urlparts = parse_url($CFG->wwwroot);
            $orgid = $urlparts['host'];
            $name = 'Moodle';
            $code = 'moodle';
            $vendorname = 'Moodle.org';
            $vendorcode = 'mdl';
            $prodversion = strval($CFG->version);
            if (!empty($CFG->mod_lti_institution_name)) {
                $consumername = $CFG->mod_lti_institution_name;
                $consumerdesc = '';
            } else {
                $consumername = get_site()->fullname;
                $consumerdesc = strip_tags(get_site()->summary);
            }
            $profile = <<< EOD
{
  "@context":[
    "http://purl.imsglobal.org/ctx/lti/v2/ToolConsumerProfile",
    {
      "tcp":"{$id}#"
    }
  ],
  "@type":"ToolConsumerProfile",
  "@id":"{$id}",
  "lti_version":"{$version}",
  "guid":"{$toolproxy->guid}",
  "product_instance":{
    "guid":"{$orgid}",
    "product_info":{
      "product_name":{
        "default_value":"{$name}",
        "key":"product.name"
      },
      "product_version":"{$prodversion}",
      "product_family":{
        "code":"{$code}",
        "vendor":{
          "code":"{$vendorcode}",
          "vendor_name":{
            "default_value":"{$vendorname}",
            "key":"product.vendor.name"
          },
          "timestamp":"{$now}"
        }
      }
    },
    "service_owner":{
      "@id":"ServiceOwner",
      "service_owner_name":{
        "default_value":"{$consumername}",
        "key":"service_owner.name"
      },
      "description":{
        "default_value":"{$consumerdesc}",
        "key":"service_owner.description"
      }
    }
  },
  "capability_offered":[{$capabilityoffered}
  ],
  "service_offered":[{$serviceoffered}
  ]
}
EOD;
            $response->set_body($profile);

        }
    }

    
    public function get_endpoint() {

        return parent::get_endpoint() . '?lti_version=' . service_base::LTI_VERSION2P0;

    }

    
    public function parse_value($value) {

        $value = str_replace('$ToolConsumerProfile.url', $this->get_endpoint(), $value);

        return $value;

    }

}