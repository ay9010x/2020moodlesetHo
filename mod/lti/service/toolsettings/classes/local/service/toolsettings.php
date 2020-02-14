<?php




namespace ltiservice_toolsettings\local\service;

defined('MOODLE_INTERNAL') || die();


class toolsettings extends \mod_lti\local\ltiservice\service_base {

    
    public function __construct() {

        parent::__construct();
        $this->id = 'toolsettings';
        $this->name = 'Tool Settings';

    }

    
    public function get_resources() {

        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new \ltiservice_toolsettings\local\resource\systemsettings($this);
            $this->resources[] = new \ltiservice_toolsettings\local\resource\contextsettings($this);
            $this->resources[] = new \ltiservice_toolsettings\local\resource\linksettings($this);
        }

        return $this->resources;

    }

    
    public static function distinct_settings(&$systemsettings, &$contextsettings, $linksettings) {

        if (!empty($systemsettings)) {
            foreach ($systemsettings as $key => $value) {
                if ((!empty($contextsettings) && array_key_exists($key, $contextsettings)) ||
                    (!empty($linksettings) && array_key_exists($key, $linksettings))) {
                    unset($systemsettings[$key]);
                }
            }
        }
        if (!empty($contextsettings)) {
            foreach ($contextsettings as $key => $value) {
                if (!empty($linksettings) && array_key_exists($key, $linksettings)) {
                    unset($contextsettings[$key]);
                }
            }
        }
    }

    
    public static function settings_to_json($settings, $simpleformat, $type, $resource) {

        $json = '';
        if (!empty($resource)) {
            $indent = '';
            if (!$simpleformat) {
                $json .= "    {\n      \"@type\":\"{$type}\",\n";
                $json .= "      \"@id\":\"{$resource->get_endpoint()}\",\n";
                $json .= "      \"custom\":{\n";
                $json .= "        \"@id\":\"{$resource->get_endpoint()}/custom\"";
                $indent = '      ';
            }
            $isfirst = $simpleformat;
            if (!empty($settings)) {
                foreach ($settings as $key => $value) {
                    if (!$isfirst) {
                        $json .= ',';
                    } else {
                        $isfirst = false;
                    }
                    $json .= "\n{$indent}  \"{$key}\":\"{$value}\"";
                }
            }
            if (!$simpleformat) {
                $json .= "\n{$indent}}\n    }";
            }
        }

        return $json;

    }

}
