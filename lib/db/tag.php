<?php



defined('MOODLE_INTERNAL') || die();

$tagareas = array(
    array(
        'itemtype' => 'user',         'component' => 'core',
        'callback' => 'user_get_tagged_users',
        'callbackfile' => '/user/lib.php',
        'showstandard' => core_tag_tag::HIDE_STANDARD,
    ),
    array(
        'itemtype' => 'course',         'component' => 'core',
        'callback' => 'course_get_tagged_courses',
        'callbackfile' => '/course/lib.php',
    ),
    array(
        'itemtype' => 'question',         'component' => 'core_question',
    ),
    array(
        'itemtype' => 'post',         'component' => 'core',
        'callback' => 'blog_get_tagged_posts',
        'callbackfile' => '/blog/lib.php',
    ),
    array(
        'itemtype' => 'blog_external',         'component' => 'core',
    ),
    array(
        'itemtype' => 'course_modules',         'component' => 'core',
        'callback' => 'course_get_tagged_course_modules',
        'callbackfile' => '/course/lib.php',
    ),
);
