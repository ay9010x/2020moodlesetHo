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
 * Strings for component 'certificate', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   certificate
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addlinklabel'] = '新增另一個連結的活動選項';
$string['addlinktitle'] = '點按新增另一個連結的活動選項';
$string['areaintro'] = '證書簡介';
$string['awarded'] = '榮獲';
$string['awardedto'] = '頒發給';
$string['back'] = '背面';
$string['border'] = '邊框';
$string['borderblack'] = '黑色';
$string['borderblue'] = '藍色';
$string['borderbrown'] = '棕色';
$string['bordercolor'] = '邊框線條';
$string['bordercolor_help'] = '由於圖像將會大幅增加PDF檔案的容量，您可以選擇列印邊框線條，以取代邊框圖案（請確保您邊框圖案已設定為否）。邊框線條的選項會以三條不同闊度及以所選顏色來列印。';
$string['bordergreen'] = '綠色';
$string['borderlines'] = '線';
$string['borderstyle'] = '邊框圖案';
$string['borderstyle_help'] = '邊框圖案的選項允許您從立書／照片／邊框文件夾中選擇邊框圖案，選擇您想在證書邊框出現的邊框圖案或選擇沒有邊框。';
$string['certificate'] = '證書驗證代碼：';
$string['certificate:addinstance'] = '新增證書例子';
$string['certificate:manage'] = '管理證書例子';
$string['certificatename'] = '證書名稱';
$string['certificate:printteacher'] = '如果開啟了列印教師的設定，將會在證書上顯示為教師。';
$string['certificatereport'] = '證書統計報表';
$string['certificatesfor'] = '證書給';
$string['certificate:student'] = '取回證書';
$string['certificatetype'] = '證書類型';
$string['certificatetype_help'] = '在這裡您可以訂立證書的設計。證書類型文件夾中有四種預設證書：
1. 在A4紙張上以嵌入字型的A4嵌入式列印
2. 在A4紙張上不以嵌入字型的A4非嵌入式列印
3. 在信紙上以嵌入字型的信件嵌入式列印
4. 在信紙上不以嵌入字型的信件非嵌入式列印

非嵌入式列印類型會使用Helvetica and Times 字型。如果您認為您的用戶沒有此字型，或您使用的語言不適用於Helvetica and Times字型的話，請選擇嵌入式列印類型。嵌入式列印使用Dejavusans and Dejavuserif字型。您的PDF檔案容量會變相變大。因此，如非必要，不解議使用嵌入式列印。

在證書類型文件夾中可以新增新的文件夾。文件夾的名稱及新的語言字符串亦需要增加至證書語言文件夾中。';
$string['certificate:view'] = '查看證書';
$string['certify'] = '這是為了證明';
$string['code'] = '代碼';
$string['completiondate'] = '完成課程';
$string['course'] = '給';
$string['coursegrade'] = '課程成績';
$string['coursename'] = '課程';
$string['coursetimereq'] = '需要在課程幾分鐘';
$string['coursetimereq_help'] = '在此輸入學生需要登入到此課程的最低限度的時間（以分鐘）以獲發證書。';
$string['credithours'] = '時數';
$string['customtext'] = '自訂文字';
$string['customtext_help'] = '如果您想在證書上列印其他教師的名字，除了線條圖像外，請不要選擇「列印教師」或任何個簽名圖像。在此文本框中輸入您想出現的教師名字。在預設設定中，此文字將會位於證書的左下方。 可使用以下的HTML 標纖：<br>, <p>, <b>, <i>, <u>, <img> (src 及闊度(或高度) 是強制性的), <a> (href 是強制性的), <font> (屬性包括: 顏色, (hex 顏色代號), 字型, (arial, times, courier, helvetica, symbol)).';
$string['date'] = '在';
$string['datefmt'] = '日期格式';
$string['datefmt_help'] = '選擇列印在證書上的日期格式。或者，選擇最後的選項以用戶選擇的語言列印證書上的日期。';
$string['datehelp'] = '日期';
$string['deletissuedcertificates'] = '刪除頒發的證書';
$string['delivery'] = '發行';
$string['delivery_help'] = '在此選擇學生如何取得他們的證書。
開啟瀏覽器：在新的瀏覽器視窗打開證書
強制下載：開啟瀏覽器檔案下載視窗
電郵證書：證書將以電郵附件形式傳送給學生

