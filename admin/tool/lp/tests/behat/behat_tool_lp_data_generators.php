<?php



require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Behat\Tester\Exception\PendingException as PendingException;
use core_competency\competency;
use core_competency\competency_framework;
use core_competency\plan;
use core_competency\user_evidence;


class behat_tool_lp_data_generators extends behat_base {

    
    protected $datageneratorlp;

    
    protected static $elements = array(
        'frameworks' => array(
            'datagenerator' => 'framework',
            'required' => array()
        ),
        'templates' => array(
            'datagenerator' => 'template',
            'required' => array()
        ),
        'plans' => array(
            'datagenerator' => 'plan',
            'required' => array('user')
        ),
        'competencies' => array(
            'datagenerator' => 'competency',
            'required' => array('framework')
        ),
        'userevidence' => array(
            'datagenerator' => 'user_evidence',
            'required' => array('user')
        ),
        'plancompetencies' => array(
            'datagenerator' => 'plan_competency',
            'required' => array('plan', 'competency')
        ),
        'userevidencecompetencies' => array(
            'datagenerator' => 'user_evidence_competency',
            'required' => array('userevidence', 'competency')
        ),
        'usercompetencies' => array(
            'datagenerator' => 'user_competency',
            'required' => array('user', 'competency')
        ),
        'usercompetencyplans' => array(
            'datagenerator' => 'user_competency_plan',
            'required' => array('user', 'competency', 'plan')
        )
    );

    
    public function the_following_lp_exist($elementname, TableNode $data) {

                require_once(__DIR__.'/../../../../../lib/phpunit/classes/util.php');

        if (empty(self::$elements[$elementname])) {
            throw new PendingException($elementname . ' data generator is not implemented');
        }

        $datagenerator = testing_util::get_data_generator();
        $this->datageneratorlp = $datagenerator->get_plugin_generator('core_competency');

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
            if (method_exists($this->datageneratorlp, $methodname)) {
                                $this->datageneratorlp->{$methodname}($elementdata);

            } else if (method_exists($this, 'process_' . $elementdatagenerator)) {
                                $this->{'process_' . $elementdatagenerator}($elementdata);
            } else {
                throw new PendingException($elementname . ' data generator is not implemented');
            }
        }
    }

    
    protected function preprocess_competency($data) {
        if (isset($data['framework'])) {
            $framework = competency_framework::get_record(array('idnumber' => $data['framework']));
            if ($framework) {
                $data['competencyframeworkid'] = $framework->get_id();
            } else {
                $framework = competency_framework::get_record(array('id' => $data['framework']));
                if ($framework) {
                    $data['competencyframeworkid'] = $framework->get_id();
                } else {
                    throw new Exception('Could not resolve framework with idnumber or id : "' . $data['category'] . '"');
                }
            }
        }
        unset($data['framework']);
        return $data;
    }

    
    protected function preprocess_plan($data) {
        global $DB;

        if (isset($data['user'])) {
            $user = $DB->get_record('user', array('username' => $data['user']), '*', MUST_EXIST);
            $data['userid'] = $user->id;
        }
        unset($data['user']);

        if (isset($data['reviewer'])) {
            if (is_number($data['reviewer'])) {
                $data['reviewerid'] = $data['reviewer'];
            } else {
                $user = $DB->get_record('user', array('username' => $data['reviewer']), '*', MUST_EXIST);
                $data['reviewerid'] = $user->id;
            }
            unset($data['reviewer']);
        }

        if (isset($data['status'])) {
            switch ($data['status']) {
                case 'draft':
                    $status = plan::STATUS_DRAFT;
                    break;
                case 'in review':
                    $status = plan::STATUS_IN_REVIEW;
                    break;
                case 'waiting for review':
                    $status = plan::STATUS_WAITING_FOR_REVIEW;
                    break;
                case 'active':
                    $status = plan::STATUS_ACTIVE;
                    break;
                case 'complete':
                    $status = plan::STATUS_COMPLETE;
                    break;
                default:
                    throw new Exception('Could not resolve plan status with: "' . $data['status'] . '"');
                    break;
            }

            $data['status'] = $status;
        }

        return $data;
    }

    
    protected function preprocess_user_evidence($data) {
        global $DB;

        if (isset($data['user'])) {
            $user = $DB->get_record('user', array('username' => $data['user']), '*', MUST_EXIST);
            $data['userid'] = $user->id;
        }
        unset($data['user']);
        return $data;
    }

    
    protected function preprocess_plan_competency($data) {
        global $DB;

        if (isset($data['plan'])) {
            $plan = $DB->get_record(plan::TABLE, array('name' => $data['plan']), '*', MUST_EXIST);
            $data['planid'] = $plan->id;
        }
        unset($data['plan']);

        if (isset($data['competency'])) {
            $competency = $DB->get_record(competency::TABLE, array('shortname' => $data['competency']), '*', MUST_EXIST);
            $data['competencyid'] = $competency->id;
        }
        unset($data['competency']);
        return $data;
    }

    
    protected function preprocess_user_evidence_competency($data) {
        global $DB;

        if (isset($data['userevidence'])) {
            $userevidence = $DB->get_record(user_evidence::TABLE, array('name' => $data['userevidence']), '*', MUST_EXIST);
            $data['userevidenceid'] = $userevidence->id;
        }
        unset($data['userevidence']);

        if (isset($data['competency'])) {
            $competency = $DB->get_record(competency::TABLE, array('shortname' => $data['competency']), '*', MUST_EXIST);
            $data['competencyid'] = $competency->id;
        }
        unset($data['competency']);
        return $data;
    }

    
    protected function preprocess_user_competency($data) {
        global $DB;

        if (isset($data['user'])) {
            $user = $DB->get_record('user', array('username' => $data['user']), '*', MUST_EXIST);
            $data['userid'] = $user->id;
        }
        unset($data['user']);

        if (isset($data['competency'])) {
            $competency = $DB->get_record(competency::TABLE, array('shortname' => $data['competency']), '*', MUST_EXIST);
            $data['competencyid'] = $competency->id;
        }
        unset($data['competency']);

        return $data;
    }

    
    protected function preprocess_user_competency_plan($data) {
        global $DB;

        if (isset($data['user'])) {
            $user = $DB->get_record('user', array('username' => $data['user']), '*', MUST_EXIST);
            $data['userid'] = $user->id;
        }
        unset($data['user']);

        if (isset($data['competency'])) {
            $competency = $DB->get_record(competency::TABLE, array('shortname' => $data['competency']), '*', MUST_EXIST);
            $data['competencyid'] = $competency->id;
        }
        unset($data['competency']);

        if (isset($data['plan'])) {
            $plan = $DB->get_record(plan::TABLE, array('name' => $data['plan']), '*', MUST_EXIST);
            $data['planid'] = $plan->id;
        }
        unset($data['plan']);

        return $data;
    }

}
