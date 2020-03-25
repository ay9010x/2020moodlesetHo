<?php



define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
define('NO_UPGRADE_CHECK', true);

require('../../config.php');
require('../../lib/flowplayer/lib.php');

flowplayer_send_flash_content('flowplayer-3.2.18.swf');