當用戶收到他們的證書，如果他們從課程主頁中點擊證書鏈接，學生可以查看獲得證書的日期及證書。';
$string['designoptions'] = '設計選項';
$string['download'] = '強制下載';
$string['emailcertificate'] = 'Email';
$string['emailothers'] = 'Email給他人';
$string['emailothers_help'] = '在此輸入電郵地址 （以逗號作分隔）以發送提醒電郵至獲得證書的學生。';
$string['emailstudenttext'] = '附件是您在{$a->course}的證書';
$string['emailteachermail'] = '{$a->student} 已收到他們的證書:  {$a->course}的"<i>{$a->certificate}</i>"。


你可以在此檢視它:
{$a->url}。';
$string['emailteachermailhtml'] = '{$a->student} 已收到他們的證書:  {$a->course}的"<i>{$a->certificate}</i>"。

你可以在此檢視它:

    <a href="{$a->url}">證書統計報表</a>。';
$string['emailteachers'] = '電子郵件通知教師';
$string['emailteachers_help'] = '如果啟用，教師將會在學生收到證書時收到提醒電郵。';
$string['entercode'] = '輸入證書字號進行驗證：';
$string['fontsans'] = '無襯線字體系列';
$string['fontsans_desc'] = '用作證書中的嵌入式字體的無襯線字體系列';
$string['fontserif'] = '襯線字體系列';
$string['fontserif_desc'] = '用作證書中的嵌入式字體的襯線字體系列';
$string['getcertificate'] = '取得您的證書';
$string['grade'] = '成績';
$string['gradedate'] = '評分日期';
$string['gradefmt'] = '成績格式';
$string['gradefmt_help'] = '您可以選擇以下三種列印證書上的成績的格式：

