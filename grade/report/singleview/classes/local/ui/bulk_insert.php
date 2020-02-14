<?php



namespace gradereport_singleview\local\ui;

use html_writer;

defined('MOODLE_INTERNAL') || die;


class bulk_insert extends element {

    
    public function __construct($item) {
        $this->name = 'bulk_' . $item->id;
        $this->applyname = $this->name_for('apply');
        $this->selectname = $this->name_for('type');
        $this->insertname = $this->name_for('value');
    }

    
    public function is_applied($data) {
        return isset($data->{$this->applyname});
    }

    
    public function get_type($data) {
        return $data->{$this->selectname};
    }

    
    public function get_insert_value($data) {
        return $data->{$this->insertname};
    }

    
    public function html() {
        $insertvalue = get_string('bulkinsertvalue', 'gradereport_singleview');
        $insertappliesto = get_string('bulkappliesto', 'gradereport_singleview');

        $insertoptions = array(
            'all' => get_string('all_grades', 'gradereport_singleview'),
            'blanks' => get_string('blanks', 'gradereport_singleview')
        );

        $selectlabel = html_writer::label(
            $insertappliesto,
            'menu' . $this->selectname
        );
        $select = html_writer::select(
            $insertoptions,
            $this->selectname,
            'blanks',
            false,
            array(
                'id' => 'menu' . $this->selectname
            )
        );

        $textlabel = html_writer::label(
            $insertvalue,
            $this->insertname
        );
        $text = new text_attribute($this->insertname, "0", 'bulk');

        $inner = implode(' ', array(
            $selectlabel,
            $select,
            $textlabel,
            $text->html()
        ));

        $fieldset = html_writer::tag(
            'fieldset',
            html_writer::tag(
                'legend',
                get_string('bulklegend', 'gradereport_singleview'),
                array(
                    'class' => 'accesshide'
                )
            ) .
            $inner
        );

        $apply = html_writer::checkbox(
            $this->applyname,
            1,
            false,
            get_string('bulkperform', 'gradereport_singleview')
        );
        $applydiv = html_writer::div($apply, 'enable');

        return $applydiv . $fieldset;
    }

    
    private function name_for($extend) {
        return "{$this->name}_$extend";
    }
}
