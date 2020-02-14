<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/report/lib.php');


class gradereport_singleview extends grade_report {

    
    public static function valid_screens() {
                return array('user', 'select', 'grade');
    }

    
    public function process_data($data) {
        if (has_capability('moodle/grade:edit', $this->context)) {
            return $this->screen->process($data);
        }
    }

    
    public function process_action($target, $action) {
    }

    
    public function __construct($courseid, $gpr, $context, $itemtype, $itemid, $unused = null) {
        parent::__construct($courseid, $gpr, $context);

        $base = '/grade/report/singleview/index.php';

        $idparams = array('id' => $courseid);

        $this->baseurl = new moodle_url($base, $idparams);

        $this->pbarurl = new moodle_url($base, $idparams + array(
                'item' => $itemtype,
                'itemid' => $itemid
            ));

                $this->setup_groups();

        $screenclass = "\\gradereport_singleview\\local\\screen\\${itemtype}";

        $this->screen = new $screenclass($courseid, $itemid, $this->currentgroup);

                $this->screen->js();
    }

    
    public function output() {
        global $OUTPUT;
        return $OUTPUT->container($this->screen->html(), 'reporttable');
    }
}

