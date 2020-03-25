<?php

abstract class feedback_item_base {

    
    protected $type;

    
    protected $item_form;

    
    protected $item;

    
    public function __construct() {
    }

    
    public function show_editform() {
        $this->item_form->display();
    }

    
    public function is_cancelled() {
        return $this->item_form->is_cancelled();
    }

    
    public function get_data() {
        if ($this->item = $this->item_form->get_data()) {
            return true;
        }
        return false;
    }

    
    abstract public function build_editform($item, $feedback, $cm);

    
    abstract public function save_item();

    
    public function create_value($value) {
        return strval($value);
    }

    
    public function compare_value($item, $dbvalue, $dependvalue) {
        return strval($dbvalue) === strval($dependvalue);
    }

    
    public function get_hasvalue() {
        return 1;
    }

    
    public function can_switch_require() {
        return true;
    }

    
    abstract public function excelprint_item(&$worksheet, $row_offset,
                                      $xls_formats, $item,
                                      $groupid, $courseid = false);

    
    abstract public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false);

    
    abstract public function get_printval($item, $value);

    
    public function get_display_name($item, $withpostfix = true) {
        return format_text($item->name, FORMAT_HTML, array('noclean' => true, 'para' => false)) .
                ($withpostfix ? $this->get_display_name_postfix($item) : '');
    }

    
    public function get_display_name_postfix($item) {
        return '';
    }

    
    abstract public function complete_form_element($item, $form);

    
    public function edit_actions($item, $feedback, $cm) {
        $actions = array();

        $strupdate = get_string('edit_item', 'feedback');
        $actions['update'] = new action_menu_link_secondary(
            new moodle_url('/mod/feedback/edit_item.php', array('id' => $item->id)),
            new pix_icon('t/edit', $strupdate, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strupdate,
            array('class' => 'editing_update', 'data-action' => 'update')
        );

        if ($this->can_switch_require()) {
            if ($item->required == 1) {
                $buttontitle = get_string('switch_item_to_not_required', 'feedback');
                $buttonimg = 'required';
            } else {
                $buttontitle = get_string('switch_item_to_required', 'feedback');
                $buttonimg = 'notrequired';
            }
            $actions['required'] = new action_menu_link_secondary(
                new moodle_url('/mod/feedback/edit.php', array('id' => $cm->id,
                    'switchitemrequired' => $item->id, 'sesskey' => sesskey())),
                new pix_icon($buttonimg, $buttontitle, 'feedback', array('class' => 'iconsmall', 'title' => '')),
                $buttontitle,
                array('class' => 'editing_togglerequired', 'data-action' => 'togglerequired')
            );
        }

        $strdelete = get_string('delete_item', 'feedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/feedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete', 'data-action' => 'delete')
        );

        return $actions;
    }
}

class feedback_item_pagebreak extends feedback_item_base {
    protected $type = "pagebreak";

    public function show_editform() {
    }

    
    public function is_cancelled() {
    }
    public function get_data() {
    }
    public function build_editform($item, $feedback, $cm) {
    }
    public function save_item() {
    }
    public function create_value($data) {
    }
    public function get_hasvalue() {
        return 0;
    }
    public function excelprint_item(&$worksheet, $row_offset,
                            $xls_formats, $item,
                            $groupid, $courseid = false) {
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
    }
    public function get_printval($item, $value) {
    }
    public function can_switch_require() {
        return false;
    }

    
    public function complete_form_element($item, $form) {
        $form->add_form_element($item,
                ['static', $item->typ.'_'.$item->id, '', '<hr class="feedback_pagebreak">']);
    }

    
    public function edit_actions($item, $feedback, $cm) {
        $actions = array();
        $strdelete = get_string('delete_pagebreak', 'feedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/feedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete', 'data-action' => 'delete')
        );
        return $actions;
    }
}
