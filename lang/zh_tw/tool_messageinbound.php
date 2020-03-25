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
 * Strings for component 'tool_messageinbound', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_messageinbound
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['classname'] = '班級名稱';
$string['component'] = '元件';
$string['configmessageinboundhost'] = '要指定一個非預設的埠，你可以使用[server]:[port](例如mail.example.com:587) 的格式。
若你讓此欄位保持空白，Moodle將使用你指定的郵件伺服器類型的預設埠。';
$string['defaultexpiration'] = '預設的地址有效期限';
$string['defaultexpiration_help'] = '當郵件地址是由這處理程式所產生時，它可以被設定一個有效期限，這樣它就不會繼續被使用。為了管理方便，最好設定一個有效期限。';
$string['description'] = '描述';
$string['domain'] = '郵件域名';
$string['edit'] = '編輯';
$string['edithandler'] = '編輯這{$a}處理程式的設定';
$string['editinghandler'] = '編輯{$a}';
$string['enabled'] = '啟用';
$string['fixedenabled_help'] = '你無法更改這一處理程式的設定。這可能是因為其他處理程式需要用到這一處理程式。';
$string['fixedvalidateaddress'] = '驗證寄件者地址';
$string['fixedvalidateaddress_help'] = '你無法為這處理程式更改地址驗證。這可能是因為這處理程式需要一個特定的設定。';
$string['handlerdisabled'] = '你試著要聯絡的郵件處理程式已經被關閉。現在無法處理簡訊。';
$string['incomingmailconfiguration'] = '進門郵件配置';
$string['incomingmailserversettings'] = '進門郵件伺服器設定';
$string['incomingmailserversettings_desc'] = 'Moodle可以連結到適當配置的IMAP伺服器。你可以在此指定用來連到你的IMAP伺服器的設定。';
$string['invalidrecipientdescription'] = '這一條 "{$a->subject}" 簡訊無法被認證，因為不是從您的個人資料表的地址，而是從不同的email地址所送出。若要讓這簡訊能被認證，您需要回覆這一簡訊。';
$string['invalidrecipientdescriptionhtml'] = '這一條 "{$a->subject}" 簡訊無法被認證，因為不是從您的個人資料表的地址，而是從不同的email地址所送出。若要讓這簡訊能被認證，您需要回覆這一簡訊。';
$string['invalidrecipientfinal'] = '這一條 "{$a->subject}" 簡訊無法被認證，請檢查您是從您的個人資料表的email地址送出簡訊。';
$string['invalid_recipient_handler'] = '若收到一則有效的簡訊，但是無法驗證發信者，這簡訊就會被儲存在郵件伺服器上，並會以發信者的個人資料表上的地址來聯絡發信者。這讓用戶有機會回覆以確認這訊息是由他送出的。

這一處理程式是用來處理這些回覆。

這一處理程式的發信者驗證是無法關閉的，因為若他們的email客戶端配置是錯誤的，這用戶可能從一個錯誤的地址來回覆。';
$string['invalid_recipient_handler_name'] = '無效收件者處理';
$string['mailbox'] = '郵箱名稱';
$string['mailboxconfiguration'] = '郵箱配置';
$string['mailboxdescription'] = '[mailbox]+subaddress@[domain]';
$string['mailsettings'] = '郵件設定';
$string['message_handlers'] = '簡訊處理程式';
$string['messageinbound'] = '班內簡訊';
$string['messageinboundenabled'] = '啟用進門郵件處理';
$string['messageinboundenabled_desc'] = '進門郵件處理必須開啟才能讓簡訊，才能讓簡訊送出去';
$string['messageinboundgeneralconfiguration'] = '一般配置';
$string['messageinboundgeneralconfiguration_desc'] = '班內簡訊處理允許您在Moodle系統內接收和處理email。它可以用來以email來回應討論區的貼文，或添加檔案到一用戶的私人檔案。';
$string['messageinboundhost'] = '收取郵件伺服器';
$string['messageinboundhostpass'] = '密碼';
$string['messageinboundhostpass_desc'] = '這是你的服務提供者提供給你的密碼，以用來登入你的郵件帳號';
$string['messageinboundhostssl'] = '使用SLL';
$string['messageinboundhostssl_desc'] = '某些郵件伺服器可在Moodle和你的伺服器之間使用加密溝通來支援額外的安全性。若你的伺服器可支援，我們建議你使用SSL加密。';
$string['messageinboundhosttype'] = '伺服器類型';
$string['messageinboundhostuser'] = '用戶名稱';
$string['messageinboundhostuser_desc'] = '這是你的服務提供者提供給你的用戶名稱，以用來登入你的郵件帳號';
$string['messageinboundmailboxconfiguration_desc'] = '當簡訊被送出時，它們是符合 address+data@example.com 的格式。要讓Moodle可靠的產生地址，請在@符號之前指定一個你平常使用的地址，並在@符號後指定一個域名。

