<?php



defined('MOODLE_INTERNAL') || die();


function events_get_cached($component) {
    global $DB;

    $cachedhandlers = array();

    if ($storedhandlers = $DB->get_records('events_handlers', array('component'=>$component))) {
        foreach ($storedhandlers as $handler) {
            $cachedhandlers[$handler->eventname] = array (
                'id'              => $handler->id,
                'handlerfile'     => $handler->handlerfile,
                'handlerfunction' => $handler->handlerfunction,
                'schedule'        => $handler->schedule,
                'internal'        => $handler->internal);
        }
    }

    return $cachedhandlers;
}


function events_uninstall($component) {
    $cachedhandlers = events_get_cached($component);
    events_cleanup($component, $cachedhandlers);

    events_get_handlers('reset');
}


function events_cleanup($component, $cachedhandlers) {
    global $DB;

    $deletecount = 0;
    foreach ($cachedhandlers as $eventname => $cachedhandler) {
        if ($qhandlers = $DB->get_records('events_queue_handlers', array('handlerid'=>$cachedhandler['id']))) {
                        foreach ($qhandlers as $qhandler) {
                events_dequeue($qhandler);
            }
        }
        $DB->delete_records('events_handlers', array('eventname'=>$eventname, 'component'=>$component));
        $deletecount++;
    }

    return $deletecount;
}


function events_dequeue($qhandler) {
    global $DB;

        $DB->delete_records('events_queue_handlers', array('id'=>$qhandler->id));

        if (!$DB->record_exists('events_queue_handlers', array('queuedeventid'=>$qhandler->queuedeventid))) {
        $DB->delete_records('events_queue', array('id'=>$qhandler->queuedeventid));
    }
}


function events_get_handlers($eventname) {
    global $DB;
    static $handlers = array();

    if ($eventname === 'reset') {
        $handlers = array();
        return false;
    }

    if (!array_key_exists($eventname, $handlers)) {
        $handlers[$eventname] = $DB->get_records('events_handlers', array('eventname'=>$eventname));
    }

    return $handlers[$eventname];
}
