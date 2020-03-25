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
 * Strings for component 'repository', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   repository
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accessiblefilepicker'] = '可使用的檔案挑選器';
$string['activaterep'] = '啟用的倉儲';
$string['activerepository'] = '可用的倉儲外掛';
$string['activitybackup'] = '活動備份';
$string['add'] = '新增';
$string['addfile'] = '新增檔案...';
$string['addfiletext'] = '添加檔案';
$string['addplugin'] = '新增一倉儲外掛';
$string['allowexternallinks'] = '允許外部鏈結';
$string['areacategoryintro'] = '類別介紹';
$string['areacourseintro'] = '課程介紹';
$string['areacourseoverviewfiles'] = '課程摘要檔案';
$string['areamainfile'] = '主要檔案';
$string['arearoot'] = '系統';
$string['areauserbackup'] = '用戶備份';
$string['areauserdraft'] = '草稿';
$string['areauserpersonal'] = '私人檔案';
$string['areauserprofile'] = '個人資料表';
$string['attachedfiles'] = '附加檔案';
$string['attachment'] = '附件';
$string['author'] = '作者';
$string['automatedbackup'] = '自動備份';
$string['back'] = '« 回頭';
$string['backtodraftfiles'] = '«回到草稿檔案管理';
$string['cachecleared'] = '快取的檔案已經被移除';
$string['cacheexpire'] = '快取已經過期';
$string['cannotaccessparentwin'] = '如果父視窗是以安全連線HTPPS開啟，那我們將不允許使用視窗開啟物件，因此我們不能為你自動刷新倉儲內容，但是我們已經取得你的session。

