<?php



$observers = array(
    array(
        'eventname'   => '\mod_assign\event\submission_created',
        'callback'    => '\assignfeedback_editpdf\event\observer::submission_created',
    ),
    array(
        'eventname'   => '\mod_assign\event\submission_updated',
        'callback'    => '\assignfeedback_editpdf\event\observer::submission_updated',
    ),
);
