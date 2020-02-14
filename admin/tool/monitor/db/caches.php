<?php



defined('MOODLE_INTERNAL') || die;

$definitions = array(
    'eventsubscriptions' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 10
    )
);
