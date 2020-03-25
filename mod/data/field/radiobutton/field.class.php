<?php

class data_field_radiobutton extends data_field_base {

    var $type = 'radiobutton';

    function display_add_field($recordid = 0, $formdata = null) {
        global $CFG, $DB, $OUTPUT;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id;
            if (isset($formdata->$fieldname)) {
                $content = $formdata->$fieldname;
            } else {
                $content = '';
            }
        } else if ($recordid) {
            $content = trim($DB->get_field('data_content', 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid)));
        } else {
            $content = '';
        }

        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<fieldset><legend><span class="accesshide">' . $this->field->name;

        if ($this->field->required) {
            $str .= '&nbsp;' . get_string('requiredelement', 'form') . '</span></legend>';
            $image = html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                      array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
            $str .= html_writer::div($image, 'inline-req');
        } else {
            $str .= '</span></legend>';
        }

        $i = 0;
        $requiredstr = '';
        $options = explode("\n", $this->field->param1);
        foreach ($options as $radio) {
            $radio = trim($radio);
            if ($radio === '') {
                continue;             }
            $str .= '<input type="radio" id="field_'.$this->field->id.'_'.$i.'" name="field_' . $this->field->id . '" ';
            $str .= 'value="' . s($radio) . '" class="mod-data-input" ';

            if ($content == $radio) {
                                $str .= 'checked />';
            } else {
                $str .= '/>';
            }

            $str .= '<label for="field_'.$this->field->id.'_'.$i.'">'.$radio.'</label><br />';
            $i++;
        }
        $str .= '</fieldset>';
        $str .= '</div>';
        return $str;
    }

     function display_search_field($value = '') {
        global $CFG, $DB;

        $varcharcontent = $DB->sql_compare_text('content', 255);
        $used = $DB->get_records_sql(
            "SELECT DISTINCT $varcharcontent AS content
               FROM {data_content}
              WHERE fieldid=?
             ORDER BY $varcharcontent", array($this->field->id));

        $options = array();
        if(!empty($used)) {
            foreach ($used as $rec) {
                $options[$rec->content] = $rec->content;              }
        }
        $return = html_writer::label(get_string('nameradiobutton', 'data'), 'menuf_'. $this->field->id, false, array('class' => 'accesshide'));
        $return .= html_writer::select($options, 'f_'.$this->field->id, $value);
        return $return;
    }

    function parse_search_field() {
        return optional_param('f_'.$this->field->id, '', PARAM_NOTAGS);
    }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_radiobutton_$i";
        $varcharcontent = $DB->sql_compare_text("{$tablealias}.content", 255);

        return array(" ({$tablealias}.fieldid = {$this->field->id} AND $varcharcontent = :$name) ", array($name=>$value));
    }

    
    function notemptyfield($value, $name) {
        return strval($value) !== '';
    }
}

