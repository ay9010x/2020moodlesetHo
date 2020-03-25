<?php



$handlers = array (
    'test_instant' => array (
        'handlerfile'      => '/lib/tests/eventslib_test.php',
        'handlerfunction'  => 'eventslib_sample_function_handler',
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

    'test_cron' => array (
        'handlerfile'      => '/lib/tests/eventslib_test.php',
        'handlerfunction'  => array('eventslib_sample_handler_class', 'static_method'),
        'schedule'         => 'cron',
        'internal'         => 1,
    ),

    'test_legacy' => array (
        'handlerfile'      => '/lib/tests/event_test.php',
        'handlerfunction'  => '\core_tests\event\unittest_observer::legacy_handler',
        'schedule'         => 'instant',
        'internal'         => 1,
    ),

);

