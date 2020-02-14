<?php



define('NO_MOODLE_COOKIES', true); 
require(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/config.php');

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/lib/editor/tinymce/plugins/moodleemoticon/dialog.php');

$emoticonmanager = get_emoticon_manager();
$stringmanager = get_string_manager();

$editor = get_texteditor('tinymce');
$plugin = $editor->get_plugin('moodleemoticon');

$htmllang = get_html_lang();
header('Content-Type: text/html; charset=utf-8');
header('X-UA-Compatible: IE=edge');
?>
<!DOCTYPE html>
<html <?php echo $htmllang ?>
<head>
    <title><?php print_string('moodleemoticon:desc', 'tinymce_moodleemoticon'); ?></title>
    <script type="text/javascript" src="<?php echo $editor->get_tinymce_base_url(); ?>/tiny_mce_popup.js"></script>
    <script type="text/javascript" src="<?php echo $plugin->get_tinymce_file_url('js/dialog.js'); ?>"></script>
</head>
<body>

    <table border="0" align="center" style="width:100%;">
<?php

$emoticons = $emoticonmanager->get_emoticons();
$index = 0;
foreach ($emoticons as $emoticon) {
    $txt = $emoticon->text;
    $img = $OUTPUT->render(
        $emoticonmanager->prepare_renderable_emoticon($emoticon, array('class' => 'emoticon emoticon-index-'.$index)));
    if ($stringmanager->string_exists($emoticon->altidentifier, $emoticon->altcomponent)) {
        $alt = get_string($emoticon->altidentifier, $emoticon->altcomponent);
    } else {
        $alt = '';
    }
    echo html_writer::tag('tr',
            html_writer::tag('td', $img, array('style' => 'width:20%;text-align:center;')) .
            html_writer::tag('td', s($txt), array('style' => 'width:40%;text-align:center;font-family:monospace;')) .
            html_writer::tag('td', $alt),
        array(
            'class' => 'emoticoninfo emoticoninfo-index-'.$index,
        )
    );
    $index++;
}

?>
    </table>

    <div class="mceActionPanel">
        <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
    </div>

</body>
</html>
