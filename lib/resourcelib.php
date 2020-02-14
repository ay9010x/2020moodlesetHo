<?php




defined('MOODLE_INTERNAL') || die();


define('RESOURCELIB_DISPLAY_AUTO', 0);

define('RESOURCELIB_DISPLAY_EMBED', 1);

define('RESOURCELIB_DISPLAY_FRAME', 2);

define('RESOURCELIB_DISPLAY_NEW', 3);

define('RESOURCELIB_DISPLAY_DOWNLOAD', 4);

define('RESOURCELIB_DISPLAY_OPEN', 5);

define('RESOURCELIB_DISPLAY_POPUP', 6);


define('RESOURCELIB_LEGACYFILES_NO', 0);

define('RESOURCELIB_LEGACYFILES_DONE', 1);

define('RESOURCELIB_LEGACYFILES_ACTIVE', 2);



function resourcelib_try_file_migration($filepath, $cmid, $courseid, $component, $filearea, $itemid) {
    $fs = get_file_storage();

    if (stripos($filepath, '/backupdata/') === 0 or stripos($filepath, '/moddata/') === 0) {
                return false;
    }

    if (!$context = context_module::instance($cmid)) {
        return false;
    }
    if (!$coursecontext = context_course::instance($courseid)) {
        return false;
    }

    $fullpath = rtrim("/$coursecontext->id/course/legacy/0".$filepath, '/');
    do {
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            if ($file = $fs->get_file_by_hash(sha1("$fullpath/.")) and $file->is_directory()) {
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
                    break;
                }
            }
            return false;
        }
    } while (false);

        $file_record = array('contextid'=>$context->id, 'component'=>$component, 'filearea'=>$filearea, 'itemid'=>$itemid);
    try {
        return $fs->create_file_from_storedfile($file_record, $file);
    } catch (Exception $e) {
                return false;
    }
}


function resourcelib_get_displayoptions(array $enabled, $current=null) {
    if (is_number($current)) {
        $enabled[] = $current;
    }

    $options = array(RESOURCELIB_DISPLAY_AUTO     => get_string('resourcedisplayauto'),
                     RESOURCELIB_DISPLAY_EMBED    => get_string('resourcedisplayembed'),
                     RESOURCELIB_DISPLAY_FRAME    => get_string('resourcedisplayframe'),
                     RESOURCELIB_DISPLAY_NEW      => get_string('resourcedisplaynew'),
                     RESOURCELIB_DISPLAY_DOWNLOAD => get_string('resourcedisplaydownload'),
                     RESOURCELIB_DISPLAY_OPEN     => get_string('resourcedisplayopen'),
                     RESOURCELIB_DISPLAY_POPUP    => get_string('resourcedisplaypopup'));

    $result = array();

    foreach ($options as $key=>$value) {
        if (in_array($key, $enabled)) {
            $result[$key] = $value;
        }
    }

    if (empty($result)) {
                $result[RESOURCELIB_DISPLAY_OPEN] = $options[RESOURCELIB_DISPLAY_OPEN];
    }

    return $result;
}


function resourcelib_guess_url_mimetype($fullurl) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    if ($fullurl instanceof moodle_url) {
        $fullurl = $fullurl->out(false);
    }

    $matches = null;
    if (preg_match("|^(.*)/[a-z]*file.php(\?file=)?(/[^&\?#]*)|", $fullurl, $matches)) {
                $fullurl = $matches[1].$matches[3];
    }

    if (preg_match("|^(.*)#.*|", $fullurl, $matches)) {
                $fullurl = $matches[1];
    }

    if (strpos($fullurl, '.php')){
                return 'text/html';

    } else if (substr($fullurl, -1) === '/') {
                return 'text/html';

    } else if (strpos($fullurl, '//') !== false and substr_count($fullurl, '/') == 2) {
                return 'text/html';

    } else {
                $parts = explode('?', $fullurl);
        $url = reset($parts);
        return mimeinfo('type', $url);
    }
}


function resourcelib_get_extension($fullurl) {

    if ($fullurl instanceof moodle_url) {
        $fullurl = $fullurl->out(false);
    }

    $matches = null;
    if (preg_match("|^(.*)/[a-z]*file.php(\?file=)?(/.*)|", $fullurl, $matches)) {
                $fullurl = $matches[1].$matches[3];
    }

    $matches = null;
    if (preg_match('/^[^#\?]+\.([a-z0-9]+)([#\?].*)?$/i', $fullurl, $matches)) {
        return strtolower($matches[1]);
    }

    return '';
}


function resourcelib_embed_image($fullurl, $title) {
    $code = '';
    $code .= '<div class="resourcecontent resourceimg">';
    $code .= "<img title=\"".s(strip_tags(format_string($title)))."\" class=\"resourceimage\" src=\"$fullurl\" alt=\"\" />";
    $code .= '</div>';

    return $code;
}


function resourcelib_embed_pdf($fullurl, $title, $clicktoopen) {
    global $CFG, $PAGE;

    $code = <<<EOT
<div class="resourcecontent resourcepdf">
  <object id="resourceobject" data="$fullurl" type="application/pdf" width="800" height="600">
    <param name="src" value="$fullurl" />
    $clicktoopen
  </object>
</div>
EOT;

        $PAGE->requires->js_init_call('M.util.init_maximised_embed', array('resourceobject'), true);

    return $code;
}


function resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype) {
    global $CFG, $PAGE;

    if ($fullurl instanceof moodle_url) {
        $fullurl = $fullurl->out();
    }

    $param = '<param name="src" value="'.$fullurl.'" />';

            $code = <<<EOT
<div class="resourcecontent resourcegeneral">
  <iframe id="resourceobject" src="$fullurl">
    $clicktoopen
  </iframe>
</div>
EOT;

        $PAGE->requires->js_init_call('M.util.init_maximised_embed', array('resourceobject'), true);

    return $code;
}
