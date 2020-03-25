<?php

class data_field_menu extends data_field_base {

    var $type = 'menu';

    function display_add_field($recordid = 0, $formdata = null) {
        global $DB, $OUTPUT;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            $content = $formdata->$fieldname;
        } else if ($recordid) {
            $content = $DB->get_field('data_content', 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid));
            $content = trim($content);
        } else {
            $content = '';
        }
        $str = '<div title="' . s($this->field->description) . '">';

        $options = array();
        $rawoptions = explode("\n",$this->field->param1);
        foreach ($rawoptions as $option) {
            $option = trim($option);
            if (strlen($option) > 0) {
                $options[$option] = $option;
            }
        }

        $str .= '<label for="' . 'field_' . $this->field->id . '">';
        $str .= html_writer::span($this->field->name, 'accesshide');
        if ($this->field->required) {
            $image = html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                     array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
            $str .= html_writer::div($image, 'inline-req');
        }
        $str .= '</label>';
        $str .= html_writer::select($options, 'field_'.$this->field->id, $content, array('' => get_string('menuchoose', 'data')),
                                    array('id' => 'field_'.$this->field->id, 'class' => 'mod-data-input'));

        $str .= '</div>';

        return $str;
    }

    function display_search_field($content = '') {
        global $CFG, $DB;

        $varcharcontent =  $DB->sql_compare_text('content', 255);
        $sql = "SELECT DISTINCT $varcharcontent AS content
                  FROM {data_content}
                 WHERE fieldid=? AND content IS NOT NULL";

        $usedoptions = array();
        if ($used = $DB->get_records_sql($sql, array($this->field->id))) {
            foreach ($used as $data) {
                $value = $data->content;
                if ($value === '') {
                    continue;
                }
                $usedoptions[$value] = $value;
            }
        }

        $options = array();
        foreach (explode("\n",$this->field->param1) as $option) {
            $option = trim($option);
            if (!isset($usedoptions[$option])) {
                continue;
            }
            $options[$option] = $option;
        }
        if (!$options) {
                        return '';
        }

        $return = html_writer::label(get_string('namemenu', 'data'), 'menuf_'. $this->field->id, false, array('class' => 'accesshide'));
        $return .= html_writer::select($options, 'f_'.$this->field->id, $content);
        return $return;
    }

     function parse_search_field() {
            return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
     }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_menu_$i";
        $varcharcontent = $DB->sql_compare_text("{$tablealias}.content", 255);

        return array(" ({$tablealias}.fieldid = {$this->field->id} AND $varcharcontent = :$name) ", array($name=>$value));
    }

    
    function notemptyfield($value, $name) {
        return strval($value) !== '';
    }

}
