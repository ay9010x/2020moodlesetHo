<?php



$string['alterstatus'] = 'Alter status';
$string['altertimeend'] = 'Alter end time';
$string['altertimestart'] = 'Alter start time';
$string['assignrole'] = 'Assign role';
$string['browseusers'] = 'Browse users';
$string['browsecohorts'] = 'Browse cohorts';
$string['confirmbulkdeleteenrolment'] = 'Are you sure you want to delete these users enrolments?';
$string['defaultstart'] = 'Default enrolment start';
$string['defaultperiod'] = 'Default enrolment duration';
$string['defaultperiod_desc'] = 'Default length of time that the enrolment is valid. If set to zero, the enrolment duration will be unlimited by default.';
$string['defaultperiod_help'] = 'Default length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited by default.';
$string['deleteselectedusers'] = 'Delete selected user enrolments';
$string['editselectedusers'] = 'Edit selected user enrolments';
$string['enrolledincourserole'] = 'Enrolled in "{$a->course}" as "{$a->role}"';
$string['enrolusers'] = 'Enrol users';
$string['expiredaction'] = 'Enrolment expiration action';
$string['expiredaction_help'] = 'Select action to carry out when user enrolment expires. Please note that some user data and settings are purged from course during course unenrolment.';
$string['expirymessageenrollersubject'] = 'Enrolment expiry notification';
$string['expirymessageenrollerbody'] = 'Enrolment in the course \'{$a->course}\' will expire within the next {$a->threshold} for the following users:

{$a->users}

To extend their enrolment, go to {$a->extendurl}';
$string['expirymessageenrolledsubject'] = 'Enrolment expiry notification';
$string['expirymessageenrolledbody'] = 'Dear {$a->user},

This is a notification that your enrolment in the course \'{$a->course}\' is due to expire on {$a->timeend}.

If you need help, please contact {$a->enroller}.';
$string['manual:config'] = 'Configure manual enrol instances';
$string['manual:enrol'] = 'Enrol users';
$string['manual:manage'] = 'Manage user enrolments';
$string['manual:unenrol'] = 'Unenrol users from the course';
$string['manual:unenrolself'] = 'Unenrol self from the course';
$string['messageprovider:expiry_notification'] = 'Manual enrolment expiry notifications';
$string['now'] = 'Now';
$string['pluginname'] = 'Manual enrolments';
$string['pluginname_desc'] = 'The manual enrolments plugin allows users to be enrolled manually via a link in the course administration settings, by a user with appropriate permissions such as a teacher. The plugin should normally be enabled, since certain other enrolment plugins, such as self enrolment, require it.';
$string['status'] = 'Enable manual enrolments';
$string['status_desc'] = 'Allow course access of internally enrolled users. This should be kept enabled in most cases.';
$string['status_help'] = 'This setting determines whether users can be enrolled manually, via a link in the course administration settings, by a user with appropriate permissions such as a teacher.';
$string['statusenabled'] = 'Enabled';
$string['statusdisabled'] = 'Disabled';
$string['unenrol'] = 'Unenrol user';
$string['unenrolselectedusers'] = 'Unenrol selected users';
$string['unenrolselfconfirm'] = 'Do you really want to unenrol yourself from course "{$a}"?';
$string['unenroluser'] = 'Do you really want to unenrol "{$a->user}" from course "{$a->course}"?';
$string['unenrolusers'] = 'Unenrol users';
$string['wscannotenrol'] = 'Plugin instance cannot manually enrol a user in the course id = {$a->courseid}';
$string['wsnoinstance'] = 'Manual enrolment plugin instance doesn\'t exist or is disabled for the course (id = {$a->courseid})';
$string['wsusercannotassign'] = 'You don\'t have the permission to assign this role ({$a->roleid}) to this user ({$a->userid}) in this course({$a->courseid}).';
$string['manualpluginnotinstalled'] = 'The "Manual" plugin has not yet been installed';
