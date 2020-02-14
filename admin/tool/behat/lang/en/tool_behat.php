<?php



$string['aim'] = 'This administration tool helps developers and test writers to create .feature files describing Moodle\'s functionalities and run them automatically. Step definitions available for use in .feature files are listed below.';
$string['allavailablesteps'] = 'All the available steps definitions';
$string['errorbehatcommand'] = 'Error running behat CLI command. Try running "{$a} --help" manually from CLI to find out more about the problem.';
$string['errorcomposer'] = 'Composer dependencies are not installed.';
$string['errordataroot'] = '$CFG->behat_dataroot is not set or is invalid.';
$string['errorsetconfig'] = '$CFG->behat_dataroot, $CFG->behat_prefix and $CFG->behat_wwwroot need to be set in config.php.';
$string['erroruniqueconfig'] = '$CFG->behat_dataroot, $CFG->behat_prefix and $CFG->behat_wwwroot values need to be different than $CFG->dataroot, $CFG->prefix, $CFG->wwwroot, $CFG->phpunit_dataroot and $CFG->phpunit_prefix values.';
$string['fieldvalueargument'] = 'Field value arguments';
$string['fieldvalueargument_help'] = 'This argument should be completed by a field value, there are many field types, simple ones like checkboxes, selects or textareas or complex ones like date selectors. You can check <a href="http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps" target="_blank">Field values</a> to see the expected field value depending on the field type you provide.';
$string['giveninfo'] = 'Given. Processes to set up the environment';
$string['infoheading'] = 'Info';
$string['installinfo'] = 'Read {$a} for installation and tests execution info';
$string['newstepsinfo'] = 'Read {$a} for info about how to add new steps definitions';
$string['newtestsinfo'] = 'Read {$a} for info about how to write new tests';
$string['nostepsdefinitions'] = 'There aren\'t steps definitions matching this filters';
$string['pluginname'] = 'Acceptance testing';
$string['stepsdefinitionscomponent'] = 'Area';
$string['stepsdefinitionscontains'] = 'Contains';
$string['stepsdefinitionsfilters'] = 'Steps definitions';
$string['stepsdefinitionstype'] = 'Type';
$string['theninfo'] = 'Then. Checkings to ensure the outcomes are the expected ones';
$string['unknownexceptioninfo'] = 'There was a problem with Selenium or your browser. Please ensure you are using the latest version of Selenium. Error:';
$string['viewsteps'] = 'Filter';
$string['wheninfo'] = 'When. Actions that provokes an event';
$string['wrongbehatsetup'] = 'Something is wrong with the behat setup and so step definitions cannot be listed: <b>{$a->errormsg}</b><br/><br/>Please check:<ul>
<li>$CFG->behat_dataroot, $CFG->behat_prefix and $CFG->behat_wwwroot are set in config.php with different values from $CFG->dataroot, $CFG->prefix and $CFG->wwwroot.</li>
<li>You ran "{$a->behatinit}" from your Moodle root directory.</li>
<li>Dependencies are installed in vendor/ and {$a->behatcommand} file has execution permissions.</li></ul>';
