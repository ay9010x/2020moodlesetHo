<?php




defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/resource/lib.php");


function resource_redirect_if_migrated($oldid, $cmid) {
    global $DB, $CFG;

    if ($oldid) {
        $old = $DB->get_record('resource_old', array('oldid'=>$oldid));
    } else {
        $old = $DB->get_record('resource_old', array('cmid'=>$cmid));
    }

    if (!$old) {
        return;
    }

    redirect("$CFG->wwwroot/mod/$old->newmodule/view.php?id=".$old->cmid);
}


function resource_display_embed($resource, $cm, $course, $file) {
    global $CFG, $PAGE, $OUTPUT;

    $clicktoopen = resource_get_clicktoopen($file, $resource->revision);

    $context = context_module::instance($cm->id);
    $path = '/'.$context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
    $moodleurl = new moodle_url('/pluginfile.php' . $path);

    $mimetype = $file->get_mimetype();
    $title    = $resource->name;

    $extension = resourcelib_get_extension($file->get_filename());

    $mediarenderer = $PAGE->get_renderer('core', 'media');
    $embedoptions = array(
        core_media::OPTION_TRUSTED => true,
        core_media::OPTION_BLOCK => true,
    );

    if (file_mimetype_in_typegroup($mimetype, 'web_image')) {          $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mimetype === 'application/pdf') {
                $code = resourcelib_embed_pdf($fullurl, $title, $clicktoopen);

    } else if ($mediarenderer->can_embed_url($moodleurl, $embedoptions)) {
                $code = $mediarenderer->embed_url($moodleurl, $title, 0, 0, $embedoptions);

    } else {
                $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    resource_print_header($resource, $cm, $course);
    resource_print_heading($resource, $cm, $course);

    echo $code;

    resource_print_intro($resource, $cm, $course);

    echo $OUTPUT->footer();
    die;
}


function resource_display_frame($resource, $cm, $course, $file) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        resource_print_header($resource, $cm, $course);
        resource_print_heading($resource, $cm, $course);
        resource_print_intro($resource, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('resource');
        $context = context_module::instance($cm->id);
        $path = '/'.$context->id.'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
        $fileurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
        $navurl = "$CFG->wwwroot/mod/resource/view.php?id=$cm->id&amp;frameset=top";
        $title = strip_tags(format_string($course->shortname.': '.$resource->name));
        $framesize = $config->framesize;
        $contentframetitle = s(format_string($resource->name));
        $modulename = s(get_string('modulename','resource'));
        $dir = get_string('thisdirection', 'langconfig');

        $file = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename" />
    <frame src="$fileurl" title="$contentframetitle" />
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $file;
        die;
    }
}


function resource_get_clicktoopen($file, $revision, $extra='') {
    global $CFG;

    $filename = $file->get_filename();
    $path = '/'.$file->get_contextid().'/mod_resource/content/'.$revision.$file->get_filepath().$file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);

    $string = get_string('clicktoopen2', 'resource', "<a href=\"$fullurl\" $extra>$filename</a>");

    return $string;
}


function resource_get_clicktodownload($file, $revision) {
    global $CFG;

    $filename = $file->get_filename();
    $path = '/'.$file->get_contextid().'/mod_resource/content/'.$revision.$file->get_filepath().$file->get_filename();
    $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, true);

    $string = get_string('clicktodownload', 'resource', "<a href=\"$fullurl\">$filename</a>");

    return $string;
}


function resource_print_workaround($resource, $cm, $course, $file) {
    global $CFG, $OUTPUT;

    resource_print_header($resource, $cm, $course);
    resource_print_heading($resource, $cm, $course, true);
    resource_print_intro($resource, $cm, $course, true);
    
    $resource->mainfile = $file->get_filename();
    echo '<div class="resourceworkaround">';
    switch (resource_get_final_display_type($resource)) {
        case RESOURCELIB_DISPLAY_POPUP:
            $path = '/'.$file->get_contextid().'/mod_resource/content/'.$resource->revision.$file->get_filepath().$file->get_filename();
            $fullurl = file_encode_url($CFG->wwwroot.'/pluginfile.php', $path, false);
            $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
            $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
            $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
            $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
            $extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";
            echo resource_get_clicktoopen($file, $resource->revision, $extra);
            break;

        case RESOURCELIB_DISPLAY_NEW:
            $extra = 'onclick="this.target=\'_blank\'"';
            echo resource_get_clicktoopen($file, $resource->revision, $extra);
            break;

        case RESOURCELIB_DISPLAY_DOWNLOAD:
                        $context = context_module::instance($cm->id);
            mod_resource_standard_log_download($resource, $course, $cm, $context);
            echo resource_get_clicktodownload($file, $resource->revision);
            break;

        case RESOURCELIB_DISPLAY_OPEN:
        default:
            echo resource_get_clicktoopen($file, $resource->revision);
            break;
    }
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}


function resource_print_header($resource, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$resource->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($resource);
    $PAGE->set_button(update_module_button($cm->id, '', get_string('modulename', 'resource')));
    echo $OUTPUT->header();
}


function resource_print_heading($resource, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($resource->name), 2);
}



