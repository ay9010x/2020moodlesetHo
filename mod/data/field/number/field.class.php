<?php

class data_field_number extends data_field_base {
    var $type = 'number';

    function update_content($recordid, $value, $name='') {
        global $DB;

        $content = new stdClass();
        $content->fieldid = $this->field->id;
        $content->recordid = $recordid;
        $value = trim($value);
        if (strlen($value) > 0) {
            $content->content = floatval($value);
        } else {
            $content->content = null;
        }
        if ($oldcontent = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $content->id = $oldcontent->id;
            return $DB->update_record('data_content', $content);
        } else {
            return $DB->insert_record('data_content', $content);
        }
    }

    function display_browse_field($recordid, $template) {
        global $DB;

        if ($content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            if (strlen($content->content) < 1) {
                return false;
            }
            $number = $content->content;
            $decimals = trim($this->field->param1);
                        if (preg_match("/^\d+$/", $decimals)) {
                $decimals = $decimals * 1;
                                $str = format_float($number, $decimals, true);
                            } else {
                $str = $number;
            }
            return $str;
        }
        return false;
    }

    function display_search_field($value = '') {
        return '<label class="accesshide" for="f_'.$this->field->id.'">' . get_string('fieldname', 'data') . '</label>' .
               '<input type="text" size="16" id="f_'.$this->field->id.'" name="f_'.$this->field->id.'" value="'.s($value).'" />';
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }

        function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_number_$i";
        $varcharcontent = $DB->sql_compare_text("{$tablealias}.content");
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND $varcharcontent = :$name) ", array($name=>$value));
    }

    function get_sort_sql($fieldname) {
        global $DB;
        return $DB->sql_cast_char2real($fieldname, true);
    }

    
    function notemptyfield($value, $name) {
        return strval($value) !== '';
    }
}


