<?php




$functions = array(

    'mod_forum_get_forums_by_courses' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'get_forums_by_courses',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Returns a list of forum instances in a provided set of courses, if
            no courses are provided then all the forum instances the user has access to will be
            returned.',
        'type' => 'read',
        'capabilities' => 'mod/forum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_get_forum_discussion_posts' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'get_forum_discussion_posts',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Returns a list of forum posts for a discussion.',
        'type' => 'read',
        'capabilities' => 'mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_get_forum_discussions_paginated' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'get_forum_discussions_paginated',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Returns a list of forum discussions optionally sorted and paginated.',
        'type' => 'read',
        'capabilities' => 'mod/forum:viewdiscussion, mod/forum:viewqandawithoutposting',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_view_forum' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'view_forum',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Trigger the course module viewed event and update the module completion status.',
        'type' => 'write',
        'capabilities' => 'mod/forum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_view_forum_discussion' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'view_forum_discussion',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Trigger the forum discussion viewed event.',
        'type' => 'write',
        'capabilities' => 'mod/forum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_add_discussion_post' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'add_discussion_post',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Create new posts into an existing discussion.',
        'type' => 'write',
        'capabilities' => 'mod/forum:replypost',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_add_discussion' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'add_discussion',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Add a new discussion into an existing forum.',
        'type' => 'write',
        'capabilities' => 'mod/forum:startdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_forum_can_add_discussion' => array(
        'classname' => 'mod_forum_external',
        'methodname' => 'can_add_discussion',
        'classpath' => 'mod/forum/externallib.php',
        'description' => 'Check if the current user can add discussions in the given forum (and optionally for the given group).',
        'type' => 'read',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);
