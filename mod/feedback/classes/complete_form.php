<?php



defined('MOODLE_INTERNAL') || die();


class mod_feedback_complete_form extends moodleform {

    
    const MODE_COMPLETE = 1;
    
    const MODE_PRINT = 2;
    
    const MODE_EDIT = 3;
    
    const MODE_VIEW_RESPONSE = 4;
    
    const MODE_VIEW_TEMPLATE = 5;

    
    protected $mode;
    
    protected $structure;
    
    protected $completion;
    
    protected $gopage;
    
    protected $hasrequired = false;

    
    public function __construct($mode, mod_feedback_structure $structure, $formid, $customdata = null) {
        $this->mode = $mode;
        $this->structure = $structure;
        $this->gopage = isset($customdata['gopage']) ? $customdata['gopage'] : 0;
        $isanonymous = $this->structure->is_anonymous() ? ' ianonymous' : '';
        parent::__construct(null, $customdata, 'POST', '',
                array('id' => $formid, 'class' => 'feedback_form' . $isanonymous), true);
    }

    
    public function definition() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'id', $this->get_cm()->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $this->get_current_course_id());
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement('hidden', 'gopage');
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'lastpage');
        $mform->setType('lastpage', PARAM_INT);
        $mform->addElement('hidden', 'startitempos');
        $mform->setType('startitempos', PARAM_INT);
        $mform->addElement('hidden', 'lastitempos');
        $mform->setType('lastitempos', PARAM_INT);

        if (isloggedin() && !isguestuser() && $this->mode != self::MODE_EDIT && $this->mode != self::MODE_VIEW_TEMPLATE &&
                    $this->mode != self::MODE_VIEW_RESPONSE) {
                        if ($this->structure->is_anonymous()) {
                $anonymousmodeinfo = get_string('anonymous', 'feedback');
            } else {
                $anonymousmodeinfo = get_string('non_anonymous', 'feedback');
            }
            $element = $mform->addElement('static', 'anonymousmode', '',
                    get_string('mode', 'feedback') . ': ' . $anonymousmodeinfo);
            $element->setAttributes($element->getAttributes() + ['class' => 'feedback_mode']);
        }

                if ($this->mode == self::MODE_COMPLETE) {
            $buttonarray = array();
            $buttonarray[] = &$mform->createElement('submit', 'gopreviouspage', get_string('previous_page', 'feedback'));
            $buttonarray[] = &$mform->createElement('submit', 'gonextpage', get_string('next_page', 'feedback'),
                    array('class' => 'form-submit'));
            $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('save_entries', 'feedback'),
                    array('class' => 'form-submit'));
            $buttonarray[] = &$mform->createElement('static', 'buttonsseparator', '', '<br>');
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        }

        if ($this->mode == self::MODE_COMPLETE) {
            $this->definition_complete();
        } else {
            $this->definition_preview();
        }

                $this->set_data(array('gopage' => $this->gopage));
    }

    
    protected function definition_complete() {
        if (!$this->structure instanceof mod_feedback_completion) {
                        return;
        }
        $pages = $this->structure->get_pages();
        $gopage = $this->gopage;
        $pageitems = $pages[$gopage];
        $hasnextpage = $gopage < count($pages) - 1;         $hasprevpage = $gopage && ($this->structure->get_previous_page($gopage, false) !== null);

                foreach ($pageitems as $item) {
            $itemobj = feedback_get_item_class($item->typ);
            $itemobj->complete_form_element($item, $this);
        }

                if (!$hasprevpage) {
            $this->remove_button('gopreviouspage');
        }
        if (!$hasnextpage) {
            $this->remove_button('gonextpage');
        }
        if ($hasnextpage) {
            $this->remove_button('savevalues');
        }
    }

    
    protected function definition_preview() {
        foreach ($this->structure->get_items() as $feedbackitem) {
            $itemobj = feedback_get_item_class($feedbackitem->typ);
            $itemobj->complete_form_element($feedbackitem, $this);
        }
    }

    
    private function remove_button($buttonname) {
        $el = $this->_form->getElement('buttonar');
        foreach ($el->_elements as $idx => $button) {
            if ($button instanceof MoodleQuickForm_submit && $button->getName() === $buttonname) {
                unset($el->_elements[$idx]);
                return;
            }
        }
    }

    
    public function get_item_value($item) {
        if ($this->structure instanceof mod_feedback_completion) {
            return $this->structure->get_item_value($item);
        }
        return null;
    }

    
    public function get_course_id() {
        return $this->structure->get_courseid();
    }

    
    public function get_feedback() {
        return $this->structure->get_feedback();
    }

    
    public function get_mode() {
        return $this->mode;
    }

    
    public function is_frozen() {
        return $this->mode == self::MODE_VIEW_RESPONSE;
    }

    
    public function get_cm() {
        return $this->structure->get_cm();
    }

    
    public function get_current_course_id() {
        return $this->structure->get_courseid() ?: $this->get_feedback()->course;
    }

    
    protected function get_suggested_class($item) {
        $class = "feedback_itemlist feedback-item-{$item->typ}";
        if ($item->dependitem) {
            $class .= " feedback_is_dependent";
        }
        if ($item->typ !== 'pagebreak') {
            $itemobj = feedback_get_item_class($item->typ);
            if ($itemobj->get_hasvalue()) {
                $class .= " feedback_hasvalue";
            }
        }
        return $class;
    }

    
    public function add_form_element($item, $element, $addrequiredrule = true, $setdefaultvalue = true) {
        global $OUTPUT;
                if (is_array($element)) {
            if ($this->is_frozen() && $element[0] === 'text') {
                                $element = ['static', $element[1], $element[2]];
            }
            $element = call_user_func_array(array($this->_form, 'createElement'), $element);
        }
        $element = $this->_form->addElement($element);

                $attributes = $element->getAttributes();
        $class = !empty($attributes['class']) ? ' ' . $attributes['class'] : '';
        $attributes['class'] = $this->get_suggested_class($item) . $class;
        $element->setAttributes($attributes);

                if ($item->required && $addrequiredrule) {
            $this->_form->addRule($element->getName(), get_string('required'), 'required', null, 'client');
        }

                if ($setdefaultvalue && ($tmpvalue = $this->get_item_value($item))) {
            $this->_form->setDefault($element->getName(), $tmpvalue);
        }

                if ($this->is_frozen()) {
            $element->freeze();
        }

                if ($item->required) {
            $required = '<img class="req" title="'.get_string('requiredelement', 'form').'" alt="'.
                    get_string('requiredelement', 'form').'" src="'.$OUTPUT->pix_url('req') .'" />';
            $element->setLabel($element->getLabel() . $required);
            $this->hasrequired = true;
        }

                $this->add_item_label($item, $element);
        $this->add_item_dependencies($item, $element);
        $this->add_item_number($item, $element);

        if ($this->mode == self::MODE_EDIT) {
            $this->enhance_name_for_edit($item, $element);
        }

        return $element;
    }

    
    public function add_form_group_element($item, $groupinputname, $name, $elements, $separator,
            $class = '') {
        $objects = array();
        foreach ($elements as $element) {
            $object = call_user_func_array(array($this->_form, 'createElement'), $element);
            $objects[] = $object;
        }
        $element = $this->add_form_element($item,
                ['group', $groupinputname, $name, $objects, $separator, false],
                false,
                false);
        if ($class !== '') {
            $attributes = $element->getAttributes();
            $attributes['class'] .= ' ' . $class;
            $element->setAttributes($attributes);
        }
        return $element;
    }

    
    protected function add_item_number($item, $element) {
        if ($this->get_feedback()->autonumbering && !empty($item->itemnr)) {
            $name = $element->getLabel();
            $element->setLabel(html_writer::span($item->itemnr. '.', 'itemnr') . ' ' . $name);
        }
    }

    
    protected function add_item_label($item, $element) {
        if (strlen($item->label) && ($this->mode == self::MODE_EDIT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            $name = $element->getLabel();
            $name = '('.format_string($item->label).') '.$name;
            $element->setLabel($name);
        }
    }

    
    protected function add_item_dependencies($item, $element) {
        $allitems = $this->structure->get_items();
        if ($item->dependitem && ($this->mode == self::MODE_EDIT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            if (isset($allitems[$item->dependitem])) {
                $dependitem = $allitems[$item->dependitem];
                $name = $element->getLabel();
                $name .= html_writer::span(' ('.format_string($dependitem->label).'-&gt;'.$item->dependvalue.')',
                        'feedback_depend');
                $element->setLabel($name);
            }
        }
    }

    
    protected function guess_element_id($item, $element) {
        if (!$id = $element->getAttribute('id')) {
            $attributes = $element->getAttributes();
            $id = $attributes['id'] = 'feedback_item_' . $item->id;
            $element->setAttributes($attributes);
        }
        if ($element->getType() === 'group') {
            return 'fgroup_' . $id;
        }
        return 'fitem_' . $id;
    }

    
    protected function enhance_name_for_edit($item, $element) {
        global $OUTPUT;
        $menu = new action_menu();
        $menu->set_owner_selector('#' . $this->guess_element_id($item, $element));
        $menu->set_constraint('.feedback_form');
        $menu->set_alignment(action_menu::TR, action_menu::BR);
        $menu->set_menu_trigger(get_string('edit'));
        $menu->do_not_enhance();
        $menu->prioritise = true;

        $itemobj = feedback_get_item_class($item->typ);
        $actions = $itemobj->edit_actions($item, $this->get_feedback(), $this->get_cm());
        foreach ($actions as $action) {
            $menu->add($action);
        }
        $editmenu = $OUTPUT->render($menu);

        $name = $element->getLabel();

        $name = html_writer::span('', 'itemdd', array('id' => 'feedback_item_box_' . $item->id)) .
                html_writer::span($name, 'itemname') .
                html_writer::span($editmenu, 'itemactions');
        $element->setLabel(html_writer::span($name, 'itemtitle'));
    }

    
    public function set_element_default($element, $defaultvalue) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->setDefault($element, $defaultvalue);
    }


    
    public function set_element_type($element, $type) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->setType($element, $type);
    }

    
    public function add_element_rule($element, $message, $type, $format = null, $validation = 'server',
            $reset = false, $force = false) {
        if ($element instanceof HTML_QuickForm_element) {
            $element = $element->getName();
        }
        $this->_form->addRule($element, $message, $type, $format, $validation, $reset, $force);
    }

    
    public function add_validation_rule(callable $callback) {
        if ($this->mode == self::MODE_COMPLETE) {
            $this->_form->addFormRule($callback);
        }
    }

    
    public function get_form_element($elementname) {
        return $this->_form->getElement($elementname);
    }

    
    public function display() {
        global $OUTPUT, $PAGE;
                if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }

        $mform = $this->_form;

                if (($mform->_required || $this->hasrequired) &&
               ($this->mode == self::MODE_COMPLETE || $this->mode == self::MODE_PRINT || $this->mode == self::MODE_VIEW_TEMPLATE)) {
            $element = $mform->addElement('static', 'requiredfields', '',
                    get_string('somefieldsrequired', 'form',
                            '<img alt="'.get_string('requiredelement', 'form').'" src="'.$OUTPUT->pix_url('req') .'" />'));
            $element->setAttributes($element->getAttributes() + ['class' => 'requirednote']);
        }

                $mform->_required = array();

                if ($this->mode == self::MODE_COMPLETE) {
            $mform->addElement('hidden', '__dummyelement');
            $buttons = $mform->removeElement('buttonar', false);
            $mform->insertElementBefore($buttons, '__dummyelement');
            $mform->removeElement('__dummyelement');
        }

        $this->_form->display();

        if ($this->mode == self::MODE_EDIT) {
            $PAGE->requires->js_call_amd('mod_feedback/edit', 'setup');
        }
    }
}
