<?php

class data_field_picture extends data_field_base {
    var $type = 'picture';
    var $previewwidth  = 50;
    var $previewheight = 50;

    function display_add_field($recordid = 0, $formdata = null) {
        global $CFG, $DB, $OUTPUT, $USER, $PAGE;

        $file        = false;
        $content     = false;
        $displayname = '';
        $alttext     = '';
        $itemid = null;
        $fs = get_file_storage();

        if ($formdata) {
            $fieldname = 'field_' . $this->field->id . '_file';
            $itemid = clean_param($formdata->$fieldname, PARAM_INT);
            $fieldname = 'field_' . $this->field->id . '_alttext';
            if (isset($formdata->$fieldname)) {
                $alttext = $formdata->$fieldname;
            }
        } else if ($recordid) {
            if ($content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                file_prepare_draft_area($itemid, $this->context->id, 'mod_data', 'content', $content->id);
                if (!empty($content->content)) {
                    if ($file = $fs->get_file($this->context->id, 'mod_data', 'content', $content->id, '/', $content->content)) {
                        $usercontext = context_user::instance($USER->id);
                        if (!$files = $fs->get_area_files($usercontext->id, 'user', 'draft', $itemid, 'id DESC', false)) {
                            return false;
                        }
                        if ($thumbfile = $fs->get_file($usercontext->id, 'user', 'draft', $itemid, '/', 'thumb_'.$content->content)) {
                            $thumbfile->delete();
                        }
                        if (empty($content->content1)) {
                                                        $src = moodle_url::make_draftfile_url($itemid, '/', $file->get_filename());
                            $displayname = $OUTPUT->pix_icon(file_file_icon($file), get_mimetype_description($file), 'moodle', array('class' => 'icon')). '<a href="'.$src.'" >'.s($file->get_filename()).'</a>';

                        } else {
                            $displayname = get_string('nofilesattached', 'repository');
                        }
                    }
                }
                $alttext = $content->content1;
            }
        } else {
            $itemid = file_get_unused_draft_itemid();
        }
        $str = '<div title="' . s($this->field->description) . '">';
        $str .= '<fieldset><legend><span class="accesshide">'.$this->field->name;

        if ($this->field->required) {
            $str .= '&nbsp;' . get_string('requiredelement', 'form') . '</span></legend>';
            $image = html_writer::img($OUTPUT->pix_url('req'), get_string('requiredelement', 'form'),
                                      array('class' => 'req', 'title' => get_string('requiredelement', 'form')));
            $str .= html_writer::div($image, 'inline-req');
        } else {
            $str .= '</span></legend>';
        }
        $str .= '<noscript>';
        if ($file) {
            $src = file_encode_url($CFG->wwwroot.'/pluginfile.php/', $this->context->id.'/mod_data/content/'.$content->id.'/'.$file->get_filename());
            $str .= '<img width="'.s($this->previewwidth).'" height="'.s($this->previewheight).'" src="'.$src.'" alt="" />';
        }
        $str .= '</noscript>';

        $options = new stdClass();
        $options->maxbytes  = $this->field->param3;
        $options->maxfiles  = 1;         $options->itemid    = $itemid;
        $options->accepted_types = array('web_image');
        $options->return_types = FILE_INTERNAL;
        $options->context = $PAGE->context;
        if (!empty($file)) {
            $options->filename = $file->get_filename();
            $options->filepath = '/';
        }

        $fm = new form_filemanager($options);
        
        $output = $PAGE->get_renderer('core', 'files');
        $str .= '<div class="mod-data-input">';
        $str .= $output->render($fm);

        $str .= '<div class="mdl-left">';
        $str .= '<input type="hidden" name="field_'.$this->field->id.'_file" value="'.s($itemid).'" />';
        $str .= '<label for="field_'.$this->field->id.'_alttext">'.get_string('alttext','data') .'</label>&nbsp;<input type="text" name="field_'
                .$this->field->id.'_alttext" id="field_'.$this->field->id.'_alttext" value="'.s($alttext).'" />';
        $str .= '</div>';
        $str .= '</div>';

        $str .= '</fieldset>';
        $str .= '</div>';

        return $str;
    }

    
    function get_file($recordid, $content=null) {
        global $DB;
        if (empty($content)) {
            if (!$content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                return null;
            }
        }
        $fs = get_file_storage();
        if (!$file = $fs->get_file($this->context->id, 'mod_data', 'content', $content->id, '/', $content->content)) {
            return null;
        }

        return $file;
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
        $name = "df_picture_$i";
        return array(" ({$tablealias}.fieldid = {$this->field->id} AND ".$DB->sql_like("{$tablealias}.content", ":$name", false).") ", array($name=>"%$value%"));
    }

