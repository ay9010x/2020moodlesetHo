<?php




namespace ltiservice_memberships\local\service;

defined('MOODLE_INTERNAL') || die();


class memberships extends \mod_lti\local\ltiservice\service_base {

    
    const CONTEXT_ROLE_PREFIX = 'http://purl.imsglobal.org/vocab/lis/v2/membership#';
    
    const CONTEXT_ROLE_INSTRUCTOR = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Instructor';
    
    const CONTEXT_ROLE_LEARNER = 'http://purl.imsglobal.org/vocab/lis/v2/membership#Learner';
    
    const INSTRUCTOR_CAPABILITY = 'moodle/course:manageactivities';

    
    public function __construct() {

        parent::__construct();
        $this->id = 'memberships';
        $this->name = get_string('servicename', 'ltiservice_memberships');

    }

    
    public function get_resources() {

        if (empty($this->resources)) {
            $this->resources = array();
            $this->resources[] = new \ltiservice_memberships\local\resource\contextmemberships($this);
            $this->resources[] = new \ltiservice_memberships\local\resource\linkmemberships($this);
        }

        return $this->resources;

    }

    
    public static function get_users_json($resource, $context, $id, $tool, $role, $limitfrom, $limitnum, $lti, $info) {

        $withcapability = '';
        $exclude = array();
        if (!empty($role)) {
            if ((strpos($role, 'http://') !== 0) && (strpos($role, 'https://') !== 0)) {
                $role = self::CONTEXT_ROLE_PREFIX . $role;
            }
            if ($role === self::CONTEXT_ROLE_INSTRUCTOR) {
                $withcapability = self::INSTRUCTOR_CAPABILITY;
            } else if ($role === self::CONTEXT_ROLE_LEARNER) {
                $exclude = array_keys(get_enrolled_users($context, self::INSTRUCTOR_CAPABILITY, 0, 'u.id',
                                                         null, null, null, true));
            }
        }
        $users = get_enrolled_users($context, $withcapability, 0, 'u.*', null, $limitfrom, $limitnum, true);
        if (count($users) < $limitnum) {
            $limitfrom = 0;
            $limitnum = 0;
        }
        $json = self::users_to_json($resource, $users, $id, $tool, $exclude, $limitfrom, $limitnum, $lti, $info);

        return $json;

    }

    
    private static function users_to_json($resource, $users, $id, $tool, $exclude, $limitfrom, $limitnum,
                                         $lti, $info) {

        $nextpage = 'null';
        if ($limitnum > 0) {
            $limitfrom += $limitnum;
            $nextpage = "\"{$resource->get_endpoint()}?limit={$limitnum}&amp;from={$limitfrom}\"";
        }
        $json = <<< EOD
{
  "@context" : "http://purl.imsglobal.org/ctx/lis/v2/MembershipContainer",
  "@type" : "Page",
  "@id" : "{$resource->get_endpoint()}",
  "nextPage" : {$nextpage},
  "pageOf" : {
    "@type" : "LISMembershipContainer",
    "membershipSubject" : {
      "@type" : "Context",
      "contextId" : "{$id}",
      "membership" : [

EOD;
        $enabledcapabilities = lti_get_enabled_capabilities($tool);
        $sep = '        ';
        foreach ($users as $user) {
            $include = !in_array($user->id, $exclude);
            if ($include && !empty($info)) {
                $include = $info->is_user_visible($info->get_course_module(), $user->id);
            }
            if ($include) {
                $member = new \stdClass();
                if (in_array('User.id', $enabledcapabilities)) {
                    $member->userId = $user->id;
                }
                if (in_array('Person.sourcedId', $enabledcapabilities)) {
                    $member->sourcedId = format_string($user->idnumber);
                }
                if (in_array('Person.name.full', $enabledcapabilities)) {
                    $member->name = format_string("{$user->firstname} {$user->lastname}");
                }
                if (in_array('Person.name.given', $enabledcapabilities)) {
                    $member->givenName = format_string($user->firstname);
                }
                if (in_array('Person.name.family', $enabledcapabilities)) {
                    $member->familyName = format_string($user->lastname);
                }
                if (in_array('Person.email.primary', $enabledcapabilities)) {
                    $member->email = format_string($user->email);
                }
                if (in_array('Result.sourcedId', $enabledcapabilities) && !empty($lti) && !empty($lti->servicesalt)) {
                    $member->resultSourcedId = json_encode(lti_build_sourcedid($lti->id, $user->id, $lti->servicesalt,
                                                           $lti->typeid));
                }
                $roles = explode(',', lti_get_ims_role($user->id, null, $id, true));

                $membership = new \stdClass();
                $membership->status = 'Active';
                $membership->member = $member;
                $membership->role = $roles;

                $json .= $sep . json_encode($membership);
                $sep = ",\n        ";
            }

        }

        $json .= <<< EOD

      ]
    }
  }
}
EOD;

        return $json;

    }

}
