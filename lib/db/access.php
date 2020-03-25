<?php



defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'moodle/site:config' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG | RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        )
    ),

    'moodle/site:readallmessages' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW
        )
    ),

    'moodle/site:deleteanymessage' => array(

        'riskbitmask' => RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:sendmessage' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'moodle/site:deleteownmessage' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),

    'moodle/site:approvecourse' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/backup:backupcourse' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/site:backup'
    ),

    'moodle/backup:backupsection' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/backup:backupcourse'
    ),

    'moodle/backup:backupactivity' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/backup:backupcourse'
    ),

    'moodle/backup:backuptargethub' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/backup:backupcourse'
    ),

    'moodle/backup:backuptargetimport' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/backup:backupcourse'
    ),

    'moodle/backup:downloadfile' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/site:backupdownload'
    ),

    'moodle/backup:configure' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/backup:userinfo' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/backup:anonymise' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/restore:restorecourse' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/site:restore'
    ),

    'moodle/restore:restoresection' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/restore:restorecourse'
    ),

    'moodle/restore:restoreactivity' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/restore:restorecourse'
    ),

    'moodle/restore:viewautomatedfilearea' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
    ),

    'moodle/restore:restoretargethub' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/restore:restorecourse'
    ),

    'moodle/restore:restoretargetimport' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/site:import'
    ),

    'moodle/restore:uploadfile' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),

        'clonepermissionsfrom' =>  'moodle/site:backupupload'
    ),

    'moodle/restore:configure' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/restore:rolldates' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        )
    ),

    'moodle/restore:userinfo' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/restore:createuser' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:manageblocks' => array(

        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:accessallgroups' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:viewfullnames' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

                'moodle/site:viewuseridentity' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:viewreports' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:trustcontent' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:uploadusers' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

        'moodle/filter:manage' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'moodle/user:create' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:delete' => array(

        'riskbitmask' => RISK_PERSONAL, RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:update' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:viewdetails' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:viewalldetails' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/user:update'
    ),

    'moodle/user:viewlastip' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/user:update'
    ),

    'moodle/user:viewhiddendetails' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:loginas' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

        'moodle/user:managesyspages' => array(

        'riskbitmap' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

        'moodle/user:manageblocks' => array(

        'riskbitmap' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_USER
    ),

        'moodle/user:manageownblocks' => array(

        'riskbitmap' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),

        'moodle/user:manageownfiles' => array(

        'riskbitmap' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),

            'moodle/user:ignoreuserquota' => array(
        'riskbitmap' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'clonepermissionsfrom' => 'moodle/course:ignorefilesizelimits'
    ),

        'moodle/my:configsyspages' => array(

        'riskbitmap' => RISK_SPAM | RISK_PERSONAL | RISK_CONFIG,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/role:assign' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/role:review' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/role:override' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/role:safeoverride' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

    'moodle/role:manage' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/role:switchroles' => array(

        'riskbitmask' => RISK_XSS | RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

                    'moodle/category:manage' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/category:update'
    ),

    'moodle/category:viewhiddencategories' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/category:visibility'
    ),

            'moodle/cohort:manage' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW
        )
    ),

        'moodle/cohort:assign' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW
        )
    ),

        'moodle/cohort:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        )
    ),

    'moodle/course:create' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'coursecreator' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
        )
    ),

    'moodle/course:request' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW,
        )
    ),

    'moodle/course:delete' => array(

        'riskbitmask' => RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:update' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
        )
    ),

    
    'moodle/course:enrolreview' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    
    'moodle/course:enrolconfig' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW
        )
    ),

    'moodle/course:reviewotherusers' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'departmentassistant' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/role:assign'
    ),

    'moodle/course:bulkmessaging' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

    'moodle/course:viewhiddenuserfields' => array(

        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:viewhiddencourses' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'coursecreator' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            
        )
    ),

    'moodle/course:visibility' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:managefiles' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:ignorefilesizelimits' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
        )
    ),

    'moodle/course:manageactivities' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:activityvisibility' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:viewhiddenactivities' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:viewparticipants' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:changefullname' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/course:changeshortname' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/course:renameroles' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/course:changeidnumber' => array(

        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),
    'moodle/course:changecategory' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/course:changesummary' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),


    'moodle/site:viewparticipants' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:isincompletionreports' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
        ),
    ),

    'moodle/course:viewscales' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:managescales' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:managegroups' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:reset' => array(

        'riskbitmask' => RISK_DATALOSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:viewsuspendedusers' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:tag' => array(
        'riskbitmask' => RISK_SPAM,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/blog:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'user' => CAP_ALLOW,
            'guest' => CAP_ALLOW            
        )
    ),

    'moodle/blog:search' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'user' => CAP_ALLOW,
            'guest' => CAP_ALLOW            
        )
    ),

    'moodle/blog:viewdrafts' => array(

        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/blog:create' => array( 
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/blog:manageentries' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/blog:manageexternal' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'moodle/calendar:manageownentries' => array( 
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/calendar:managegroupentries' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        )
    ),

    'moodle/calendar:manageentries' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        )
    ),

    'moodle/user:editprofile' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,

        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:editownprofile' => array(

        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'guest' => CAP_PROHIBIT,
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/user:changeownpassword' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'guest' => CAP_PROHIBIT,
            'user' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

            'moodle/user:readuserposts' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW
        )
    ),

    'moodle/user:readuserblogs' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW            
        )
    ),

        'moodle/user:viewuseractivitiesreport' => array(
        'riskbitmask' => RISK_PERSONAL,

        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
        )
    ),

        'moodle/user:editmessageprofile' => array(

         'riskbitmask' => RISK_SPAM,

         'captype' => 'write',
         'contextlevel' => CONTEXT_USER,
         'archetypes' => array(
             'manager' => CAP_ALLOW
         )
     ),

     'moodle/user:editownmessageprofile' => array(

         'captype' => 'write',
         'contextlevel' => CONTEXT_SYSTEM,
         'archetypes' => array(
             'guest' => CAP_PROHIBIT,
             'user' => CAP_ALLOW,
             'manager' => CAP_ALLOW
         )
     ),

    'moodle/question:managecategory' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

        'moodle/question:add' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:editmine' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:editall' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:viewmine' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:viewall' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:usemine' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:useall' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:movemine' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    'moodle/question:moveall' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' =>  'moodle/question:manage'
    ),
    
        'moodle/question:config' => array(
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

        'moodle/question:flag' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/site:doclinks' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:sectionvisibility' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:useremail' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:viewhiddensections' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:setcurrentsection' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/course:movesections' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:update'
    ),

    'moodle/site:mnetlogintoremote' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        )
    ),

    'moodle/grade:viewall' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,         'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'moodleset' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:viewcoursegrades'
    ),

    'moodle/grade:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'auditor' => CAP_ALLOW,
            'student' => CAP_ALLOW
        )
    ),

    'moodle/grade:viewhidden' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:viewcoursegrades'
    ),

    'moodle/grade:import' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

    'moodle/grade:export' => array(
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

    'moodle/grade:manage' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

    'moodle/grade:edit' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

            'moodle/grade:managegradingforms' => array(
        'riskbitmask' => RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

            'moodle/grade:sharegradingforms' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

            'moodle/grade:managesharedforms' => array(
        'riskbitmask' => RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

    'moodle/grade:manageoutcomes' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

    'moodle/grade:manageletters' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_PROHIBIT,
            'editingteacher' => CAP_PROHIBIT,
            'departmentassistant' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:managegrades'
    ),

    'moodle/grade:hide' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/grade:lock' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/grade:unlock' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/my:manageblocks' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),

    'moodle/notes:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/notes:manage' => array(
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/tag:manage' => array(
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/tag:edit' => array(
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/tag:flag' => array(
        'riskbitmask' => RISK_SPAM,

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),

    'moodle/tag:editblocks' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/block:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'guest' => CAP_ALLOW,
            'user' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        )
    ),

    'moodle/block:edit' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'moodle/portfolio:export' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'user' => CAP_ALLOW,
        )
    ),
    'moodle/comment:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'frontpage' => CAP_ALLOW,
            'guest' => CAP_ALLOW,
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        )
    ),
    'moodle/comment:post' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),
    'moodle/comment:delete' => array(

        'riskbitmask' => RISK_DATALOSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),
    'moodle/webservice:createtoken' => array(

        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS | RISK_SPAM | RISK_PERSONAL | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/webservice:createmobiletoken' => array(

        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),
    'moodle/rating:view' => array(

        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/rating:viewany' => array(

        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/rating:viewall' => array(

        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/rating:rate' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
     'moodle/course:publish' => array(

        'captype' => 'write',
        'riskbitmask' => RISK_SPAM | RISK_PERSONAL,
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/course:markcomplete' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/community:add' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        )
    ),
    'moodle/community:download' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
        )
    ),

        'moodle/badges:manageglobalsettings' => array(
        'riskbitmask'  => RISK_DATALOSS | RISK_CONFIG,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'       => CAP_ALLOW,
        )
    ),

        'moodle/badges:viewbadges' => array(
        'captype'       => 'read',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'user'          => CAP_ALLOW,
        )
    ),

        'moodle/badges:manageownbadges' => array(
        'riskbitmap'    => RISK_SPAM,
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_USER,
        'archetypes'    => array(
            'user'    => CAP_ALLOW
        )
    ),

        'moodle/badges:viewotherbadges' => array(
        'riskbitmap'    => RISK_PERSONAL,
        'captype'       => 'read',
        'contextlevel'  => CONTEXT_USER,
        'archetypes'    => array(
            'user'    => CAP_ALLOW
        )
    ),

        'moodle/badges:earnbadge' => array(
        'captype'       => 'write',
        'contextlevel'  => CONTEXT_COURSE,
        'archetypes'    => array(
            'user'           => CAP_ALLOW,
        )
    ),

        'moodle/badges:createbadge' => array(
        'riskbitmask'  => RISK_SPAM,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:deletebadge' => array(
        'riskbitmask'  => RISK_DATALOSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:configuredetails' => array(
        'riskbitmask'  => RISK_SPAM,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:configurecriteria' => array(
        'riskbitmask'  => RISK_XSS,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:configuremessages' => array(
        'riskbitmask'  => RISK_SPAM,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:awardbadge' => array(
        'riskbitmask'  => RISK_SPAM,
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'        => CAP_ALLOW,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        )
    ),

        'moodle/badges:viewawarded' => array(
        'riskbitmask'  => RISK_PERSONAL,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
                'manager'        => CAP_ALLOW,
                'teacher'        => CAP_ALLOW,
                'editingteacher' => CAP_ALLOW,
                'departmentmanager' => CAP_ALLOW,
                'departmentassistant' => CAP_ALLOW,
        )
    ),

    'moodle/site:forcelanguage' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
        )
    ),

        'moodle/search:query' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'student' => CAP_ALLOW,
            'auditor' => CAP_ALLOW,            
            'user' => CAP_ALLOW,
            'guest' => CAP_ALLOW
        )
    ),

        'moodle/competency:competencymanage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW
        )
    ),
    'moodle/competency:competencyview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
            'user' => CAP_ALLOW
        ),
    ),
    'moodle/competency:competencygrade' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,         'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        ),
    ),
        'moodle/competency:coursecompetencymanage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
        ),
    ),
    'moodle/competency:coursecompetencyconfigure' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:coursecompetencygradable' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'student' => CAP_ALLOW
        ),
        'clonepermissionsfrom' => 'moodle/course:isincompletionreports'
    ),
    'moodle/competency:coursecompetencyview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    ),
        'moodle/competency:evidencedelete' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
        ),
        'clonepermissionsfrom' => 'moodle/site:config'
    ),
        'moodle/competency:planmanage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:planmanagedraft' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:planmanageown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
        ),
    ),
    'moodle/competency:planmanageowndraft' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
        ),
    ),
    'moodle/competency:planview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:planviewdraft' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:planviewown' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    ),
    'moodle/competency:planviewowndraft' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
        ),
    ),
    'moodle/competency:planrequestreview' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/competency:planrequestreviewown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),
    'moodle/competency:planreview' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:plancomment' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:plancommentown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    ),
        'moodle/competency:usercompetencyview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,             'archetypes' => array(
            'manager' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacherassistant' => CAP_ALLOW
        )
    ),
    'moodle/competency:usercompetencyrequestreview' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        )
    ),
    'moodle/competency:usercompetencyrequestreviewown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    ),
    'moodle/competency:usercompetencyreview' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:usercompetencycomment' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:usercompetencycommentown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    ),
        'moodle/competency:templatemanage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'departmentmanager' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:templateview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_COURSECAT,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'departmentmanager' => CAP_ALLOW,
            'departmentassistant' => CAP_ALLOW,
        ),
    ),
        'moodle/competency:userevidencemanage' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),
    'moodle/competency:userevidencemanageown' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
    ),
    'moodle/competency:userevidenceview' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_USER,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
    ),

);
