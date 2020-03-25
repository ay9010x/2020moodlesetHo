<?php




namespace ltiservice_toolproxy\local\service;

defined('MOODLE_INTERNAL') || die();


class toolproxy extends \mod_lti\local\ltiservice\service_base {

    
    public function __construct() {

        parent::__construct();
        $this->id = 'toolproxy';
        $this->name = 'Tool Proxy';

    }

    
    public function get_resources() {

        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new \ltiservice_toolproxy\local\resource\toolproxy($this);
        }

        return $this->resources;

    }

}
