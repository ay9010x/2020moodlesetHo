<?php


require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$eventname = required_param('eventname', PARAM_RAW);

admin_externalpage_setup('reporteventlists');

$completelist = report_eventlist_list_generator::get_all_events_list(false);

if (!array_key_exists($eventname, $completelist)) {
    print_error('errorinvalidevent', 'report_eventlist');
}

$component = explode('\\', $eventname);
$directory = core_component::get_component_directory($component[1]);

$directory = $directory . '/classes/event';
if (!is_dir($directory)) {
    print_error('errorinvaliddirectory', 'report_eventlist');
}
$filename = end($component);
$eventfiles = $directory . '/' . $filename . '.php';
$title = $eventname::get_name();

$eventinformation = array('title' => $title);
$eventcontents = file_get_contents($eventfiles);
$eventinformation['filecontents'] = $eventcontents;

$ref = new \ReflectionClass($eventname);
$eventinformation['explanation'] = $eventname::get_explanation($eventname);
if (!$ref->isAbstract()) {
    $eventinformation = array_merge($eventinformation, $eventname::get_static_info());
    $eventinformation['legacyevent'] = $eventname::get_legacy_eventname();
    $eventinformation['crud'] = report_eventlist_list_generator::get_crud_string($eventinformation['crud']);
    $eventinformation['edulevel'] = report_eventlist_list_generator::get_edulevel_string($eventinformation['edulevel']);
} else {
    $eventinformation['abstract'] = true;
    if ($eventname != '\core\event\base') {
                        $crudpattern = "/(\['crud'\]\s=\s')(\w)/";
        $result = array();
        preg_match($crudpattern, $eventcontents, $result);
        if (!empty($result[2])) {
            $eventinformation['crud'] = report_eventlist_list_generator::get_crud_string($result[2]);
        }

                $edulevelpattern = "/(\['edulevel'\]\s=\sself\:\:)(\w*)/";
        $result = array();
        preg_match($edulevelpattern, $eventcontents, $result);
        if (!empty($result[2])) {
            $educationlevel = constant('\core\event\base::' . $result[2]);
            $eventinformation['edulevel'] = report_eventlist_list_generator::get_edulevel_string($educationlevel);
        }

                $affectedtablepattern = "/(\['objecttable'\]\s=\s')(\w*)/";
        $result = array();
        preg_match($affectedtablepattern, $eventcontents, $result);
        if (!empty($result[2])) {
            $eventinformation['objecttable'] = $result[2];
        }
    }
}

$othertypepattern = "/(@type\s([\w|\s|.]*))+/";
$typeparams = array();
preg_match_all($othertypepattern, $eventcontents, $typeparams);
if (!empty($typeparams[2])) {
    $eventinformation['typeparameter'] = array();
    foreach ($typeparams[2] as $typeparameter) {
        $eventinformation['typeparameter'][] = $typeparameter;
    }
}

$otherpattern = "/(\*\s{5}-([\w|\s]*\:[\w|\s|\(|\)|.]*))/";
$typeparams = array();
preg_match_all($otherpattern, $eventcontents, $typeparams);
if (!empty($typeparams[2])) {
    $eventinformation['otherparameter'] = array();
    foreach ($typeparams[2] as $typeparameter) {
        $eventinformation['otherparameter'][] = $typeparameter;
    }
}

if ($parentclass = get_parent_class($eventname)) {
    $eventinformation['parentclass'] = '\\' . $parentclass;
}

$allobserverslist = report_eventlist_list_generator::get_observer_list();
$observers = array();

if (isset($allobserverslist['\\core\\event\\base'])) {
    $observers = $allobserverslist['\\core\\event\\base'];
}
if (isset($allobserverslist[$eventname])) {
    $observers = array_merge($observers, $allobserverslist[$eventname]);
}

$renderer = $PAGE->get_renderer('report_eventlist');
echo $renderer->render_event_detail($observers, $eventinformation);

