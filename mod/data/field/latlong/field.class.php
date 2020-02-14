<?php

class data_field_latlong extends data_field_base {
    var $type = 'latlong';

                
            
    var $linkoutservices = array(
          "Google Maps" => "http://maps.google.com/maps?q=@lat@,+@long@&iwloc=A&hl=en",
          "Google Earth" => "@wwwroot@/mod/data/field/latlong/kml.php?d=@dataid@&fieldid=@fieldid@&rid=@recordid@",
          "Geabios" => "http://www.geabios.com/html/services/maps/PublicMap.htm?lat=@lat@&lon=@long@&fov=0.3&title=Moodle%20data%20item",
          "OpenStreetMap" => "http://www.openstreetmap.org/index.html?lat=@lat@&lon=@long@&zoom=11",
          "Multimap" => "http://www.multimap.com/map/browse.cgi?scale=200000&lon=@long@&lat=@lat@&icon=x"
    );
    
    function display_add_field($recordid = 0, $formdata = null) {
        global $CFG, $DB, $OUTPUT;

        $lat = '';
        $long = '';
        if ($formdata) {
            $fieldname = 'field_' . $this->field->id . '_0';
            $lat = $formdata->$fieldname;
            $fieldname = 'field_' . $this->field->id . '_1';
            $long = $formdata->$fieldname;
        } else if ($recordid) {
            if ($content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                $lat  = $content->content;
                $long = $content->content1;
            }
        }
        $str = '<div title="'.s($this->field->description).'">';
        $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name.'</span></legend>';
        $str .= '<table><tr><td align="right">';
        $str .= '<label for="field_'.$this->field->id.'_0" class="mod-data-input">' . get_string('latitude', 'data');
        if ($this->field->required) {
            $str .= html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                     array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
        }
        $str .= '</label></td><td><input type="text" name="field_'.$this->field->id.'_0" id="field_'.$this->field->id.'_0" value="';
        $str .= s($lat).'" size="10" />°N</td></tr>';
        $str .= '<tr><td align="right"><label for="field_'.$this->field->id.'_1" class="mod-data-input">';
        $str .= get_string('longitude', 'data');
        if ($this->field->required) {
            $str .= html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                     array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
        }
        $str .= '</label></td><td><input type="text" name="field_'.$this->field->id.'_1" id="field_'.$this->field->id.'_1" value="';
        $str .= s($long).'" size="10" />°E</td>';
        $str .= '</tr>';
        $str .= '</table>';
        $str .= '</fieldset>';
        $str .= '</div>';
        return $str;
    }

    function display_search_field($value = '') {
        global $CFG, $DB;

        $varcharlat = $DB->sql_compare_text('content');
        $varcharlong= $DB->sql_compare_text('content1');
        $latlongsrs = $DB->get_recordset_sql(
            "SELECT DISTINCT $varcharlat AS la, $varcharlong AS lo
               FROM {data_content}
              WHERE fieldid = ?
             ORDER BY $varcharlat, $varcharlong", array($this->field->id));

        $options = array();
        foreach ($latlongsrs as $latlong) {
            $latitude = format_float($latlong->la, 4);
            $longitude = format_float($latlong->lo, 4);
            $options[$latlong->la . ',' . $latlong->lo] = $latitude . ' ' . $longitude;
        }
        $latlongsrs->close();

        $return = html_writer::label(get_string('latlong', 'data'), 'menuf_'.$this->field->id, false, array('class' => 'accesshide'));
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
        $name1 = "df_latlong1_$i";
        $name2 = "df_latlong2_$i";
        $varcharlat = $DB->sql_compare_text("{$tablealias}.content");
        $varcharlong= $DB->sql_compare_text("{$tablealias}.content1");


        $latlong[0] = '';
        $latlong[1] = '';
        $latlong = explode (',', $value, 2);
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND $varcharlat = :$name1 AND $varcharlong = :$name2) ",
                     array($name1=>$latlong[0], $name2=>$latlong[1]));
    }

    function display_browse_field($recordid, $template) {
        global $CFG, $DB;
        if ($content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $lat = $content->content;
            if (strlen($lat) < 1) {
                return false;
            }
            $long = $content->content1;
            if (strlen($long) < 1) {
                return false;
            }
                        if($lat < 0) {
                $compasslat = format_float(-$lat, 4) . '°S';
            } else {
                $compasslat = format_float($lat, 4) . '°N';
            }
            if($long < 0) {
                $compasslong = format_float(-$long, 4) . '°W';
            } else {
                $compasslong = format_float($long, 4) . '°E';
            }

                        $servicesshown = explode(',', $this->field->param1);

                        $urlreplacements = array(
                '@lat@'=> $lat,
                '@long@'=> $long,
                '@wwwroot@'=> $CFG->wwwroot,
                '@contentid@'=> $content->id,
                '@dataid@'=> $this->data->id,
                '@courseid@'=> $this->data->course,
                '@fieldid@'=> $content->fieldid,
                '@recordid@'=> $content->recordid,
            );

            if(sizeof($servicesshown)==1 && $servicesshown[0]) {
                $str = " <a href='"
                          . str_replace(array_keys($urlreplacements), array_values($urlreplacements), $this->linkoutservices[$servicesshown[0]])
                          ."' title='$servicesshown[0]'>$compasslat $compasslong</a>";
            } elseif (sizeof($servicesshown)>1) {
                $str = '<form id="latlongfieldbrowse">';
                $str .= "$compasslat, $compasslong\n";
                $str .= "<label class='accesshide' for='jumpto'>". get_string('jumpto') ."</label>";
                $str .= "<select id='jumpto' name='jumpto'>";
                foreach($servicesshown as $servicename){
                                        $str .= "\n  <option value='"
                               . str_replace(array_keys($urlreplacements), array_values($urlreplacements), $this->linkoutservices[$servicename])
                               . "'>".htmlspecialchars($servicename)."</option>";
                }
                                                $str .= "\n</select><input type='button' value='" . get_string('go') . "' onclick='if(previousSibling.value){self.location=previousSibling.value}'/>";
                $str .= '</form>';
            } else {
                $str = "$compasslat, $compasslong";
            }

            return $str;
        }
        return false;
    }

    function update_content($recordid, $value, $name='') {
        global $DB;

        $content = new stdClass();
        $content->fieldid = $this->field->id;
        $content->recordid = $recordid;
                        $value = unformat_float($value);
        $value = trim($value);
        if (strlen($value) > 0) {
            $value = floatval($value);
        } else {
            $value = null;
        }
        $names = explode('_', $name);
        switch ($names[2]) {
            case 0:
                                $content->content = $value;
                break;
            case 1:
                                $content->content1 = $value;
                break;
            default:
                break;
        }
        if ($oldcontent = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            $content->id = $oldcontent->id;
            return $DB->update_record('data_content', $content);
        } else {
            return $DB->insert_record('data_content', $content);
        }
    }

    function get_sort_sql($fieldname) {
        global $DB;
        return $DB->sql_cast_char2real($fieldname, true);
    }

    function export_text_value($record) {
                return sprintf('%01.4f', $record->content) . ' ' . sprintf('%01.4f', $record->content1);
    }

    
    function notemptyfield($value, $name) {
        return isset($value) && !($value == '');
    }

    
    public function field_validation($values) {
        $valuecount = 0;
                foreach ($values as $value) {
            if (isset($value->value) && !($value->value == '')) {
                $valuecount++;
            }
        }
                if ($valuecount == 0 || $valuecount == 2) {
            return false;
        }
                return get_string('latlongboth', 'data');
    }
}
