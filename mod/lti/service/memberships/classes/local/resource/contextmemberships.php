<?php




namespace ltiservice_memberships\local\resource;

use \mod_lti\local\ltiservice\service_base;
use ltiservice_memberships\local\service\memberships;

defined('MOODLE_INTERNAL') || die();


class contextmemberships extends \mod_lti\local\ltiservice\resource_base {

    
    public function __construct($service) {

        parent::__construct($service);
        $this->id = 'ToolProxyBindingMemberships';
        $this->template = '/{context_type}/{context_id}/bindings/{vendor_code}/{product_code}/{tool_code}/memberships';
        $this->variables[] = 'ToolProxyBinding.memberships.url';
        $this->formats[] = 'application/vnd.ims.lis.v2.membershipcontainer+json';
        $this->methods[] = 'GET';

    }

    
    public function execute($response) {
        global $CFG, $DB;

        $params = $this->parse_template();
        $role = optional_param('role', '', PARAM_TEXT);
        $limitnum = optional_param('limit', 0, PARAM_INT);
        $limitfrom = optional_param('from', 0, PARAM_INT);
        if ($limitnum <= 0) {
            $limitfrom = 0;
        }

        try {
            if (!$this->get_service()->check_tool_proxy($params['product_code'])) {
                throw new \Exception(null, 401);
            }
            if (!($course = $DB->get_record('course', array('id' => $params['context_id']), 'id', IGNORE_MISSING))) {
                throw new \Exception(null, 404);
            }
            if (!($context = \context_course::instance($course->id))) {
                throw new \Exception(null, 404);
            }
            if (!($tool = $DB->get_record('lti_types', array('id' => $params['tool_code']),
                                    'toolproxyid,enabledcapability,parameter', IGNORE_MISSING))) {
                throw new \Exception(null, 404);
            }
            $toolproxy = $DB->get_record('lti_tool_proxies', array('id' => $tool->toolproxyid), 'guid', IGNORE_MISSING);
            if (!$toolproxy || ($toolproxy->guid !== $this->get_service()->get_tool_proxy()->guid)) {
                throw new \Exception(null, 400);
            }
            $json = memberships::get_users_json($this, $context, $course->id, $tool, $role, $limitfrom, $limitnum, null, null);

            $response->set_content_type($this->formats[0]);
            $response->set_body($json);

        } catch (\Exception $e) {
            $response->set_code($e->getCode());
        }
    }

    
    public function parse_value($value) {
        global $COURSE, $DB;

        if ($COURSE->id === SITEID) {
            $this->params['context_type'] = 'Group';
        } else {
            $this->params['context_type'] = 'CourseSection';
        }
        $this->params['context_id'] = $COURSE->id;
        $this->params['vendor_code'] = $this->get_service()->get_tool_proxy()->vendorcode;
        $this->params['product_code'] = $this->get_service()->get_tool_proxy()->guid;

        $id = optional_param('id', 0, PARAM_INT);         if (!empty($id)) {
            $cm = get_coursemodule_from_id('lti', $id, 0, false, IGNORE_MISSING);
            $lti = $DB->get_record('lti', array('id' => $cm->instance), 'typeid', IGNORE_MISSING);
            if ($lti && !empty($lti->typeid)) {
                $this->params['tool_code'] = $lti->typeid;
            }
        }
        $value = str_replace('$ToolProxyBinding.memberships.url', parent::get_endpoint(), $value);

        return $value;

    }

}
