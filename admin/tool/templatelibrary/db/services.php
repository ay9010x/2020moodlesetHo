<?php



$functions = array(

    'tool_templatelibrary_list_templates' => array(
        'classname'   => 'tool_templatelibrary\external',
        'methodname'  => 'list_templates',
        'classpath'   => '',
        'description' => 'List/search templates by component.',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => true,
        'loginrequired' => false,
    ),
    'tool_templatelibrary_load_canonical_template' => array(
        'classname'   => 'tool_templatelibrary\external',
        'methodname'  => 'load_canonical_template',
        'description' => 'Load a canonical template by name (not the theme overidden one).',
        'type'        => 'read',
        'ajax'        => true,
        'loginrequired' => false,
    ),

);

