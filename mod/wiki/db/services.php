<?php



$functions = array(

    'mod_wiki_get_wikis_by_courses' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_wikis_by_courses',
        'description'   => 'Returns a list of wiki instances in a provided set of courses, if ' .
                           'no courses are provided then all the wiki instances the user has access to will be returned.',
        'type'          => 'read',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_view_wiki' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'view_wiki',
        'description'   => 'Trigger the course module viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_view_page' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'view_page',
        'description'   => 'Trigger the page viewed event and update the module completion status.',
        'type'          => 'write',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_get_subwikis' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_subwikis',
        'description'   => 'Returns the list of subwikis the user can see in a specific wiki.',
        'type'          => 'read',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'mod_wiki_get_subwiki_pages' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_subwiki_pages',
        'description'   => 'Returns the list of pages for a specific subwiki.',
        'type'          => 'read',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_get_subwiki_files' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_subwiki_files',
        'description'   => 'Returns the list of files for a specific subwiki.',
        'type'          => 'read',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_get_page_contents' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_page_contents',
        'description'   => 'Returns the contents of a page.',
        'type'          => 'read',
        'capabilities'  => 'mod/wiki:viewpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_get_page_for_editing' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'get_page_for_editing',
        'description'   => 'Locks and retrieves info of page-section to be edited.',
        'type'          => 'write',
        'capabilities'  => 'mod/wiki:editpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_new_page' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'new_page',
        'description'   => 'Create a new page in a subwiki.',
        'type'          => 'write',
        'capabilities'  => 'mod/wiki:editpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),

    'mod_wiki_edit_page' => array(
        'classname'     => 'mod_wiki_external',
        'methodname'    => 'edit_page',
        'description'   => 'Save the contents of a page.',
        'type'          => 'write',
        'capabilities'  => 'mod/wiki:editpage',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
