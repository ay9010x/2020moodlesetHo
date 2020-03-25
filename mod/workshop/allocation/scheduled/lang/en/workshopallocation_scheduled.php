<?php




defined('MOODLE_INTERNAL') || die();

$string['currentstatus'] = 'Current status';
$string['currentstatusexecution'] = 'Status';
$string['currentstatusexecution1'] = 'Executed on {$a->datetime}';
$string['currentstatusexecution2'] = 'To be executed again on {$a->datetime}';
$string['currentstatusexecution3'] = 'To be executed on {$a->datetime}';
$string['currentstatusexecution4'] = 'Awaiting execution';
$string['currentstatusreset'] = 'Reset';
$string['currentstatusresetinfo'] = 'Check the box and save the form to reset the execution result';
$string['currentstatusreset_help'] = 'Saving the form with this checkbox ticked will result in resetting the current status. All the information about the previous execution will be removed so the allocation can be executed again (if enabled above).';
$string['currentstatusresult'] = 'Recent execution result';
$string['currentstatusnext'] = 'Next execution';
$string['currentstatusnext_help'] = 'In some cases, the allocation is scheduled to be automatically executed again even if it was already executed. This may happen if the submissions deadline has been prolonged, for example.';
$string['enablescheduled'] = 'Enable scheduled allocation';
$string['enablescheduledinfo'] = 'Automatically allocate submissions at the end of the submission phase';
$string['scheduledallocationsettings'] = 'Scheduled allocation settings';
$string['scheduledallocationsettings_help'] = 'If enabled, the scheduled allocation method will automatically allocate submissions for the assessment at the end of the submission phase. The end of the phase can be defined in the workshop setting \'Submissions deadline\'.

Internally, the random allocation method is executed with the parameters pre-defined in this form. It means that the scheduled allocation works as if the teacher executed the random allocation themselves at the end of the submission phase using the allocation settings below.

Note that the scheduled allocation is *not* executed if you manually switch the workshop into the assessment phase before the submissions deadline. You have to allocate submissions yourself in that case. The scheduled allocation method is particularly useful when used together with the automatic phase switching feature.';
$string['pluginname'] = 'Scheduled allocation';
$string['randomallocationsettings'] = 'Allocation settings';
$string['randomallocationsettings_help'] = 'Parameters for the random allocation method are defined here. They will be used by the random allocation plugin for the actual allocation of submissions.';
$string['resultdisabled'] = 'Scheduled allocation disabled';
$string['resultenabled'] = 'Scheduled allocation enabled';
$string['resultexecuted'] = 'Success';
$string['resultfailed'] = 'Unable to automatically allocate submissions';
$string['resultfailedconfig'] = 'Scheduled allocation misconfigured';
$string['resultfaileddeadline'] = 'Workshop does not have the submissions deadline defined';
$string['resultfailedphase'] = 'Workshop not in the submission phase';
$string['resultvoid'] = 'No submissions were allocated';
$string['resultvoiddeadline'] = 'Not after the submissions deadline yet';
$string['resultvoidexecuted'] = 'The allocation has been already executed';
$string['setup'] = 'Set up scheduled allocation';
