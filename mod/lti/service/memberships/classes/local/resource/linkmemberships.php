<?php




namespace ltiservice_memberships\local\resource;

use \mod_lti\local\ltiservice\service_base;
use ltiservice_memberships\local\service\memberships;
use core_availability\info;
use core_availability\info_module;

defined('MOODLE_INTERNAL') || die();


class linkmemberships extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'LtiLinkMemberships';
        $this->template = '/links/{link_id}/memberships';
        $this->variables[] = 'LtiLink.memberships.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.membershipcontainer+json';
        $this->methods[] = 'GET';

    }

    
    public function execute($response) {
        global $CFG, $DB;

        $params = $this->parse_template();
        $linkid = $params['link_id'];
        $role = optional_param('role', '', PARAM_TEXT);
        $limitnum = optional_param('limit', 0, PARAM_INT);
        $limitfrom = optional_param('from', 0, PARAM_INT);
        if ($limitnum <= 0) {
            $limitfrom = 0;
        }

        try {
            if (empty($linkid)) {
                throw new \Exception(null, 404);
            }
            if (!($lti = $DB->get_record('lti', array('id' => $linkid), 'id,course,typeid,servicesalt', IGNORE_MISSING))) {
                throw new \Exception(null, 404);
            }
            $tool = $DB->get_record('lti_types', array('id' => $lti->typeid));
            $toolproxy = $DB->get_record('lti_tool_proxies', array('id' => $tool->toolproxyid));
            if (!$this->check_tool_proxy($toolproxy->guid, $response->get_request_data())) {
                throw new \Exception(null, 401);
            }
            if (!($course = $DB->get_record('course', array('id' => $lti->course), 'id', IGNORE_MISSING))) {
                throw new \Exception(null, 404);
            }
            if (!($context = \context_course::instance($lti->course))) {
                throw new \Exception(null, 404);
            }
            $modinfo = get_fast_modinfo($course);
            $cm = get_coursemodule_from_instance('lti', $linkid, $lti->course, false, MUST_EXIST);
            $cm = $modinfo->get_cm($cm->id);
            $info = new info_module($cm);
            if ($info->is_available_for_all()) {
                $info = null;
            }

            $json = memberships::get_users_json($this, $context, $lti->course, $tool, $role, $limitfrom, $limitnum, $lti, $info);

            $response->set_content_type($this->formats[0]);
            $response->set_body($json);

        } catch (\Exception $e) {
            $response->set_code($e->getCode());
        }

    }

    
    public function parse_value($value) {

        $id = optional_param('id', 0, PARAM_INT);         if (!empty($id)) {
            $cm = get_coursemodule_from_id('lti', $id, 0, false, MUST_EXIST);
            $this->params['link_id'] = $cm->instance;
        }
        $value = str_replace('$LtiLink.memberships.url', parent::get_endpoint(), $value);

        return $value;

    }

}
