<?php

class data_field_text extends data_field_base {

    var $type = 'text';

    function display_search_field($value = '') {
        return '<label class="accesshide" for="f_' . $this->field->id . '">'. $this->field->name.'</label>' . '<input type="text" size="16" id="f_'.$this->field->id.'" name="f_'.$this->field->id.'" value="'.s($value).'" />';
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_text_$i";
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND ".$DB->sql_like("{$tablealias}.content", ":$name", false).") ", array($name=>"%$value%"));
    }

    
    function notemptyfield($value, $name) {
        return strval($value) !== '';
    }
}