    function display_browse_field($recordid, $template) {
        global $CFG, $DB;

        if (!$content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
            return false;
        }

        if (empty($content->content)) {
            return '';
        }

        $alt   = $content->content1;
        $title = $alt;

        if ($template == 'listtemplate') {
            $src = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$this->context->id.'/mod_data/content/'.$content->id.'/'.'thumb_'.$content->content);
                        $str = '<a href="view.php?d='.$this->field->dataid.'&amp;rid='.$recordid.'"><img src="'.$src.'" alt="'.s($alt).'" title="'.s($title).'" class="list_picture"/></a>';

        } else {
            $src = file_encode_url($CFG->wwwroot.'/pluginfile.php', '/'.$this->context->id.'/mod_data/content/'.$content->id.'/'.$content->content);
            $width  = $this->field->param1 ? ' width="'.s($this->field->param1).'" ':' ';
            $height = $this->field->param2 ? ' height="'.s($this->field->param2).'" ':' ';
            $str = '<a href="'.$src.'"><img '.$width.$height.' src="'.$src.'" alt="'.s($alt).'" title="'.s($title).'" class="list_picture"/></a>';
        }

        return $str;
    }

    function update_field() {
        global $DB, $OUTPUT;

                $oldfield = $DB->get_record('data_fields', array('id'=>$this->field->id));
        $DB->update_record('data_fields', $this->field);

                if ($oldfield && ($oldfield->param4 != $this->field->param4 || $oldfield->param5 != $this->field->param5)) {
                        if ($contents = $DB->get_records('data_content', array('fieldid'=>$this->field->id))) {
                $fs = get_file_storage();
                if (count($contents) > 20) {
                    echo $OUTPUT->notification(get_string('resizingimages', 'data'), 'notifysuccess');
                    echo "\n\n";
                                        ob_flush();
                }
                foreach ($contents as $content) {
                    if (!$file = $fs->get_file($this->context->id, 'mod_data', 'content', $content->id, '/', $content->content)) {
                        continue;
                    }
                    if ($thumbfile = $fs->get_file($this->context->id, 'mod_data', 'content', $content->id, '/', 'thumb_'.$content->content)) {
                        $thumbfile->delete();
                    }
                    core_php_time_limit::raise(300);
                                        $this->update_thumbnail($content, $file);
                }
            }
        }
        return true;
    }

    function update_content($recordid, $value, $name='') {
        global $CFG, $DB, $USER;

        if (!$content = $DB->get_record('data_content', array('fieldid'=>$this->field->id, 'recordid'=>$recordid))) {
                    $content = new stdClass();
            $content->fieldid  = $this->field->id;
            $content->recordid = $recordid;
            $id = $DB->insert_record('data_content', $content);
            $content = $DB->get_record('data_content', array('id'=>$id));
        }

        $names = explode('_', $name);
        switch ($names[2]) {
            case 'file':
                $fs = get_file_storage();
                file_save_draft_area_files($value, $this->context->id, 'mod_data', 'content', $content->id);
                $usercontext = context_user::instance($USER->id);
                $files = $fs->get_area_files(
                    $this->context->id,
                    'mod_data', 'content',
                    $content->id,
                    'itemid, filepath, filename',
                    false);

                                if (count($files) == 0) {
                    $content->content = null;
                } else {
                    $file = array_values($files)[0];

                    if (count($files) > 1) {
                                                debugging('more then one file found in mod_data instance {$this->data->id} picture field (field id: {$this->field->id}) area during update data record {$recordid} (content id: {$content->id})', DEBUG_NORMAL);
                    }

                    if ($file->get_imageinfo() === false) {
                        $url = new moodle_url('/mod/data/edit.php', array('d' => $this->field->dataid));
                        redirect($url, get_string('invalidfiletype', 'error', $file->get_filename()));
                    }
                    $content->content = $file->get_filename();
                    $this->update_thumbnail($content, $file);
                }
                $DB->update_record('data_content', $content);

                break;

            case 'alttext':
                                $content->content1 = clean_param($value, PARAM_NOTAGS);
                $DB->update_record('data_content', $content);
                break;

            default:
                break;
        }
    }

    function update_thumbnail($content, $file) {
                                $fs = get_file_storage();
        $file_record = array('contextid'=>$file->get_contextid(), 'component'=>$file->get_component(), 'filearea'=>$file->get_filearea(),
                             'itemid'=>$file->get_itemid(), 'filepath'=>$file->get_filepath(),
                             'filename'=>'thumb_'.$file->get_filename(), 'userid'=>$file->get_userid());
        try {
                        $fs->convert_image($file_record, $file, $this->field->param4, $this->field->param5, true);
            return true;
        } catch (Exception $e) {
            debugging($e->getMessage());
            return false;
        }
    }

    function text_export_supported() {
        return false;
    }

    function file_ok($path) {
        return true;
    }

    
    function notemptyfield($value, $name) {
        global $USER;

        $names = explode('_', $name);
        if ($names[2] == 'file') {
            $usercontext = context_user::instance($USER->id);
            $fs = get_file_storage();
            $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $value);
            return count($files) >= 2;
        }
        return false;
    }
}