function resource_get_file_details($resource, $cm) {
    $options = empty($resource->displayoptions) ? array() : @unserialize($resource->displayoptions);
    $filedetails = array();
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);
                                $mainfile = $files ? reset($files) : null;
        if (!empty($options['showsize'])) {
            $filedetails['size'] = 0;
            foreach ($files as $file) {
                                $filedetails['size'] += $file->get_filesize();
                if ($file->get_repository_id()) {
                                        $filedetails['isref'] = true;
                }
            }
        }
        if (!empty($options['showtype'])) {
            if ($mainfile) {
                $filedetails['type'] = get_mimetype_description($mainfile);
                                if ($filedetails['type'] === get_mimetype_description('document/unknown')) {
                    $filedetails['type'] = '';
                }
            } else {
                $filedetails['type'] = '';
            }
        }
        if (!empty($options['showdate'])) {
            if ($mainfile) {
                                                if ($mainfile->get_timemodified() > $mainfile->get_timecreated() + 5 * MINSECS) {
                    $filedetails['modifieddate'] = $mainfile->get_timemodified();
                } else {
                    $filedetails['uploadeddate'] = $mainfile->get_timecreated();
                }
                if ($mainfile->get_repository_id()) {
                                        $filedetails['isref'] = true;
                }
            } else {
                $filedetails['uploadeddate'] = '';
            }
        }
    }
    return $filedetails;
}


function resource_get_optional_details($resource, $cm) {
    global $DB;

    $details = '';

    $options = empty($resource->displayoptions) ? array() : @unserialize($resource->displayoptions);
    if (!empty($options['showsize']) || !empty($options['showtype']) || !empty($options['showdate'])) {
        if (!array_key_exists('filedetails', $options)) {
            $filedetails = resource_get_file_details($resource, $cm);
        } else {
            $filedetails = $options['filedetails'];
        }
        $size = '';
        $type = '';
        $date = '';
        $langstring = '';
        $infodisplayed = 0;
        if (!empty($options['showsize'])) {
            if (!empty($filedetails['size'])) {
                $size = display_size($filedetails['size']);
                $langstring .= 'size';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showtype'])) {
            if (!empty($filedetails['type'])) {
                $type = $filedetails['type'];
                $langstring .= 'type';
                $infodisplayed += 1;
            }
        }
        if (!empty($options['showdate']) && (!empty($filedetails['modifieddate']) || !empty($filedetails['uploadeddate']))) {
            if (!empty($filedetails['modifieddate'])) {
                $date = get_string('modifieddate', 'mod_resource', userdate($filedetails['modifieddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            } else if (!empty($filedetails['uploadeddate'])) {
                $date = get_string('uploadeddate', 'mod_resource', userdate($filedetails['uploadeddate'],
                    get_string('strftimedatetimeshort', 'langconfig')));
            }
            $langstring .= 'date';
            $infodisplayed += 1;
        }

        if ($infodisplayed > 1) {
            $details = get_string("resourcedetails_{$langstring}", 'resource',
                    (object)array('size' => $size, 'type' => $type, 'date' => $date));
        } else {
                        $details = $size . $type . $date;
        }
    }

    return $details;
}


function resource_print_intro($resource, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);

    $extraintro = resource_get_optional_details($resource, $cm);
    if ($extraintro) {
                $extraintro = html_writer::tag('p', $extraintro, array('class' => 'resourcedetails'));
    }

    if ($ignoresettings || !empty($options['printintro']) || $extraintro) {
        $gotintro = trim(strip_tags($resource->intro));
        if ($gotintro || $extraintro) {
            echo $OUTPUT->box_start('mod_introbox', 'resourceintro');
            if ($gotintro) {
                echo format_module_intro('resource', $resource, $cm->id);
            }
            echo $extraintro;
            echo $OUTPUT->box_end();
        }
    }
}


function resource_print_tobemigrated($resource, $cm, $course) {
    global $DB, $OUTPUT;

    $resource_old = $DB->get_record('resource_old', array('oldid'=>$resource->id));
    resource_print_header($resource, $cm, $course);
    resource_print_heading($resource, $cm, $course);
    resource_print_intro($resource, $cm, $course);
    echo $OUTPUT->notification(get_string('notmigrated', 'resource', $resource_old->type));
    echo $OUTPUT->footer();
    die;
}


function resource_print_filenotfound($resource, $cm, $course) {
    global $DB, $OUTPUT;

    $resource_old = $DB->get_record('resource_old', array('oldid'=>$resource->id));
    resource_print_header($resource, $cm, $course);
    resource_print_heading($resource, $cm, $course);
    resource_print_intro($resource, $cm, $course);
    if ($resource_old) {
        echo $OUTPUT->notification(get_string('notmigrated', 'resource', $resource_old->type));
    } else {
        echo $OUTPUT->notification(get_string('filenotfound', 'resource'));
    }
    echo $OUTPUT->footer();
    die;
}


function resource_get_final_display_type($resource) {
    global $CFG, $PAGE;

    if ($resource->display != RESOURCELIB_DISPLAY_AUTO) {
        return $resource->display;
    }

    if (empty($resource->mainfile)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    } else {
        $mimetype = mimeinfo('type', $resource->mainfile);
    }

    if (file_mimetype_in_typegroup($mimetype, 'archive')) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (file_mimetype_in_typegroup($mimetype, array('web_image', '.htm', 'web_video', 'web_audio'))) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

        return RESOURCELIB_DISPLAY_OPEN;
}


class resource_content_file_info extends file_info_stored {
    public function get_parent() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->browser->get_file_info($this->context);
        }
        return parent::get_parent();
    }
    public function get_visible_name() {
        if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
            return $this->topvisiblename;
        }
        return parent::get_visible_name();
    }
}

function resource_set_mainfile($data) {
    global $DB;
    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_resource', 'content', 0, array('subdirs'=>true));
    }
    $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
                $file = reset($files);
        file_set_sortorder($context->id, 'mod_resource', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
}