百分比成績：以百分比形式列印成績
分數成績：以分數數值形式(百分法分數)列印成績
文字等第：以文字等第形式列印百分比成績';
$string['gradeletter'] = '成績(文字等第)';
$string['gradepercent'] = '成績(百分比)';
$string['gradepoints'] = '成績(百分法分數)';
$string['imagetype'] = '圖像類型';
$string['incompletemessage'] = '要下載您的證書，您必須先完成所有必需活動。請返回課程以完成您的課業。';
$string['intro'] = '介紹';
$string['issued'] = '頒發';
$string['issueddate'] = '頒發日期';
$string['issueoptions'] = '頒發選項';
$string['landscape'] = '橫印';
$string['lastviewed'] = '您最後收到證書於：';
$string['letter'] = '文字';
$string['lockingoptions'] = '鎖定選項';
$string['modulename'] = '證書';
$string['modulenameplural'] = '證書';
$string['mycertificates'] = '我的證書';
$string['nocertificates'] = '這兒沒有證書';
$string['nocertificatesissued'] = '這兒沒有證書被頒發';
$string['nocertificatesreceived'] = '還沒收到任何課程的證書';
$string['nofileselected'] = '必須選一個要上傳的檔案!';
$string['nogrades'] = '無成績可用';
$string['notapplicable'] = '無法使用';
$string['notfound'] = '這證書字號無法被證實';
$string['notissued'] = '沒有頒發';
$string['notissuedyet'] = '還沒頒發';
$string['notreceived'] = '您沒有收到這證書';
$string['openbrowser'] = '在新視窗開啟';
$string['opendownload'] = '點按下方按鈕可以儲存您的證書到電腦中。';
$string['openemail'] = '點按下方按鈕您的證書將以電子郵件附件方式給您。';
$string['openwindow'] = '點按下方按鈕可以在新視窗開啟您的證書。';
$string['or'] = '或';
$string['orientation'] = '列印方向';
$string['orientation_help'] = '選擇您的證書是要以直式或橫式方向呈現？';
$string['pluginadministration'] = '證書管理';
$string['pluginname'] = '證書';
$string['portrait'] = '直印';
$string['printdate'] = '列印日期';
$string['printdate_help'] = '如果選取列印日期，這將會是列印的日期。如果選取了課程完成日期但學生尚未完成此課程，將會列印收取日期。您亦可以選擇評分的日期。如果證書是在評分前頒發，將會列印收取日期。';
$string['printerfriendly'] = '列印友善頁面';
$string['printgrade'] = '列印成績';
$string['printgrade_help'] = '您可以在成績簿上選擇任何成績項目以列印證書上的學生成績。成績項目將會根據成績簿上出現的次序排列。選擇以下的成績格式。';
$string['printhours'] = '列印學分數';
$string['printhours_help'] = '輸入要列印在證書上的學分數';
$string['printnumber'] = '列印證書字號';
$string['printnumber_help'] = '一個獨一無二的隨機字母和數字十位數可以列印在證書上。此數字可用作對比證書報告上編碼以作核實。';
$string['printoutcome'] = '列印核心能力';
$string['printoutcome_help'] = '您可以選擇任何學習成果以列印證書上學習成果的名稱及學生的學習成果。例子： 作業成果：精通';
$string['printseal'] = '印章或機構標誌圖像';
$string['printseal_help'] = '此選項允許您選擇在證書／照片／印章的文件夾中選取您想列印到證書上的印章或機構標誌。在預設設定中，此圖像將會位於證書的右下方。';
$string['printsignature'] = '簽名圖案';
$string['printsignature_help'] = '此選項允許您選擇在證書／照片／簽名圖案的文件夾中選取您想列印到證書上的簽名圖案。您可以列印簽名的圖示或列印簽名用的橫線。在預設設定中，此圖像將會位於證書的左下方。';
$string['printteacher'] = '列印教師姓名';
$string['printteacher_help'] = '';
$string['printwmark'] = '浮水印圖像';
$string['printwmark_help'] = '浮水印圖像可設置在證書的背景中。浮水印是褪色圖像。浮水印圖像可以是標誌、印章、波浪、字句或您想使用的圖像背景。';
$string['receivedcerts'] = '已收到證書';
$string['receiveddate'] = '收到日期';
$string['removecert'] = '移除已頒發證書';
$string['report'] = '報表';
$string['reportcert'] = '證書報表';
$string['reportcert_help'] = '如果您在此選擇是，用戶的證書報告上將出現取的證書日期、編碼、課程名稱。如果您選擇在證書上列印成績，用戶的證書報告上將成績。';
$string['requiredtimenotmet'] = '您必須在課程中花至少{$a->requiredtime} 分鐘以獲得此證書';
$string['requiredtimenotvalid'] = '需要時間必須是大於零的有效數字';
$string['reviewcertificate'] = '查看您的證書';
$string['savecert'] = '儲存證書';
$string['savecert_help'] = '如果您選擇此選項，每張證書的副本將會儲存在課程檔案中的moddata文件夾中。儲存的證書副本鏈結將顯示在證書報告中。';
$string['seal'] = '印章';
$string['sigline'] = '線';
$string['signature'] = '簽名';
$string['statement'] = '已經完成這課程';
$string['summaryofattempts'] = '以前收到的證書的摘要';
$string['textoptions'] = '文字選項';
$string['title'] = '研習證書';
$string['to'] = '頒發給';
$string['typeA4_embedded'] = 'A4 嵌入';
$string['typeA4_non_embedded'] = 'A4 非嵌入';
$string['typeletter_embedded'] = '信件嵌入';
$string['typeletter_non_embedded'] = '信件非嵌入';
$string['unsupportedfiletype'] = '檔案必須是jpeg或png格式';
$string['uploadimage'] = '上傳圖片';
$string['uploadimagedesc'] = '此接鍵會帶您到新的頁面以上載圖片。';
$string['userdateformat'] = '用戶語言的日期格式';
$string['validate'] = '查驗';
$string['verifycertificate'] = '查驗證書';
$string['viewcertificateviews'] = '查看{$a}張頒發的證書';
$string['viewed'] = '您收到這一證書是在：';
$string['viewtranscript'] = '檢視證書';
$string['watermark'] = '浮水印';
