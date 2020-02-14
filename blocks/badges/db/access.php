<?php



$capabilities = array(
        'block/badges:addinstance' => array(
                'captype'      => 'read',
                'contextlevel' => CONTEXT_BLOCK,
                'archetypes' => array(
                    'editingteacher' => CAP_ALLOW,
                    'manager' => CAP_ALLOW
                ),
                'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),
        'block/badges:myaddinstance' => array(
                'riskbitmask'  => RISK_PERSONAL,
                'captype'      => 'read',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes'   => array(
                        'user' => CAP_ALLOW,
                ),
                'clonepermissionsfrom' => 'moodle/my:manageblocks'
        ),
);