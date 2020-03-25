<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_email', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   auth_email
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['auth_emaildescription'] = '以電子郵件自我註冊是系統預設認證方式。當用戶在登入頁按下"建立新帳號"按鈕，即可建立自己的帳號時，在選擇帳號名稱與密碼之後，系統將以電子郵件送出確認訊息。
申請者須閱在讀電子郵件之後，按下內容內確認連結，即可啟動帳號使用權。以上動作只要一次即可，之後登入時只要帳號名稱與密碼相符便可登入。
注意：要啟用這一外掛套件，必須在"管理>>用戶>>帳號>>新增一位用戶>>選擇身分認驗證方法"的下拉選單中選擇"電子郵件確認"。';
$string['auth_emailnoemail'] = '嘗試寄送郵件給您時發生錯誤！';
$string['auth_emailrecaptcha'] = '針對以電子郵件註冊的用戶，在其登入的頁面中，增加一個視覺/聲音確認的表單元件。它可以提供一個圖形或語音驗證的服務，保護你的網站，防範惡意註冊與濫用攻擊。詳細說明請參見http://www.google.com/recaptcha';
$string['auth_emailrecaptcha_key'] = '啟動reCAPTCHA字詞驗證元件';
$string['auth_emailsettings'] = '設定';
$string['pluginname'] = '電子郵件確認';
