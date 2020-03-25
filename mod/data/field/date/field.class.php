<?php


class data_field_date extends data_field_base {

    var $type = 'date';

    var $day   = 0;
    var $month = 0;
    var $year  = 0;

    function display_add_field($recordid = 0, $formdata = null) {
        global $DB, $OUTPUT;

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id . '_day';
            $day   = $formdata->$fieldname;
            $fieldname = 'field_' . $this->field->id . '_month';
            $month   = $formdata->$fieldname;
            $fieldname = 'field_' . $this->field->id . '_year';
            $year   = $formdata->$fieldname;

            $calendartype = \core_calendar\type_factory::get_calendar_instance();
            $gregoriandate = $calendartype->convert_to_gregorian($year, $month, $day);
            $content = make_timestamp(
                $gregoriandate['year'],
                $gregoriandate['month'],
                $gregoriandate['day'],
                $gregoriandate['hour'],
                $gregoriandate['minute'],
                0,
                0,
                false);
        } else if ($recordid) {
            $content = (int)$DB->get_field('data_content', 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid));
        } else {
            $content = time();
        }

        $str = '<div title="'.s($this->field->description).'" class="mod-data-input">';
        $dayselector = html_writer::select_time('days', 'field_'.$this->field->id.'_day', $content);
        $monthselector = html_writer::select_time('months', 'field_'.$this->field->id.'_month', $content);
        $yearselector = html_writer::select_time('years', 'field_'.$this->field->id.'_year', $content);
        $str .= $dayselector . $monthselector . $yearselector;
        $str .= '</div>';

        return $str;
    }

        function display_search_field($value=0) {
        $selectors = html_writer::select_time('days', 'f_'.$this->field->id.'_d', $value['timestamp'])
           . html_writer::select_time('months', 'f_'.$this->field->id.'_m', $value['timestamp'])
           . html_writer::select_time('years', 'f_'.$this->field->id.'_y', $value['timestamp']);
        $datecheck = html_writer::checkbox('f_'.$this->field->id.'_z', 1, $value['usedate']);
        $str = $selectors . ' ' . $datecheck . ' ' . get_string('usedate', 'data');

        return $str;
    }

    function generate_sql($tablealias, $value) {
        global $DB;

        static $i=0;
        $i++;
        $name = "df_date_$i";
        $varcharcontent = $DB->sql_compare_text("{$tablealias}.content");
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND $varcharcontent = :$name) ", array($name => $value['timestamp']));
    }

    function parse_search_field() {
        $day   = optional_param('f_'.$this->field->id.'_d', 0, PARAM_INT);
        $month = optional_param('f_'.$this->field->id.'_m', 0, PARAM_INT);
        $year  = optional_param('f_'.$this->field->id.'_y', 0, PARAM_INT);
        $usedate = optional_param('f_'.$this->field->id.'_z', 0, PARAM_INT);
        $data = array();
        if (!empty($day) && !empty($month) && !empty($year) && $usedate == 1) {
            $calendartype = \core_calendar\type_factory::get_calendar_instance();
            $gregoriandate = $calendartype->convert_to_gregorian($year, $month, $day);

            $data['timestamp'] = make_timestamp(
                $gregoriandate['year'],
                $gregoriandate['month'],
                $gregoriandate['day'],
                $gregoriandate['hour'],
                $gregoriandate['minute'],
                0,
                0,
                false);
            $data['usedate'] = 1;
            return $data;
        } else {
            return 0;
        }
    }

    function update_content($recordid, $value, $name='') {
        global $DB;

        $names = explode('_',$name);
        $name = $names[2];          
        $this->$name = $value;

        if ($this->day and $this->month and $this->year) {  
            $content = new stdClass();
            $content->fieldid = $this->field->id;
            $content->recordid = $recordid;

            $calendartype = \core_calendar\type_factory::get_calendar_instance();
            $gregoriandate = $calendartype->convert_to_gregorian($this->year, $this->month, $this->day);
            $content->content = make_timestamp(
                $gregoriandate['year'],
                $gregoriandate['month'],
                $gregoriandate['day'],
                $gregoriandate['hour'],
                $gregoriandate['minute'],
                0,
                0,
                false);

            if ($oldcontent = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                $content->id = $oldcontent->id;
                return $DB->update_record('data_content', $content);
            } else {
                return $DB->insert_record('data_content', $content);
            }
        }
    }

    function display_browse_field($recordid, $template) {
        global $CFG, $DB;

        if ($content = $DB->get_field('data_content', 'content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            return userdate($content, get_string('strftimedate'), 0);
        }
    }

    function get_sort_sql($fieldname) {
        global $DB;
        return $DB->sql_cast_char2int($fieldname, true);
    }


}