請回到檔案挑選器並再次選擇倉儲，它將會有效。';
$string['cannotdelete'] = '無法刪除這一檔案。';
$string['cannotdownload'] = '無法下載這一檔案';
$string['cannotdownloaddir'] = '無法下載這一資料夾';
$string['cannotinitplugin'] = '呼叫plugin_init 失敗';
$string['choosealink'] = '選擇一個鏈結....';
$string['chooselicense'] = '選擇授權方式';
$string['cleancache'] = '清除我的快取檔案';
$string['close'] = '關閉';
$string['commonrepositorysettings'] = '共同的倉儲設定';
$string['configallowexternallinks'] = '這選項讓所有用戶可以選擇是否將外在媒體複製到Moodle中。
若設定為關，媒體永遠會複製到Moodle系統裡(對於資料的完整和安全較好)。
若設定為開，則每次添加媒體到文章時，用戶就可自行選擇是否要複製到Moodle系統裡。';
$string['configcacheexpire'] = '瀏覽外在倉儲時，這檔案清單被放在本地快取記憶體的時間(秒)。';
$string['configgetfiletimeout'] = '上傳一個外部檔案到Moodle系統的時間限制(秒)';
$string['configsaved'] = '配置已被儲存!';
$string['configsyncfiletimeout'] = '下載外部檔案到Moodle的時間限制(秒)';
$string['configsyncimagetimeout'] = '在同步化時，從外部倉儲下載一圖像檔的時間限制(秒)';
$string['confirmdelete'] = '你確定要刪除這一倉儲 - {$a}？若你選擇"繼續並下載"，檔案所參照到的外部內容將會下載到Moodle中，但它會很花時間來進行。';
$string['confirmdeletefile'] = '你確定要刪除這一檔案？';
$string['confirmdeletefilewithhref'] = '你確定要刪除這一檔案？
這裡有{$a}個別名/捷徑檔案使用這個檔案作為他們的資源。
如果你進行下去，那這些別名將會轉換為真實複件。';
$string['confirmdeletefolder'] = '你確定要刪除這一資料夾？所有的檔案和下層資料夾都將被刪除。';
$string['confirmremove'] = '你確定要移除這一倉儲外掛({$a})、它的選項和<stong>所有實例</stong>嗎？若你選擇"繼續並下載"，那檔案所參照到的外部內容將會被下載到Moodle中，但它可能需要很長的時間來處理。';
$string['confirmrenamefile'] = '你確定要充新命名/移除這一檔案？
這裡有{$a}個別名/捷徑檔案使用這個檔案作為他們的資源。
如果你進行下去，那這些別名將會轉換為真實複件。';
$string['confirmrenamefolder'] = '你確定要搬移/重新命名這一資料夾？
任何別名/捷徑檔案參照到這資料夾的檔案，將會轉換為真實複件。';
$string['continueuninstall'] = '繼續';
$string['continueuninstallanddownload'] = '繼續並下載';
$string['copying'] = '複製';
$string['coursebackup'] = '課程備份';
$string['create'] = '建立';
$string['createfolder'] = '建立資料夾';
$string['createfolderfail'] = '無法建立這一資料夾';
$string['createfoldersuccess'] = '建立資料夾成功';
$string['createinstance'] = '建立一倉儲檔案';
$string['createrepository'] = '建立一倉儲';
$string['createxxinstance'] = '建立 "{$a}" 檔案';
$string['date'] = '日期';
$string['datecreated'] = '建立於';
$string['deleted'] = '倉儲已經刪除';
$string['deleterepository'] = '刪除這一倉儲';
$string['detailview'] = '檢視細節';
$string['dimensions'] = '向度';
$string['disabled'] = '關閉';
$string['displayasdetails'] = '以檔案細節顯示';
$string['displayasicons'] = '以檔案圖標顯示';
$string['displayastree'] = '以檔案樹顯示';
$string['displaydetails'] = '以檔案細節顯示資料夾內容';
$string['displayicons'] = '以檔案圖標顯示資料夾內容';
$string['displaytree'] = '以檔案樹狀結構顯示資料夾內容';
$string['download'] = '下載';
$string['downloadallfiles'] = '下載所有檔案';
$string['downloadfolder'] = '全部下載';
$string['downloadsucc'] = '這檔案已經下載成功';
$string['draftareanofiles'] = '無法下載，因為沒有檔案附加在上面';
$string['editrepositoryinstance'] = '編輯倉儲實例';
$string['emptylist'] = '空白清單';
$string['emptytype'] = '無法建立倉儲類型：類型名稱是空的';
$string['enablecourseinstances'] = '允許用戶新增一倉儲檔案到用這課程';
$string['enableuserinstances'] = '允許用戶新增一倉儲實例到這用戶處境';
$string['enter'] = '輸入';
$string['entername'] = '請輸入資料夾名稱';
$string['enternewname'] = '請輸入這新檔案名稱';
$string['error'] = '發生一個未知的錯誤!';
$string['errordoublereference'] = '無法以簡稱或別名複寫檔案，因為該簡稱已經存在';
$string['errornotyourfile'] = '錯誤! 你不能挑選一個不是你添加的檔案';
$string['errorpostmaxsize'] = '錯誤! 這一上傳的檔案可能超過伺服器上php.ini檔的設定(max_post_size)';
$string['erroruniquename'] = '錯誤! 倉儲實例名稱必須是獨一無二的';
$string['errorwhilecommunicatingwith'] = '錯誤！在與倉儲"{$a}"溝通時發生錯誤';
$string['errorwhiledownload'] = '錯誤! 在下載檔案{$a}時發生錯誤';
$string['existingrepository'] = '這倉儲已經存在';
$string['federatedsearch'] = '聯合的搜尋';
$string['fileexists'] = '此檔名已經被使用，請使用另一個名稱';
$string['fileexistsdialog_editor'] = '有一個這名稱的檔案已經被附加到你正在編輯的文字上了';
$string['fileexistsdialog_filemanager'] = '有一個這名稱的檔案已經被附加上去了';
$string['fileexistsdialogheader'] = '檔案已存在';
$string['filename'] = '檔案名稱';
$string['filenotnull'] = '你必須選擇一個檔案來上傳。';
$string['filepicker'] = '檔案挑選器';
$string['filesaved'] = '這檔案已經被儲存';
$string['filesizenull'] = '檔案大小無法決定';
$string['folderexists'] = '資料夾名稱已經被使用，請使用另一個名字。';
$string['foldernotfound'] = '沒找到資料夾';
$string['folderrecurse'] = '資料夾不能移到自己的下層資料夾';
$string['getfile'] = '選擇這一檔案';
$string['getfiletimeout'] = '接收檔案超過時間';
$string['help'] = '幫助';
$string['iconview'] = '以圖示檢視';
$string['imagesize'] = '{$a->width} x {$a->height} px';
$string['instance'] = '實例';
$string['instancedeleted'] = '實例已被刪除';
$string['instances'] = '倉儲實例';
$string['instancesforcourses'] = '{$a} 個課程共用實例';
$string['instancesforsite'] = '{$a}適用於整個網站的共同實例';
$string['instancesforusers'] = '{$a}用戶私人實例';
$string['invalidfiletype'] = '{$a}檔案類型無法被接受';
$string['invalidjson'] = '無效的JSON 字串';
$string['invalidparams'] = '無效的參數';
$string['invalidplugin'] = '無效的倉儲{$a} 外掛套件';
$string['invalidrepositoryid'] = '無效的倉儲識別編號';
$string['isactive'] = '啟用的？';
$string['keyword'] = '關鍵字';
$string['lastmodified'] = '最後修改';
$string['license'] = '授權方式';
$string['linkexternal'] = '外部鏈結';
$string['listview'] = '以清單檢視';
$string['loading'] = '載入中...';
$string['login'] = '登入您的帳號';
$string['logout'] = '登出';
$string['lostsource'] = '錯誤! 資源不見了。{$a}';
$string['makefileinternal'] = '做一個這檔案的複本';
$string['makefilelink'] = '直接鏈結到這檔案';
$string['makefilereference'] = '建立一個別名/快捷方式的文件';
$string['manage'] = '管理倉儲';
$string['manageinstances'] = '管理實例';
$string['manageurl'] = '管理';
$string['manageuserrepository'] = '管理個人的倉儲';
$string['moving'] = '移動中';
$string['name'] = '名稱';
$string['newfolder'] = '新資料夾';
$string['newfoldername'] = '新資料夾名稱';
$string['noenter'] = '沒有東西被輸入';
$string['nofilesattached'] = '沒有附加檔案';
$string['nofilesavailable'] = '沒有可用的檔案';
$string['nomorefiles'] = '不允許更多附件';
$string['nopathselected'] = '還沒有選擇目的地的路徑(可以用雙擊樹狀節點來選擇)';
$string['nopermissiontoaccess'] = '沒有權限來存取這一倉儲';
$string['norepositoriesavailable'] = '抱歉，你現有的倉儲沒有一個可以回復成要求格式的檔案。';
$string['norepositoriesexternalavailable'] = '抱歉，你現有的倉儲沒有一個可以回復成外部檔案。';
$string['noresult'] = '沒有搜尋結果';
$string['notyourinstances'] = '你不能檢視/編輯其他用戶的倉儲實例';
$string['off'] = '啟用但隱藏';
$string['on'] = '啟用且可見';
$string['openpicker'] = '選擇一檔案...';
$string['operation'] = '操作';
$string['original'] = '原初的';
$string['overwrite'] = '覆寫';
$string['overwriteall'] = '全部覆寫';
$string['path'] = '路徑';
$string['personalrepositories'] = '可用的倉儲實例';
$string['plugin'] = '倉儲外掛套件';
$string['pluginerror'] = '倉儲外掛套件有錯誤。';
$string['pluginname'] = '倉儲外掛套件名稱';
$string['pluginnamehelp'] = '若你留空白，將使用預設名稱。';
$string['popup'] = '擊點"登入"按鈕來登入';
$string['popupblockeddownload'] = '這下載中的視窗是被封鎖的，請你將瀏覽器設定為"允許使用彈出式視窗"，並再試一次。';
$string['preview'] = '預覽';
$string['privatefilesof'] = '{a}個私人檔案';
$string['readonlyinstance'] = '你不能編輯/刪除一個唯讀的實例';
$string['referencesexist'] = '這裡有{$a}個別名/捷徑檔案使用這一個檔案作為他們資源。';
$string['referenceslist'] = '別名/捷徑';
$string['refresh'] = '重新整理';
$string['refreshnonjsfilepicker'] = '請關閉這一視窗，並刷新非javascript檔案挑選器。';
$string['removed'] = '倉儲已被移除';
$string['renameall'] = '全部重新命名';
$string['renameto'] = '重新命名為{$a}';
$string['repositories'] = '倉儲';
$string['repository'] = '倉儲';
$string['repositorycourse'] = '課程倉儲';
$string['repositoryerror'] = '遠端倉儲回復錯誤：{$a}';
$string['repositoryicon'] = '倉儲圖示';
$string['save'] = '儲存';
$string['saveas'] = '儲存為';
$string['saved'] = '已經儲存';
$string['saving'] = '儲存中';
$string['search'] = '搜尋';
$string['searching'] = '搜尋';
$string['searchrepo'] = '搜尋倉儲';
$string['sectionbackup'] = '學習單元備份';
$string['select'] = '選擇';
$string['setmainfile'] = '設定主要檔案';
$string['setmainfile_help'] = '若在資料夾中有多個檔案，主要檔案是那個會出現在檢視頁面的。其他檔案，比如說圖片檔或影片檔可能是鑲嵌在它裡面。在檔案管理程式中，主要檔案是是以黑體字標題來區分。';
$string['settings'] = '設定';
$string['setupdefaultplugins'] = '設定預設的倉儲外掛套件';
$string['siteinstances'] = '這網站的倉儲實例';
$string['size'] = '大小';
$string['submit'] = '送出';
$string['sync'] = '同步';
$string['syncfiletimeout'] = '同步檔案時超過時間';
$string['syncimagetimeout'] = '同步檔案時超過時間';
$string['thumbview'] = '以縮圖檢視';
$string['title'] = '選擇一個檔案....';
$string['type'] = '類型';
$string['typenotvisible'] = '不可見的類型';
$string['undisclosedreference'] = '(未公開的參考文獻)';
$string['undisclosedsource'] = '(未公開的資源)';
$string['unknownoriginal'] = '未知';
$string['unzipped'] = '解壓縮成功';
$string['upload'] = '上傳這一檔案';
$string['uploading'] = '上傳.....';
$string['uploadsucc'] = '這檔案已經順利上傳';
$string['uselatestfile'] = '使用最新檔案';
$string['usenonjsfilemanager'] = '在新視窗中開啟檔案管理';
$string['usenonjsfilepicker'] = '在新視窗開啟檔案挑選器';
$string['usercontextrepositorydisabled'] = '你不能在用戶處境下，編輯這一倉儲。';
$string['wrongcontext'] = '你不能在這處境下存取';
$string['xhtmlerror'] = '你可能正在使用XHTML嚴格表頭，某些YUI要素在這模式下無法運作，請在Moodle中把它關掉。';
$string['ziped'] = '已順利壓縮資料夾';
