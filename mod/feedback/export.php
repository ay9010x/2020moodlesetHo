<?php



require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

$url = new moodle_url('/mod/feedback/export.php', array('id'=>$id));
if ($action !== false) {
    $url->param('action', $action);
}
$PAGE->set_url($url);

if (! $cm = get_coursemodule_from_id('feedback', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $feedback = $DB->get_record("feedback", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);

require_capability('mod/feedback:edititems', $context);

if ($action == 'exportfile') {
    if (!$exportdata = feedback_get_xml_data($feedback->id)) {
        print_error('nodata');
    }
    @feedback_send_xml_data($exportdata, 'feedback_'.$feedback->id.'.xml');
    exit;
}

redirect('view.php?id='.$id);
exit;

function feedback_get_xml_data($feedbackid) {
    global $DB;

    $space = '     ';
        if (!$items = $DB->get_records('feedback_item', array('feedback'=>$feedbackid), 'position')) {
        return false;
    }

        $data = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
    $data .= '<FEEDBACK VERSION="200701" COMMENT="XML-Importfile for mod/feedback">'."\n";
    $data .= $space.'<ITEMS>'."\n";

        foreach ($items as $item) {
                $data .= $space.$space.'<ITEM TYPE="'.$item->typ.'" REQUIRED="'.$item->required.'">'."\n";

                $data .= $space.$space.$space.'<ITEMID>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->id;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</ITEMID>'."\n";

                $data .= $space.$space.$space.'<ITEMTEXT>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->name;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</ITEMTEXT>'."\n";

                $data .= $space.$space.$space.'<ITEMLABEL>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->label;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</ITEMLABEL>'."\n";

                $data .= $space.$space.$space.'<PRESENTATION>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->presentation;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</PRESENTATION>'."\n";

                $data .= $space.$space.$space.'<OPTIONS>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->options;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</OPTIONS>'."\n";

                $data .= $space.$space.$space.'<DEPENDITEM>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->dependitem;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</DEPENDITEM>'."\n";

                $data .= $space.$space.$space.'<DEPENDVALUE>'."\n";
                $data .= $space.$space.$space.$space.'<![CDATA[';
        $data .= $item->dependvalue;
                $data .= ']]>'."\n";
                $data .= $space.$space.$space.'</DEPENDVALUE>'."\n";

                $data .= $space.$space.'</ITEM>'."\n";
    }

        $data .= $space.'</ITEMS>'."\n";
    $data .= '</FEEDBACK>'."\n";

    return $data;
}

function feedback_send_xml_data($data, $filename) {
    @header('Content-Type: application/xml; charset=UTF-8');
    @header('Content-Disposition: attachment; filename="'.$filename.'"');
    print($data);
}
