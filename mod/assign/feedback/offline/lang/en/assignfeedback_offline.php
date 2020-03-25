<?php



$string['confirmimport'] = 'Confirm grades import';
$string['default'] = 'Enabled by default';
$string['default_help'] = 'If set, offline grading with worksheets will be enabled by default for all new assignments.';
$string['downloadgrades'] = 'Download grading worksheet';
$string['enabled'] = 'Offline grading worksheet';
$string['enabled_help'] = 'If enabled, the teacher will be able to download and upload a worksheet with student grades when marking the assignments.';
$string['feedbackupdate'] = 'Set field "{$a->field}" for "{$a->student}" to "{$a->text}"';
$string['graderecentlymodified'] = 'The grade has been modified in Moodle more recently than in the grading worksheet for {$a}';
$string['gradelockedingradebook'] = 'The grade has been locked in the gradebook for {$a}';
$string['gradeupdate'] = 'Set grade for {$a->student} to {$a->grade}';
$string['ignoremodified'] = 'Allow updating records that have been modified more recently in Moodle than in the spreadsheet.';
$string['ignoremodified_help'] = 'When the grading worksheet is downloaded from Moodle it contains the last modified date for each of the grades. If any of the grades are updated in Moodle after this worksheet is downloaded, by default Moodle will refuse to overwrite this updated information when importing the grades. By selecting this option Moodle will disable this safety check and it may be possible for multiple markers to overwrite each others grades.';
$string['importgrades'] = 'Confirm changes in grading worksheet';
$string['invalidgradeimport'] = 'Moodle could not read the uploaded worksheet. Make sure it is saved in comma separated value format (.csv) and try again.';
$string['gradesfile'] = 'Grading worksheet (csv format)';
$string['gradesfile_help'] = 'Grading worksheet with modified grades. This file must be a csv file that has been downloaded from this assignment and must contain columns for the student grade, and identifier. The encoding for the file must be &quot;UTF-8&quot;';
$string['nochanges'] = 'No modified grades found in uploaded worksheet';
$string['offlinegradingworksheet'] = 'Grades';
$string['pluginname'] = 'Offline grading worksheet';
$string['processgrades'] = 'Import grades';
$string['skiprecord'] = 'Skip record';
$string['updaterecord'] = 'Update record';
$string['uploadgrades'] = 'Upload grading worksheet';
$string['updatedgrades'] = 'Updated {$a} grades and feedback';
