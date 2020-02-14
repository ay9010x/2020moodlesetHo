<?php



namespace gradereport_singleview\local\screen;

use gradereport_singleview\local\ui\range;
use gradereport_singleview\local\ui\bulk_insert;
use grade_grade;
use grade_item;
use moodle_url;
use pix_icon;
use html_writer;
use gradereport_singleview;

defined('MOODLE_INTERNAL') || die;


class grade extends tablelike implements selectable_items, filterable_items {

    
    private $totalitemcount = 0;

    
    private $requiresextra = false;

    
    private $requirespaging = true;

    
    public static function allowcategories() {
        return get_config('moodle', 'grade_overridecat');
    }

    
    public static function filter($item) {
        return get_config('moodle', 'grade_overridecat') ||
                !($item->is_course_item() || $item->is_category_item());
    }

    
    public function select_label() {
        return get_string('selectuser', 'gradereport_singleview');
    }

    
    public function description() {
        return get_string('users');
    }

    
    public function options() {
        $options = array();
        foreach ($this->items as $userid => $user) {
            $options[$userid] = fullname($user);
        }

        return $options;
    }

    
    public function item_type() {
        return 'user';
    }

    
    public function original_definition() {
        $def = array('finalgrade', 'feedback');

        $def[] = 'override';

        $def[] = 'exclude';

        return $def;
    }

    
    public function init($selfitemisempty = false) {

        $this->items = $this->load_users();
        $this->totalitemcount = count($this->items);

        if ($selfitemisempty) {
            return;
        }

        $params = array(
            'id' => $this->itemid,
            'courseid' => $this->courseid
        );

        $this->item = grade_item::fetch($params);
        if (!self::filter($this->item)) {
            $this->items = array();
            $this->set_init_error(get_string('gradeitemcannotbeoverridden', 'gradereport_singleview'));
        }

        $this->requiresextra = !$this->item->is_manual_item();

        $this->setup_structure();

        $this->set_definition($this->original_definition());
        $this->set_headers($this->original_headers());
    }

    
    public function original_headers() {
        return array(
            '',             get_string('firstname') . ' (' . get_string('alternatename') . ') ' . get_string('lastname'),
            get_string('range', 'grades'),
            get_string('grade', 'grades'),
            get_string('feedback', 'grades'),
            $this->make_toggle_links('override'),
            $this->make_toggle_links('exclude')
        );
    }

    
    public function format_line($item) {
        global $OUTPUT;

        $grade = $this->fetch_grade_or_default($this->item, $item->id);

        $lockicon = '';

        $lockedgrade = $lockedgradeitem = 0;
        if (!empty($grade->locked)) {
            $lockedgrade = 1;
        }
        if (!empty($grade->grade_item->locked)) {
            $lockedgradeitem = 1;
        }
                if ( $lockedgrade || $lockedgradeitem ) {
            $lockicon = $OUTPUT->pix_icon('t/locked', 'grade is locked') . ' ';
        }

        if (!empty($item->alternatename)) {
            $fullname = $lockicon . $item->alternatename . ' (' . $item->firstname . ') ' . $item->lastname;
        } else {
            $fullname = $lockicon . fullname($item);
        }

        $item->imagealt = $fullname;
        $url = new moodle_url("/user/view.php", array('id' => $item->id, 'course' => $this->courseid));
        $iconstring = get_string('filtergrades', 'gradereport_singleview', $fullname);
        $grade->label = $fullname;

        $line = array(
            $OUTPUT->action_icon($this->format_link('user', $item->id), new pix_icon('t/editstring', $iconstring)),
            $OUTPUT->user_picture($item, array('visibletoscreenreaders' => false)) .
            html_writer::link($url, $fullname),
            $this->item_range()
        );
        $lineclasses = array(
            "action",
            "user",
            "range"
        );
        $outputline = array();
        $i = 0;
        foreach ($line as $key => $value) {
            $cell = new \html_table_cell($value);
            if ($isheader = $i == 1) {
                $cell->header = $isheader;
                $cell->scope = "row";
            }
            if (array_key_exists($key, $lineclasses)) {
                $cell->attributes['class'] = $lineclasses[$key];
            }
            $outputline[] = $cell;
            $i++;
        }

        return $this->format_definition($outputline, $grade);
    }

    
    public function item_range() {
        if (empty($this->range)) {
            $this->range = new range($this->item);
        }

        return $this->range;
    }

    
    public function supports_paging() {
        return $this->requirespaging;
    }

    
    public function pager() {
        global $OUTPUT;

        return $OUTPUT->paging_bar(
            $this->totalitemcount, $this->page, $this->perpage,
            new moodle_url('/grade/report/singleview/index.php', array(
                'perpage' => $this->perpage,
                'id' => $this->courseid,
                'group' => $this->groupid,
                'itemid' => $this->itemid,
                'item' => 'grade'
            ))
        );
    }

    
    public function heading() {
        return get_string('gradeitem', 'gradereport_singleview', $this->item->get_name());
    }

    
    public function summary() {
        return get_string('summarygrade', 'gradereport_singleview');
    }

    
    public function process($data) {
        $bulk = new bulk_insert($this->item);
                        if ($bulk->is_applied($data)) {
            $filter = $bulk->get_type($data);
            $insertvalue = $bulk->get_insert_value($data);
                        if ($this->supports_paging()) {
                $gradeitem = grade_item::fetch(array(
                    'courseid' => $this->courseid,
                    'id' => $this->item->id
                ));

                $null = $gradeitem->gradetype == GRADE_TYPE_SCALE ? -1 : '';

                foreach ($this->items as $itemid => $item) {
                    $field = "finalgrade_{$gradeitem->id}_{$itemid}";
                    if (isset($data->$field)) {
                        continue;
                    }

                    $grade = grade_grade::fetch(array(
                        'itemid' => $gradeitem->id,
                        'userid' => $itemid
                    ));

                    $data->$field = empty($grade) ? $null : $grade->finalgrade;
                    $data->{"old$field"} = $data->$field;
                }
            }

            foreach ($data as $varname => $value) {
                if (preg_match('/^oldoverride_(\d+)_(\d+)/', $varname, $matches)) {
                                        if ($filter == 'all') {
                        $override = "override_{$matches[1]}_{$matches[2]}";
                        $data->$override = '1';
                    }
                }
                if (!preg_match('/^finalgrade_(\d+)_/', $varname, $matches)) {
                    continue;
                }

                $gradeitem = grade_item::fetch(array(
                    'courseid' => $this->courseid,
                    'id' => $matches[1]
                ));

                $isscale = ($gradeitem->gradetype == GRADE_TYPE_SCALE);

                $empties = (trim($value) === '' or ($isscale and $value == -1));

                if ($filter == 'all' or $empties) {
                    $data->$varname = ($isscale and empty($insertvalue)) ?
                        -1 : $insertvalue;
                }
            }
        }

        return parent::process($data);
    }

}