舉例來說，在此例中，信箱名稱就是 "address"，而email域名就是 "example.com"。為此目的，你應該使用一個專用的email帳號。';
$string['messageprocessingerror'] = '您最近送出一個email "{$a->subject}" 但很不幸，它無法被處理。

錯誤的細節如下：

{$a->error}';
$string['messageprocessingerrorhtml'] = '<p>您最近送出一個email "{$a->subject}" 但是很不幸，它無法被處理。</p>
<p>錯誤的細節如下：</p>
<p>{$a->error}</p>';
$string['messageprocessingfailed'] = '這一簡訊 "{$a->subject}" 無法被處理。錯誤原因如下: "{$a->message}"';
$string['messageprocessingfailedunknown'] = '這一簡訊 "{$a->subject}" 無法被處理。請聯絡你的管理員查明原因。';
$string['messageprocessingsuccess'] = '{$a->plain}

若你不想繼續收到這些通知，你可以在你的瀏覽器上開啟 {$a->messagepreferencesurl}，然後編輯的人簡訊偏好。';
$string['messageprocessingsuccesshtml'] = '{$a->html}
<p>若你不相繼續收到這些通知，你可以<a href="{$a->messagepreferencesurl}">編輯你的個人簡訊偏好</a>。</p>';
$string['messageprovider:invalidrecipienthandler'] = '用來確認說一個班內簡訊是否由你發出的簡訊';
$string['messageprovider:messageprocessingerror'] = '當班內簡訊無法被處理時提出警告';
$string['messageprovider:messageprocessingsuccess'] = '確認一則簡訊已經順利處理完';
$string['name'] = '名稱';
$string['noencryption'] = '關閉 - 不加密';
$string['noexpiry'] = '不過期';
$string['oldmessagenotfound'] = '你嘗試以手工認證一簡訊，但找不到這一簡訊。這可能是它已經被處理完，或是這簡訊已經過期。';
$string['oneday'] = '一天';
$string['onehour'] = '一小時';
$string['oneweek'] = '一周';
$string['oneyear'] = '一年';
$string['pluginname'] = '班內簡訊配置';
$string['replysubjectprefix'] = '回信：';
$string['requirevalidation'] = '驗證寄件者地址';
$string['ssl'] = 'SSL(自動偵測SSL版本)';
$string['sslv2'] = 'SSLv2 (強制使用 SSL 第2版)';
$string['sslv3'] = 'SSLv3 (強制使用 SSL 第3版)';
$string['taskcleanup'] = '清除沒經過驗證的進來的email';
$string['taskpickup'] = '收取進來的email';
$string['tls'] = 'TLS (TLS; 經由未加密的管道，開始進行協議層次的協商；啟動安全連接時所建議的方式)';
$string['tlsv1'] = 'TLSv1 (以TLS 指令版本1.x 連接到伺服器)';
$string['validateaddress'] = '驗證寄件者郵件地址';
$string['validateaddress_help'] = '當從一用戶處收到一條簡訊時，Moodle會比較發信者的地址和他們個人資料表的地址是否相同，以進行驗證。

若兩個地址不吻合，那這發信用戶會收到一個通知，以確認他們是否確實送出這email。

若這設定被關閉，Moodle就不會去檢查發信者的地址。';
