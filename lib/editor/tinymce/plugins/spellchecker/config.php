<?php



require('../../../../../config.php');

@error_reporting(E_ALL ^ E_NOTICE); 
$engine = get_config('tinymce_spellchecker', 'spellengine');
if (!$engine or $engine === 'GoogleSpell') {
    $engine = 'PSpell';
}
$config['general.engine'] = $engine;

if ($config['general.engine'] === 'PSpell') {
        $config['PSpell.mode'] = PSPELL_FAST;
    $config['PSpell.spelling'] = "";
    $config['PSpell.jargon'] = "";
    $config['PSpell.encoding'] = "";
} else if ($config['general.engine'] === 'PSpellShell') {
        $config['PSpellShell.mode'] = PSPELL_FAST;
    $config['PSpellShell.aspell'] = $CFG->aspellpath;
    $config['PSpellShell.tmp'] = '/tmp';
}
