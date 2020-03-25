<?php



defined('MOODLE_INTERNAL') || die();


class core_role_check_capability_table extends core_role_capability_table_base {
    protected $user;
    protected $fullname;
    protected $contextname;
    protected $stryes;
    protected $strno;
    private $hascap;

    
    public function __construct($context, $user, $contextname) {
        parent::__construct($context, 'explaincaps');
        $this->user = $user;
        $this->fullname = fullname($user);
        $this->contextname = $contextname;
        $this->stryes = get_string('yes');
        $this->strno = get_string('no');
    }

    protected function add_header_cells() {
        echo '<th>' . get_string('allowed', 'core_role') . '</th>';
    }

    protected function num_extra_columns() {
        return 1;
    }

    protected function get_row_classes($capability) {
        $this->hascap = has_capability($capability->name, $this->context, $this->user->id);
        if ($this->hascap) {
            return array('yes');
        } else {
            return array('no');
        }
    }

    protected function add_row_cells($capability) {
        if ($this->hascap) {
            $result = $this->stryes;
        } else {
            $result = $this->strno;
        }
        $a = new stdClass;
        $a->fullname = $this->fullname;
        $a->capability = $capability->name;
        $a->context = $this->contextname;
        return '<td>' . $result . '</td>';
    }
}
