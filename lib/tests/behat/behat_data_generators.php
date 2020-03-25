<?php




require_once(__DIR__ . '/../../behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Behat\Tester\Exception\PendingException as PendingException;


class behat_data_generators extends behat_base {

    
    protected $datagenerator;

    
    protected static $elements = array(
        'users' => array(
            'datagenerator' => 'user',
            'required' => array('username')
        ),
        'categories' => array(
            'datagenerator' => 'category',
            'required' => array('idnumber'),
            'switchids' => array('category' => 'parent')
        ),
        'courses' => array(
            'datagenerator' => 'course',
            'required' => array('shortname'),
            'switchids' => array('category' => 'category')
        ),
        'groups' => array(
            'datagenerator' => 'group',
            'required' => array('idnumber', 'course'),
            'switchids' => array('course' => 'courseid')
        ),
        'groupings' => array(
            'datagenerator' => 'grouping',
            'required' => array('idnumber', 'course'),
            'switchids' => array('course' => 'courseid')
        ),
        'course enrolments' => array(
            'datagenerator' => 'enrol_user',
            'required' => array('user', 'course', 'role'),
            'switchids' => array('user' => 'userid', 'course' => 'courseid', 'role' => 'roleid')
        ),
        'permission overrides' => array(
            'datagenerator' => 'permission_override',
            'required' => array('capability', 'permission', 'role', 'contextlevel', 'reference'),
            'switchids' => array('role' => 'roleid')
        ),
        'system role assigns' => array(
            'datagenerator' => 'system_role_assign',
            'required' => array('user', 'role'),
            'switchids' => array('user' => 'userid', 'role' => 'roleid')
        ),
        'role assigns' => array(
            'datagenerator' => 'role_assign',
            'required' => array('user', 'role', 'contextlevel', 'reference'),
            'switchids' => array('user' => 'userid', 'role' => 'roleid')
        ),
        'activities' => array(
            'datagenerator' => 'activity',
            'required' => array('activity', 'idnumber', 'course'),
            'switchids' => array('course' => 'course', 'gradecategory' => 'gradecat')
        ),
        'blocks' => array(
            'datagenerator' => 'block_instance',
            'required' => array('blockname', 'contextlevel', 'reference'),
        ),
        'group members' => array(
            'datagenerator' => 'group_member',
            'required' => array('user', 'group'),
            'switchids' => array('user' => 'userid', 'group' => 'groupid')
        ),
        'grouping groups' => array(
            'datagenerator' => 'grouping_group',
            'required' => array('grouping', 'group'),
            'switchids' => array('grouping' => 'groupingid', 'group' => 'groupid')
        ),
        'cohorts' => array(
            'datagenerator' => 'cohort',
            'required' => array('idnumber')
        ),
        'cohort members' => array(
            'datagenerator' => 'cohort_member',
            'required' => array('user', 'cohort'),
            'switchids' => array('user' => 'userid', 'cohort' => 'cohortid')
        ),
        'roles' => array(
            'datagenerator' => 'role',
            'required' => array('shortname')
        ),
        'grade categories' => array(
            'datagenerator' => 'grade_category',
            'required' => array('fullname', 'course'),
            'switchids' => array('course' => 'courseid', 'gradecategory' => 'parent')
        ),
        'grade items' => array(
            'datagenerator' => 'grade_item',
            'required' => array('course'),
            'switchids' => array('scale' => 'scaleid', 'outcome' => 'outcomeid', 'course' => 'courseid',
                                 'gradecategory' => 'categoryid')
        ),
        'grade outcomes' => array(
            'datagenerator' => 'grade_outcome',
            'required' => array('shortname', 'scale'),
            'switchids' => array('course' => 'courseid', 'gradecategory' => 'categoryid', 'scale' => 'scaleid')
        ),
        'scales' => array(
            'datagenerator' => 'scale',
            'required' => array('name', 'scale'),
            'switchids' => array('course' => 'courseid')
        ),
        'question categories' => array(
            'datagenerator' => 'question_category',
            'required' => array('name', 'contextlevel', 'reference'),
            'switchids' => array('questioncategory' => 'parent')
        ),
        'questions' => array(
            'datagenerator' => 'question',
            'required' => array('qtype', 'questioncategory', 'name'),
            'switchids' => array('questioncategory' => 'category', 'user' => 'createdby')
        ),
        'tags' => array(
            'datagenerator' => 'tag',
            'required' => array('name')
        ),
    );

    
    public function the_following_exist($elementname, TableNode $data) {

                require_once(__DIR__ . '/../../testing/generator/lib.php');

        if (empty(self::$elements[$elementname])) {
            throw new PendingException($elementname . ' data generator is not implemented');
        }

        $this->datagenerator = testing_util::get_data_generator();

        $elementdatagenerator = self::$elements[$elementname]['datagenerator'];
        $requiredfields = self::$elements[$elementname]['required'];
        if (!empty(self::$elements[$elementname]['switchids'])) {
            $switchids = self::$elements[$elementname]['switchids'];
        }

        foreach ($data->getHash() as $elementdata) {

                        foreach ($requiredfields as $requiredfield) {
                if (!isset($elementdata[$requiredfield])) {
                    throw new Exception($elementname . ' requires the field ' . $requiredfield . ' to be specified');
                }
            }

                        if (isset($switchids)) {
                foreach ($switchids as $element => $field) {
                    $methodname = 'get_' . $element . '_id';

                                        if (isset($elementdata[$element])) {
                                                $id = $this->{$methodname}($elementdata[$element]);
                        unset($elementdata[$element]);
                        $elementdata[$field] = $id;
                    }
                }
            }

                        if (method_exists($this, 'preprocess_' . $elementdatagenerator)) {
                $elementdata = $this->{'preprocess_' . $elementdatagenerator}($elementdata);
            }

                        $methodname = 'create_' . $elementdatagenerator;
            if (method_exists($this->datagenerator, $methodname)) {
                                $this->datagenerator->{$methodname}($elementdata);

            } else if (method_exists($this, 'process_' . $elementdatagenerator)) {
                                $this->{'process_' . $elementdatagenerator}($elementdata);
            } else {
                throw new PendingException($elementname . ' data generator is not implemented');
            }
        }

    }

    
    protected function preprocess_user($data) {
        if (!isset($data['password'])) {
            $data['password'] = $data['username'];
        }
        return $data;
    }

    
    protected function preprocess_cohort($data) {
        if (isset($data['contextlevel'])) {
            if (!isset($data['reference'])) {
                throw new Exception('If field contextlevel is specified, field reference must also be present');
            }
            $context = $this->get_context($data['contextlevel'], $data['reference']);
            unset($data['contextlevel']);
            unset($data['reference']);
            $data['contextid'] = $context->id;
        }
        return $data;
    }

    
    protected function preprocess_grade_item($data) {
        global $CFG;
        require_once("$CFG->libdir/grade/constants.php");

        if (isset($data['gradetype'])) {
            $data['gradetype'] = constant("GRADE_TYPE_" . strtoupper($data['gradetype']));
        }

        if (!empty($data['category']) && !empty($data['courseid'])) {
            $cat = grade_category::fetch(array('fullname' => $data['category'], 'courseid' => $data['courseid']));
            if (!$cat) {
                throw new Exception('Could not resolve category with name "' . $data['category'] . '"');
            }
            unset($data['category']);
            $data['categoryid'] = $cat->id;
        }

        return $data;
    }

    
    protected function process_activity($data) {
        global $DB, $CFG;

                $activityname = $data['activity'];
        unset($data['activity']);

                if (isset($data['grade']) && strlen($data['grade']) && !is_number($data['grade'])) {
            $data['grade'] = - $this->get_scale_id($data['grade']);
            require_once("$CFG->libdir/grade/constants.php");

            if (!isset($data['gradetype'])) {
                $data['gradetype'] = GRADE_TYPE_SCALE;
            }
        }

                $cmoptions = array();
        $cmcolumns = $DB->get_columns('course_modules');
        foreach ($cmcolumns as $key => $value) {
            if (isset($data[$key])) {
                $cmoptions[$key] = $data[$key];
            }
        }

                try {
            $this->datagenerator->create_module($activityname, $data, $cmoptions);
        } catch (coding_exception $e) {
            throw new Exception('\'' . $activityname . '\' activity can not be added using this step,' .
                ' use the step \'I add a "ACTIVITY_OR_RESOURCE_NAME_STRING" to section "SECTION_NUMBER"\' instead');
        }
    }

    
    protected function process_block_instance($data) {

        if (empty($data['blockname'])) {
            throw new Exception('\'blocks\' requires the field \'block\' type to be specified');
        }

        if (empty($data['contextlevel'])) {
            throw new Exception('\'blocks\' requires the field \'contextlevel\' to be specified');
        }

        if (!isset($data['reference'])) {
            throw new Exception('\'blocks\' requires the field \'reference\' to be specified');
        }

        $context = $this->get_context($data['contextlevel'], $data['reference']);
        $data['parentcontextid'] = $context->id;

                                $this->datagenerator->create_block($data['blockname'], $data, $data);
    }

    
    protected function process_enrol_user($data) {
        global $SITE;

        if (empty($data['roleid'])) {
            throw new Exception('\'course enrolments\' requires the field \'role\' to be specified');
        }

        if (!isset($data['userid'])) {
            throw new Exception('\'course enrolments\' requires the field \'user\' to be specified');
        }

        if (!isset($data['courseid'])) {
            throw new Exception('\'course enrolments\' requires the field \'course\' to be specified');
        }

        if (!isset($data['enrol'])) {
            $data['enrol'] = 'manual';
        }

        if (!isset($data['timestart'])) {
            $data['timestart'] = 0;
        }

        if (!isset($data['timeend'])) {
            $data['timeend'] = 0;
        }

        if (!isset($data['status'])) {
            $data['status'] = null;
        }

                if ($data['courseid'] == $SITE->id) {
                        $context = context_course::instance($data['courseid']);
            role_assign($data['roleid'], $data['userid'], $context->id);

        } else {
                        $this->datagenerator->enrol_user($data['userid'], $data['courseid'], $data['roleid'], $data['enrol'],
                    $data['timestart'], $data['timeend'], $data['status']);
        }

    }

    
    protected function process_permission_override($data) {

                $context = $this->get_context($data['contextlevel'], $data['reference']);

        switch ($data['permission']) {
            case get_string('allow', 'role'):
                $permission = CAP_ALLOW;
                break;
            case get_string('prevent', 'role'):
                $permission = CAP_PREVENT;
                break;
            case get_string('prohibit', 'role'):
                $permission = CAP_PROHIBIT;
                break;
            default:
                throw new Exception('The \'' . $data['permission'] . '\' permission does not exist');
                break;
        }

        if (is_null(get_capability_info($data['capability']))) {
            throw new Exception('The \'' . $data['capability'] . '\' capability does not exist');
        }

        role_change_permission($data['roleid'], $context, $data['capability'], $permission);
    }

    
    protected function process_system_role_assign($data) {

        if (empty($data['roleid'])) {
            throw new Exception('\'system role assigns\' requires the field \'role\' to be specified');
        }

        if (!isset($data['userid'])) {
            throw new Exception('\'system role assigns\' requires the field \'user\' to be specified');
        }

        $context = context_system::instance();

        $this->datagenerator->role_assign($data['roleid'], $data['userid'], $context->id);
    }

    
    protected function process_role_assign($data) {

        if (empty($data['roleid'])) {
            throw new Exception('\'role assigns\' requires the field \'role\' to be specified');
        }

        if (!isset($data['userid'])) {
            throw new Exception('\'role assigns\' requires the field \'user\' to be specified');
        }

        if (empty($data['contextlevel'])) {
            throw new Exception('\'role assigns\' requires the field \'contextlevel\' to be specified');
        }

        if (!isset($data['reference'])) {
            throw new Exception('\'role assigns\' requires the field \'reference\' to be specified');
        }

                $context = $this->get_context($data['contextlevel'], $data['reference']);

        $this->datagenerator->role_assign($data['roleid'], $data['userid'], $context->id);
    }

    
    protected function process_role($data) {

                if (empty($data['shortname'])) {
            throw new Exception('\'role\' requires the field \'shortname\' to be specified');
        }

        $this->datagenerator->create_role($data);
    }

    
    protected function process_cohort_member($data) {
        cohort_add_member($data['cohortid'], $data['userid']);
    }

    
    protected function process_question_category($data) {
        $context = $this->get_context($data['contextlevel'], $data['reference']);
        $data['contextid'] = $context->id;
        $this->datagenerator->get_plugin_generator('core_question')->create_question_category($data);
    }

    
    protected function process_question($data) {
        if (array_key_exists('questiontext', $data)) {
            $data['questiontext'] = array(
                    'text'   => $data['questiontext'],
                    'format' => FORMAT_HTML,
                );
        }

        if (array_key_exists('generalfeedback', $data)) {
            $data['generalfeedback'] = array(
                    'text'   => $data['generalfeedback'],
                    'format' => FORMAT_HTML,
                );
        }

        $which = null;
        if (!empty($data['template'])) {
            $which = $data['template'];
        }

        $this->datagenerator->get_plugin_generator('core_question')->create_question($data['qtype'], $which, $data);
    }

    
    protected function get_gradecategory_id($fullname) {
        global $DB;

        if (!$id = $DB->get_field('grade_categories', 'id', array('fullname' => $fullname))) {
            throw new Exception('The specified grade category with fullname "' . $fullname . '" does not exist');
        }
        return $id;
    }

    
    protected function get_user_id($username) {
        global $DB;

        if (!$id = $DB->get_field('user', 'id', array('username' => $username))) {
            throw new Exception('The specified user with username "' . $username . '" does not exist');
        }
        return $id;
    }

    
    protected function get_role_id($roleshortname) {
        global $DB;

        if (!$id = $DB->get_field('role', 'id', array('shortname' => $roleshortname))) {
            throw new Exception('The specified role with shortname "' . $roleshortname . '" does not exist');
        }

        return $id;
    }

    
    protected function get_category_id($idnumber) {
        global $DB;

                if ($idnumber == false) {
            return null;
        }

        if (!$id = $DB->get_field('course_categories', 'id', array('idnumber' => $idnumber))) {
            throw new Exception('The specified category with idnumber "' . $idnumber . '" does not exist');
        }

        return $id;
    }

    
    protected function get_course_id($shortname) {
        global $DB;

        if (!$id = $DB->get_field('course', 'id', array('shortname' => $shortname))) {
            throw new Exception('The specified course with shortname "' . $shortname . '" does not exist');
        }
        return $id;
    }

    
    protected function get_group_id($idnumber) {
        global $DB;

        if (!$id = $DB->get_field('groups', 'id', array('idnumber' => $idnumber))) {
            throw new Exception('The specified group with idnumber "' . $idnumber . '" does not exist');
        }
        return $id;
    }

    
    protected function get_grouping_id($idnumber) {
        global $DB;

        if (!$id = $DB->get_field('groupings', 'id', array('idnumber' => $idnumber))) {
            throw new Exception('The specified grouping with idnumber "' . $idnumber . '" does not exist');
        }
        return $id;
    }

    
    protected function get_cohort_id($idnumber) {
        global $DB;

        if (!$id = $DB->get_field('cohort', 'id', array('idnumber' => $idnumber))) {
            throw new Exception('The specified cohort with idnumber "' . $idnumber . '" does not exist');
        }
        return $id;
    }

    
    protected function get_outcome_id($shortname) {
        global $DB;

        if (!$id = $DB->get_field('grade_outcomes', 'id', array('shortname' => $shortname))) {
            throw new Exception('The specified outcome with shortname "' . $shortname . '" does not exist');
        }
        return $id;
    }

    
    protected function get_scale_id($name) {
        global $DB;

        if (!$id = $DB->get_field('scale', 'id', array('name' => $name))) {
            throw new Exception('The specified scale with name "' . $name . '" does not exist');
        }
        return $id;
    }

    
    protected function get_questioncategory_id($name) {
        global $DB;

        if ($name == 'Top') {
            return 0;
        }

        if (!$id = $DB->get_field('question_categories', 'id', array('name' => $name))) {
            throw new Exception('The specified question category with name "' . $name . '" does not exist');
        }
        return $id;
    }

    
    protected function get_context($levelname, $contextref) {
        global $DB;

                $contextlevels = context_helper::get_all_levels();
        $contextnames = array();
        foreach ($contextlevels as $level => $classname) {
            $contextnames[context_helper::get_level_name($level)] = $level;
        }

        if (empty($contextnames[$levelname])) {
            throw new Exception('The specified "' . $levelname . '" context level does not exist');
        }
        $contextlevel = $contextnames[$levelname];

                if ($contextlevel == CONTEXT_SYSTEM) {
            return context_system::instance();
        }

        switch ($contextlevel) {

            case CONTEXT_USER:
                $instanceid = $DB->get_field('user', 'id', array('username' => $contextref));
                break;

            case CONTEXT_COURSECAT:
                $instanceid = $DB->get_field('course_categories', 'id', array('idnumber' => $contextref));
                break;

            case CONTEXT_COURSE:
                $instanceid = $DB->get_field('course', 'id', array('shortname' => $contextref));
                break;

            case CONTEXT_MODULE:
                $instanceid = $DB->get_field('course_modules', 'id', array('idnumber' => $contextref));
                break;

            default:
                break;
        }

        $contextclass = $contextlevels[$contextlevel];
        if (!$context = $contextclass::instance($instanceid, IGNORE_MISSING)) {
            throw new Exception('The specified "' . $contextref . '" context reference does not exist');
        }

        return $context;
    }

}
