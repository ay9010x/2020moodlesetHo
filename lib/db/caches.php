<?php



$definitions = array(

                'string' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 30,
        'canuselocalstore' => true,
    ),

        'langmenu' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'canuselocalstore' => true,
    ),

                    'databasemeta' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'requireidentifiers' => array(
            'dbfamily'
        ),
        'simpledata' => true,         'staticacceleration' => true,
        'staticaccelerationsize' => 15
    ),

                                'eventinvalidation' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'requiredataguarantee' => true,
        'simpledata' => true,
    ),

                'questiondata' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,         'requiredataguarantee' => false,
        'datasource' => 'question_finder',
        'datasourcefile' => 'question/engine/bank.php',
    ),

                    'htmlpurifier' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'canuselocalstore' => true,
    ),

                        'config' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'simpledata' => true
    ),

                'groupdata' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,         'simpledata' => true,         'staticacceleration' => true,         'staticaccelerationsize' => 2,     ),

        'calendar_subscriptions' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
    ),

        'capabilities' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 1,
        'ttl' => 3600,     ),

            'yuimodules' => array(
        'mode' => cache_store::MODE_APPLICATION,
    ),

        'observers' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 2,
    ),

            'plugin_manager' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
    ),

        'coursecattree' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'invalidationevents' => array(
            'changesincoursecat',
        )
    ),
        'coursecat' => array(
        'mode' => cache_store::MODE_SESSION,
        'invalidationevents' => array(
            'changesincoursecat',
            'changesincourse',
        ),
        'ttl' => 600,
    ),
        'coursecatrecords' => array(
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'invalidationevents' => array(
            'changesincoursecat',
        ),
    ),
        'coursecontacts' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'staticacceleration' => true,
        'simplekeys' => true,
        'ttl' => 3600,
    ),
        'repositories' => array(
        'mode' => cache_store::MODE_REQUEST,
    ),
        'externalbadges' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'ttl' => 3600,
    ),
            'coursemodinfo' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'canuselocalstore' => true,
    ),
                    'userselections' => array(
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true,
        'simpledata' => true
    ),

        'completion' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'ttl' => 3600,
        'staticacceleration' => true,
        'staticaccelerationsize' => 2,     ),

                    'navigation_expandcourse' => array(
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true,
        'simpledata' => true
    ),

            'suspended_userids' => array(
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'simpledata' => true,
    ),

            'plugin_functions' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 5
    ),

        'tags' => array(
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'staticacceleration' => true,
    ),

        'grade_categories' => array(
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true,
        'invalidationevents' => array(
            'changesingradecategories',
        )
    ),

        'temp_tables' => array(
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'simpledata' => true
    ),

        'tagindexbuilder' => array(
        'mode' => cache_store::MODE_SESSION,
        'simplekeys' => true,
        'simplevalues' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 10,
        'ttl' => 900,         'invalidationevents' => array(
            'resettagindexbuilder',
        ),
    ),
);
