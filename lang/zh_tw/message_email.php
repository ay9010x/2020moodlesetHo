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
 * Strings for component 'message_email', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   message_email
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowattachments'] = '允許有附件';
$string['allowusermailcharset'] = '允許使用者選擇字元集';
$string['configallowattachments'] = '若啟用，從這網站送出的eMail可以加上附件，比如說獎章。';
$string['configallowusermailcharset'] = '若啟用此功能，每個用戶都可以在自己的個人資料表設定中選用自己電子郵件的語系編碼。';
$string['configemailonlyfromnoreplyaddress'] = '若啟用，所有email將會使用這"不用回信"的位址作為"來自"的位址。這樣可以用來防止外部郵件系統的反欺騙控制阻隔了email。';
$string['configmailnewline'] = '在郵件中使用的換行字元。根據RFC 822bis定義CRLF是必須的，有些郵件可以自動將LF轉換為CRLF，有些郵件伺服器則將CRLF作錯誤的轉換至CRCRLF，其餘的將缺少LF的信件駁回收（例如qmail)。如果您有不能傳送郵件或重覆空新行的問題，可嘗試變更此設定。';
$string['confignoreplyaddress'] = '有時電子郵件以用戶身份發送(如討論區張貼)。有時用戶不希望別人看到自己的電子郵件地址，在這情況下，您在此處指定的電子郵件地址將會被使用來當作發送信箱。';
$string['configsitemailcharset'] = '這一設定將指定這網站送出的全部電子郵件所使用的預設字集。';
$string['configsmtpauthtype'] = '設定用在SMTP伺服器的認證類型';
$string['configsmtphosts'] = '填入一個或多個本地SMTP伺服主機全名(例如\'mail.a.com\'或\'mail.a.com; mail.b.com\')，Moodle將用它(們)發送郵件。

若要指定一個非預設的埠(例如，port 25以外的)，你可以使用這server]:[port] 語法(例如\'mail.a.com:587\')。就安全性連結，port 456 常用在SSL，而 port 587常用在TLS，若需要的話，在下方指鄧安全傳輸協定。

如果你留空不填，Moodle將使用PHP的預設方法來發送郵件。';
$string['configsmtpmaxbulk'] = '每一SMTP session可傳送訊息數的最大值。訊息分組也許可以加速電子郵件的傳送。小於２的數值會強制每一封電子郵件都產生新的SMTP session。';
$string['configsmtpsecure'] = '若SMTP伺服器需要安全連結，請指定正確的傳輸協定類型。';
$string['configsmtpuser'] = '如果您在上面指定了一個SMTP伺服主機，而該伺服主機要求身份驗證，那麼在此填入用戶名和密碼。';
$string['email'] = '傳送email通知給';
$string['emailonlyfromnoreplyaddress'] = '永遠從這不要回信的位址上送出email？';
$string['ifemailleftempty'] = '留空白會傳送通知給{$a}';
$string['mailnewline'] = '郵件中使用的換行字元';
$string['none'] = '無';
$string['noreplyaddress'] = '不要回覆的信箱地址';
$string['pluginname'] = 'Email';
$string['sitemailcharset'] = '字集';
$string['smtpauthtype'] = 'SMTP認證類型';
$string['smtphosts'] = 'SMTP 主機';
$string['smtpmaxbulk'] = 'SMTP連線限制';
$string['smtppass'] = 'SMTP 密碼';
$string['smtpsecure'] = 'SMTP 安全性';
$string['smtpuser'] = 'SMTP 帳號';
