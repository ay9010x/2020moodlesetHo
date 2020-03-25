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
 * Strings for component 'role', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   role
 * @copyright 2017 Click-AP {@link http://www.click-ap.com}
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addinganewrole'] = '增加一個新角色';
$string['addrole'] = '增加一個新角色';
$string['advancedoverride'] = '進階角色覆蓋';
$string['allow'] = '允許';
$string['allowassign'] = '允許指派角色';
$string['allowed'] = '被允許的';
$string['allowoverride'] = '允許覆蓋角色';
$string['allowroletoassign'] = '允許有 {$a->fromrole} 角色的用戶去指派 {$a->targetrole}的角色。';
$string['allowroletooverride'] = '允許有 {$a->fromrole} 用戶的用戶去覆蓋 {$a->targetrole}的角色。';
$string['allowroletoswitch'] = '允許有 {$a->fromrole} 角色的用戶去切換 {$a->targetrole}的角色。';
$string['allowswitch'] = '允許角色切換';
$string['allsiteusers'] = '網站全部用戶';
$string['archetype'] = '角色原型';
$string['archetype_help'] = '角色原型是一個角色的預設的授權狀態，也是重設時，會復原的狀態。它也是網站升級時，這角色的新授權狀態。';
$string['archetypemanager'] = '原型：管理員';
$string['archetypecoursecreator'] = '原型： 課程建立者';
$string['archetypedepartmentmanager'] = '原型： 系所管理員';
$string['archetypedepartmentassistant'] = '原型： 系所助理';
$string['archetypeeditingteacher'] = '原型： 教師(有編輯權)';
$string['archetypeteacher'] = '原型： 教師(無編輯權)';
$string['archetypeteacherassistant'] = '原型： 助教';
$string['archetypestudent'] = '原型： 學生';
$string['archetypeauditor'] = '原型： 旁聽生';
$string['archetypeguest'] = '原型： 訪客';
$string['archetypeuser'] = '原型： 已被認證的用戶';
$string['archetypefrontpage'] = '原型： 在首頁被認證的用戶';
$string['archetypemodsetws'] = '原型： ModSETAPI';
$string['assignanotherrole'] = '指派另一角色';
$string['assignedroles'] = '被指派的角色';
$string['assignerror'] = '當把 {$a->role} 角色指派給用戶 {$a->user} 時，發生錯誤。';
$string['assignglobalroles'] = '指派系統角色';
$string['assignmentcontext'] = '指派的處境';
$string['assignmentoptions'] = '指派的選項';
$string['assignrole'] = '指派角色';
$string['assignrolenameincontext'] = '在{$a->context}中指派“{$a->role}”角色';
$string['assignroles'] = '指派角色';
$string['assignroles_help'] = '透過在某個處境中指派一個角色給某一用戶，您可以給予用戶該角色擁有的權限，但只限於目前處境和所有下層的處境。

例如，如果一個用戶在某課程中被指派為學生角色，那麼他在該課程中所有的活動和區塊中的角色都是學生。';
$string['assignrolesin'] = '指派在{$a}的角色';
$string['assignrolesrelativetothisuser'] = '指派角色給這一用戶';
$string['backtoallroles'] = '回到所有角色的清單';
$string['backup:anonymise'] = '備份課程時不包含用戶姓名';
$string['backup:backupactivity'] = '備份活動';
$string['backup:backupcourse'] = '備份課程';
$string['backup:backupsection'] = '備份學習單元';
$string['backup:backuptargethub'] = '為課程集散中心進行備份';
$string['backup:backuptargetimport'] = '為匯入進行備份';
$string['backup:configure'] = '設置備份選項';
$string['backup:downloadfile'] = '從備份區域下載檔案';
$string['backup:userinfo'] = '備份用戶資料';
$string['badges:awardbadge'] = '頒發獎章給一用戶';
$string['badges:configurecriteria'] = '設定/編輯贏得一獎章的條件';
$string['badges:configuredetails'] = '設定/編輯獎章細節';
$string['badges:configuremessages'] = '配置獎章訊息';
$string['badges:createbadge'] = '建立/複製獎章';
$string['badges:deletebadge'] = '刪除獎章';
$string['badges:earnbadge'] = '贏得獎章';
$string['badges:manageglobalsettings'] = '管理獎章的整體設定';
$string['badges:manageownbadges'] = '檢視並管理自己贏得的獎章';
$string['badges:viewawarded'] = '不需要得到獎章，就能檢視哪些用戶贏得一特定獎章';
$string['badges:viewbadges'] = '檢視可用的獎章而不需要贏得它們';
$string['badges:viewotherbadges'] = '在別的用戶的個人資料表上檢視公開的獎章';
$string['block:edit'] = '編輯區塊的設定';
$string['block:view'] = '檢視區塊';
$string['blog:create'] = '新增部落格內容';
$string['blog:manageentries'] = '編輯和管理內容';
$string['blog:manageexternal'] = '編輯和管理外面的部落格';
$string['blog:search'] = '搜尋部落格文章';
$string['blog:view'] = '瀏覽部落格內容';
$string['blog:viewdrafts'] = '檢視部落格文章的草稿';
$string['calendar:manageentries'] = '管理任何行事曆條目';
$string['calendar:managegroupentries'] = '管理群組的行事曆條目';
$string['calendar:manageownentries'] = '管理自己的行事曆條目';
$string['capabilities'] = '能力';
$string['capability'] = '能力';
$string['category:create'] = '建立類別';
$string['category:delete'] = '刪除類別';
$string['category:manage'] = '管理類別';
$string['category:update'] = '更新類別';
$string['category:viewhiddencategories'] = '檢視隱藏的類別';
$string['category:visibility'] = '查看隱藏的類別';
$string['checkglobalpermissions'] = '檢查系統權限';
$string['checkpermissions'] = '檢查權限';
$string['checkpermissionsin'] = '檢查{$a}的權限';
$string['checksystempermissionsfor'] = '檢查{$a->fullname}的系統權限';
$string['checkuserspermissionshere'] = '檢查{$a->fullname}在此{$a->contextlevel}中擁有的權限';
$string['chooseroletoassign'] = '請選擇一個角色來指派';
$string['cohort:assign'] = '加入或移除校定班級群組成員';
$string['cohort:manage'] = '建立、刪除和搬移校定班級群組';
$string['cohort:view'] = '檢視全站的校定班級群組';
$string['comment:delete'] = '刪除回應';
$string['comment:post'] = '張貼回應';
$string['comment:view'] = '閱讀回應';
$string['community:add'] = '使用這社群區塊來搜尋課程集散中心，並尋找課程';
$string['community:download'] = '從這社群區塊下載一課程';
$string['competency:competencygrade'] = '設定核心能力評等方式';
$string['competency:competencymanage'] = '管理核心能力架構';
$string['competency:competencyview'] = '檢視核心能力架構';
$string['competency:coursecompetencyconfigure'] = '配置課程核心能力的設定';
$string['competency:coursecompetencygradable'] = '接收核心能力的分數';
$string['competency:coursecompetencymanage'] = '管理課程核心能力';
$string['competency:coursecompetencyview'] = '檢視課程核心能力';
$string['competency:evidencedelete'] = '刪除證據';
$string['competency:plancomment'] = '對一學習計畫做評論';
$string['competency:plancommentown'] = '對自己的學習計畫做評論';
$string['competency:planmanage'] = '管理學習計畫';
$string['competency:planmanagedraft'] = '管理學習計畫草稿';
$string['competency:planmanageown'] = '管理自己的學習計畫';
$string['competency:planmanageowndraft'] = '管理自己的學習計畫草稿';
$string['competency:planrequestreview'] = '請求審查一學習計畫';
$string['competency:planrequestreviewown'] = '請求審查自己的學習計畫';
$string['competency:planreview'] = '審查一學習計畫';
$string['competency:planview'] = '審查所有的學習計畫';
$string['competency:planviewdraft'] = '檢視學習計畫草稿';
$string['competency:planviewown'] = '檢視自己的學習計畫';
$string['competency:planviewowndraft'] = '檢視自己的學習計畫草稿';
$string['competency:templatemanage'] = '管理學習計畫範本';
$string['competency:templateview'] = '檢視學習計畫範本';
$string['competency:usercompetencycomment'] = '對一用戶核心能力做評論';
$string['competency:usercompetencycommentown'] = '對自己的用戶核心能力做評論';
$string['competency:usercompetencyrequestreview'] = '請求審查一用戶核心能力';
$string['competency:usercompetencyrequestreviewown'] = '請求審查自己的用戶核心能力';
$string['competency:usercompetencyreview'] = '審查一用戶核心能力';
$string['competency:usercompetencyview'] = '檢視一用戶核心能力';
$string['competency:userevidencemanage'] = '管理先備學習的證據';
$string['competency:userevidencemanageown'] = '管理自己的先備學習的證據';
$string['competency:userevidenceview'] = '檢視一用戶的先備學習的證據';
$string['confirmaddadmin'] = '你真的要將用戶<strong>{$a}</strong> 指派為新的網站管理員？';
$string['confirmdeladmin'] = '你真的要將用戶<strong>{$a}</strong> 從網站管理員名單中移除？';
$string['confirmroleprevent'] = '您確定要在處境“{$a->context}” 中有權限{$a->cap}的角色列表中移除<strong>{$a->role} </strong>嗎？';
$string['confirmroleunprohibit'] = '您確定要在處境{$a->context}中禁止有權限{$a->cap}的角色列表中刪除角色<strong>{$a->role}</strong>嗎？';
$string['confirmunassign'] = '你確定你要從這一用戶身上移除這一角色？';
$string['confirmunassignno'] = '取消';
$string['confirmunassigntitle'] = '確認角色變更';
$string['confirmunassignyes'] = '移除';
$string['context'] = '處境';
$string['course:activityvisibility'] = '隱藏/顯示活動';
$string['course:bulkmessaging'] = '可發送訊息給多人';
$string['course:changecategory'] = '更改課程類別';
$string['course:changefullname'] = '更改課程全名';
$string['course:changeidnumber'] = '更改課程編號';
$string['course:changeshortname'] = '更改課程簡稱';
$string['course:changesummary'] = '更改課程摘要';
$string['course:create'] = '建立課程';
$string['course:delete'] = '刪除課程';
$string['course:enrolconfig'] = '設定課程中的選課實例';
$string['course:enrolreview'] = '審查選課';
$string['course:ignorefilesizelimits'] = '使用檔案時，可以不受任何檔案大小的限制';
$string['course:isincompletionreports'] = '被顯示在完成報告';
$string['course:manageactivities'] = '管理活動';
$string['course:managefiles'] = '管理檔案';
$string['course:managegrades'] = '管理成績';
$string['course:managegroups'] = '管理群組';
$string['course:managescales'] = '管理量尺';
$string['course:markcomplete'] = '在課程完成時，將用戶標記為完成';
$string['course:movesections'] = '移動學習單元';
$string['course:publish'] = '將課程發佈到課程集散地';
$string['course:renameroles'] = '角色重新命名';
$string['course:request'] = '申請建立新課程';
$string['course:reset'] = '課程歸零';
$string['course:reviewotherusers'] = '查核其它用戶。';
$string['course:sectionvisibility'] = '控制學習單元可見性';
$string['course:setcurrentsection'] = '設定當前學習單元';
$string['course:tag'] = '更改課程標籤';
$string['course:update'] = '更新課程設定';
$string['course:useremail'] = '使email地址有效/無效';
$string['course:view'] = '檢視不含參與者的課程';
$string['course:viewcoursegrades'] = '檢視課程成績';
$string['course:viewhiddenactivities'] = '檢視隱藏的活動';
$string['course:viewhiddencourses'] = '檢視隱藏的課程';
$string['course:viewhiddensections'] = '瀏覽隱藏的學習單元';
$string['course:viewhiddenuserfields'] = '檢視隱藏的用戶資料欄位';
$string['course:viewparticipants'] = '檢視課程參與者';
$string['course:viewscales'] = '檢視量尺';
$string['course:viewsuspendedusers'] = '檢視已休學的用戶';
$string['course:visibility'] = '隱藏/顯示課程';
$string['createrolebycopying'] = '複製{$a}為一個新角色';
$string['createthisrole'] = '建立這角色';
$string['currentcontext'] = '現在的處境';
$string['currentrole'] = '現在的角色';
$string['customroledescription'] = '自訂角色描述';
$string['customroledescription_help'] = '若這自訂描述是空的，那標準角色的描述會自動地地區化。';
$string['customrolename'] = '自訂角色完整名稱';
$string['customrolename_help'] = '若自訂名稱是空的，那標準角色的名稱會自動地區化。你必須提供完整名稱給所有的自訂角色。';
$string['defaultrole'] = '預設的角色';
$string['defaultx'] = '預設：{$a}';
$string['defineroles'] = '定義角色';
$string['deletecourseoverrides'] = '刪除課程中所有置換的角色';
$string['deletelocalroles'] = '刪除所有本地角色的指派';
$string['deleterolesure'] = '您確定要刪除角色“{$a->name} ({$a->shortname})”嗎？</p><p>目前此角色已經指派給{$a->count}個使用者了。';
$string['deletexrole'] = '刪除{$a}角色';
$string['duplicaterole'] = '複製角色';
$string['duplicaterolesure'] = '您確定要複製角色“{$a->name} ({$a->shortname})”嗎？</p>';
$string['editingrolex'] = '編輯角色"{$a}"中';
$string['editrole'] = '編輯角色';
$string['editxrole'] = '編輯{$a}角色';
$string['errorbadrolename'] = '不正確的角色名稱';
$string['errorbadroleshortname'] = '不正確的角色簡稱';
$string['errorexistsrolename'] = '角色名稱已經存在';
$string['errorexistsroleshortname'] = '角色簡稱已經存在';
$string['eventroleallowassignupdated'] = '允許角色指派';
$string['eventroleallowoverrideupdated'] = '允許角色覆蓋';
$string['eventroleallowswitchupdated'] = '允許角色切換';
$string['eventroleassigned'] = '被指派的角色';
$string['eventrolecapabilitiesupdated'] = '角色權限已被更新';
$string['eventroledeleted'] = '角色已刪除';
$string['eventroleunassigned'] = '取消角色指派';
$string['existingadmins'] = '現任網站管理員';
$string['existingusers'] = '已經有{$a}位用戶';
$string['explanation'] = '解釋';
$string['export'] = '匯出';
$string['extusers'] = '現有的用戶';
$string['extusersmatching'] = '符合"{$a}"的現有用戶';
$string['filter:manage'] = '管理本地過濾器設定';
$string['frontpageuser'] = '在首頁的已認證用戶';
$string['frontpageuserdescription'] = '首頁課程中所有已經登入的用戶';
$string['globalrole'] = '系統角色';
$string['globalroleswarning'] = '警告！您在此頁指派的任何角色都將成為該使用者的全網站角色，在整個網站都有效，包括首頁和所有課程。';
$string['gotoassignroles'] = '轉到為此{$a->contextlevel}所指派的角色';
$string['gotoassignsystemroles'] = '跳轉到指派的系統角色';
$string['grade:edit'] = '編修成績簿';
$string['grade:export'] = '匯出成績簿';
$string['grade:hide'] = '隱藏/顯示 成績或項目';
$string['grade:import'] = '匯入成績簿';
$string['grade:lock'] = '鎖定成績和項目';
$string['grade:manage'] = '管理成績項目';
$string['grade:managegradingforms'] = '管理進階計分方法';
$string['grade:manageletters'] = '管理字母等第';
$string['grade:manageoutcomes'] = '管理成績簿的核心能力';
$string['grade:managesharedforms'] = '管理進階的評分表單模版';
$string['grade:override'] = '置換成績';
$string['grade:sharegradingforms'] = '將進階評分表單作為模版分享';
$string['grade:unlock'] = '解除成績或項目的鎖定';
$string['grade:view'] = '檢視自己的成績簿';
$string['grade:viewall'] = '檢視其他人的成績';
$string['grade:viewhidden'] = '檢視當事人隱藏的成績';
$string['highlightedcellsshowdefault'] = '在在以下表單中被標示出來的權限是上面目前選出角色原形的預設權限。';
$string['highlightedcellsshowinherit'] = '下列表單中被選擇的權限是上面所選角色原形的預設權限。';
$string['inactiveformorethan'] = '沒有活動超過 {$a->timeperiod}';
$string['ingroup'] = '在這"{$a->group}"群組';
$string['inherit'] = '繼承';
$string['invalidpresetfile'] = '無效的角色定義檔';
$string['legacy:admin'] = '原角色：網站管理員';
$string['legacy:coursecreator'] = '原角色：課程開設者';
$string['legacy:editingteacher'] = '原角色：教師（有編輯權）';
$string['legacy:guest'] = '原角色：訪客';
$string['legacy:student'] = '原角色：學生';
$string['legacy:teacher'] = '原角色：教師（無編輯權）';
$string['legacytype'] = '原角色類型';
$string['legacy:user'] = '原角色：認證的使用者';
$string['listallroles'] = '列出全部角色';
$string['localroles'] = '本地委派的角色';
$string['mainadmin'] = '主要管理員';
$string['mainadminset'] = '設定主要管理員';
$string['manageadmins'] = '管理網站管理員';
$string['manager'] = '管理員';
$string['managerdescription'] = '管理員可以存取課程和修改它們，他們通常不參與課程。';
$string['manageroles'] = '管理角色';
$string['maybeassignedin'] = '這角色可以被指派的處境的類型';
$string['morethan'] = '超過{$a} 位';
$string['multipleroles'] = '多重角色';
$string['my:configsyspages'] = '為儀表板頁面配置系統樣板';
$string['my:manageblocks'] = '管理儀表板頁區塊';
$string['neededroles'] = '那些角色有這一權限';
$string['nocapabilitiesincontext'] = '在此處境中沒有可使用的權限';
$string['noneinthisx'] = '此{$a}中沒有用戶';
$string['noneinthisxmatching'] = '在這{$a->contexttype}中，沒有符合“{$a->search}”的用戶';
$string['norole'] = '沒有角色';
$string['noroleassignments'] = '此用戶在本站任何地方都沒有被分配任何角色';
$string['noroles'] = '沒有角色';
$string['notabletoassignroleshere'] = '您不能在此分配任何角色';
$string['notabletooverrideroleshere'] = '您不能在這裡覆蓋任何角色的權限';
$string['notes:manage'] = '管理筆記';
$string['notes:view'] = '檢視筆記';
$string['notset'] = '未設定';
$string['overrideanotherrole'] = '撤銷另一個角色';
$string['overridecontext'] = '覆蓋處境';
$string['overridepermissions'] = '置換權限';
$string['overridepermissionsforrole'] = '覆蓋"{$a->role}"在{$a->context}中的權限';
$string['overridepermissions_help'] = '透過覆蓋權限可以在特定的處境中允許或禁止做某些動作。';
$string['overridepermissionsin'] = '在{$a}中置換權限';
$string['overrideroles'] = '置換角色';
$string['overriderolesin'] = '在{$a}中置換角色';
$string['overrides'] = '置換';
$string['overridesbycontext'] = '覆蓋(依照處境)';
$string['permission'] = '權限';
$string['permission_help'] = '權限是關於能力的設定。它有四個選項：

*沒有設定<br/>
*允許-有使用此能力的權限<br/>
*阻止-無使用此能力的權限，即使在更高的處境中被允許。<br/>
*禁止-權限完全被停止使用，並且在任何更低(更特定的)處境中都不能覆蓋這個設定';
$string['permissions'] = '權限';
$string['permissionsforuser'] = '用戶{$a}的權限';
$string['permissionsincontext'] = '在{$a}的權限';
$string['portfolio:export'] = '匯出到學習歷程檔案系統';
$string['potentialusers'] = '{$a}位潛在的使用者';
$string['potusers'] = '潛在的用戶';
$string['potusersmatching'] = '符合“{$a}”的潛在用戶';
$string['prevent'] = '防止';
$string['prohibit'] = '禁止';
$string['prohibitedroles'] = '被禁止的';
$string['question:add'] = '增加新試題';
$string['question:config'] = '設置試題類型';
$string['question:editall'] = '編輯所有試題';
$string['question:editmine'] = '編輯自己的試題';
$string['question:flag'] = '試圖回答問題時標記題目';
$string['question:managecategory'] = '管理試題類別';
$string['question:moveall'] = '搬移所有試題';
$string['question:movemine'] = '搬移自己的試題';
$string['question:useall'] = '使用所有試題';
$string['question:usemine'] = '使用自己的試題';
$string['question:viewall'] = '查看所有試題';
$string['question:viewmine'] = '查看自己的試題';
$string['rating:rate'] = '新增評比到項目';
$string['rating:view'] = '察看你收到總評比';
$string['rating:viewall'] = '視每個人給出的全部原始評比';
$string['rating:viewany'] = '檢視每個人收到的總評比';
$string['resetrole'] = '重新設定回預設狀態';
$string['resettingrole'] = '重新設定角色 \'{$a}\'';
$string['restore:configure'] = '設定還原選項';
$string['restore:createuser'] = '在還原時建立新用戶';
$string['restore:restoreactivity'] = '還原活動';
$string['restore:restorecourse'] = '還原課程';
$string['restore:restoresection'] = '還原學習單元';
$string['restore:restoretargethub'] = '以來自課程集散中心的檔案還原';
$string['restore:restoretargetimport'] = '以匯入的檔案還原';
$string['restore:rolldates'] = '允許在還原回存活動設定日期';
$string['restore:uploadfile'] = '上傳檔案到備份區域';
$string['restore:userinfo'] = '還原用戶資料';
$string['restore:viewautomatedfilearea'] = '從自動備份中還原課程';
$string['risks'] = '風險';
$string['roleallowheader'] = '允許的角色：';
$string['roleallowinfo'] = '選擇一個角色，將其加入到場景為“{$a->context}”，權限為“{$a->cap}”的允許的角色列表中：';
$string['role:assign'] = '分配角色給用戶';
$string['roleassignments'] = '分配角色';
$string['roledefinitions'] = '角色定義';
$string['rolefullname'] = '角色名稱';
$string['roleincontext'] = '{$a->context}中的{$a->role}';
$string['role:manage'] = '建立和管理角色';
$string['role:override'] = '置換其他角色的權限';
$string['roleprohibitheader'] = '禁用的角色';
$string['roleprohibitinfo'] = '選擇一個角色，將其加入到場景為“{$a->context}”，權限為“{$a->cap}”的禁用的角色列表中：';
$string['rolerepreset'] = '使用角色設定';
$string['roleresetdefaults'] = '預設';
$string['roleresetrole'] = '使用角色或原型';
$string['role:review'] = '檢查其他人的權限';
$string['rolerisks'] = '角色風險';
$string['roles'] = '角色';
$string['role:safeoverride'] = '撤銷安全權限';
$string['roleselect'] = '選擇角色';
$string['rolesforuser'] = '用戶{$a}的角色';
$string['roles_help'] = '角色是為整個系統而定義的權限的集合，你可以將它指派給在一特定的處境中特定的用戶。';
$string['roleshortname'] = '角色簡稱';
$string['roleshortname_help'] = '角色簡稱是一低層次的角色辨識方式，它只可以使用ASCII文數字字元。
不要更改所有標準角色的簡稱。';
$string['role:switchroles'] = '切換到其他角色';
$string['roletoassign'] = '要指派的角色：';
$string['roletooverride'] = '被置換的角色';
$string['safeoverridenotice'] = '警告：高危險性權限已經被鎖定，因為您只能修改安全的權限。';
$string['search:query'] = '進行全網站的搜尋';
$string['selectanotheruser'] = '選擇另一用戶';
$string['selectauser'] = '選擇一位用戶';
$string['selectrole'] = '選擇一個角色';
$string['showallroles'] = '顯示所有角色';
$string['showthisuserspermissions'] = '顯示這一用戶的權限';
$string['site:accessallgroups'] = '讀取所有群組';
$string['siteadministrators'] = '網站管理員';
$string['site:approvecourse'] = '審核開課申請';
$string['site:backup'] = '備份課程';
$string['site:config'] = '改變網站環境配置';
$string['site:deleteanymessage'] = '刪除這一網站上的任何簡訊';
$string['site:deleteownmessage'] = '刪除這一用戶收到與送出的簡訊';
$string['site:doanything'] = '可以做任何事';
$string['site:doclinks'] = '顯示連結到網站外的文件';
$string['site:forcelanguage'] = '覆寫課程所用語言';
$string['site:import'] = '將其他課程匯入課程中';
$string['site:manageblocks'] = '在一頁面管理區塊';
$string['site:mnetloginfromremote'] = '經由MNet從遠端的應用程式登入';
$string['site:mnetlogintoremote'] = '經由MNet漫遊到遠端的應用程式';
$string['site:readallmessages'] = '讀取網站上的全部訊息';
$string['site:restore'] = '還原課程';
$string['site:sendmessage'] = '傳送訊息給任何人';
$string['site:trustcontent'] = '信任發送的內容';
$string['site:uploadusers'] = '從檔案上傳新用戶';
$string['site:viewfullnames'] = '總是可以看到用戶的全名';
$string['site:viewparticipants'] = '瀏覽課程參與名單';
$string['site:viewreports'] = '檢視報表';
$string['site:viewuseridentity'] = '在清單上看到完整用戶資料';
$string['tag:create'] = '建立新標籤';
$string['tag:edit'] = '編輯現存的標籤';
$string['tag:editblocks'] = '在標籤頁中編輯區塊';
$string['tag:flag'] = '標示出不恰當的標籤';
$string['tag:manage'] = '管理所有標籤';
$string['thisnewrole'] = '這一新角色';
$string['thisusersroles'] = '這一用戶的角色指派';
$string['unassignarole'] = '撤銷{$a}角色';
$string['unassignconfirm'] = '您確定要刪除用戶“{$a->user}”的“{$a->role}”角色嗎？';
$string['unassignerror'] = '取消用戶{$a->user}的{$a->role}角色時出錯。';
$string['user:changeownpassword'] = '修改自己的密碼';
$string['user:create'] = '建立用戶';
$string['user:delete'] = '刪除用戶';
$string['user:editmessageprofile'] = '為其他用戶修改傳入訊息的目的地';
$string['user:editownmessageprofile'] = '編輯自己的傳入簡訊的目的地';
$string['user:editownprofile'] = '編輯自己的個人資料';
$string['user:editprofile'] = '編輯用戶的個人資料';
$string['user:ignoreuserquota'] = '不管用戶配額限制';
$string['user:loginas'] = '變身登入系統';
$string['user:manageblocks'] = '在其他用戶的個人資料頁上管理區塊';
$string['user:manageownblocks'] = '在自己的公開個人資料頁面管理區塊';
$string['user:manageownfiles'] = '在自己的私人檔案區管理檔案';
$string['user:managesyspages'] = '為公開的個人資料頁設定預設的版面格式';
$string['user:readuserblogs'] = '檢視全部用戶的部落格';
$string['user:readuserposts'] = '檢視全部用戶的討論區貼文';
$string['usersfrom'] = '來自{$a}的用戶';
$string['usersfrommatching'] = '來自{$a->contextname}且符合“{$a->search}”的用戶';
$string['usersinthisx'] = '在此{$a}中的用戶';
$string['usersinthisxmatching'] = '在此{$a->contextype}中符合"{$a->search}"的用戶';
$string['userswithrole'] = '所有用戶加以一個角色';
$string['userswiththisrole'] = '屬於此角色的用戶';
$string['user:update'] = '更新用戶個人資料';
$string['user:viewalldetails'] = '檢視所有用戶個人資料頁的完整訊息';
$string['user:viewdetails'] = '檢視用戶個人資料';
$string['user:viewhiddendetails'] = '檢視用戶資料中隱藏的細節';
$string['user:viewlastip'] = '檢視全部用戶最新的IP位址';
$string['user:viewuseractivitiesreport'] = '檢視用戶活動報告';
$string['user:viewusergrades'] = '檢視用戶成績';
$string['useshowadvancedtochange'] = '使用"顯示進階選項"來修改';
$string['viewingdefinitionofrolex'] = '察看角色"{$a}"的定義';
$string['viewrole'] = '檢視角色詳細資訊';
$string['webservice:createmobiletoken'] = '為行動設備建立網路服務的存取憑證';
$string['webservice:createtoken'] = '建立網路服務存取憑證';
$string['whydoesuserhavecap'] = '為什麼{$a->fullname}在場景{$a->context}中有{$a->capability}權限？';
$string['whydoesusernothavecap'] = '為什麼{$a->fullname}在場景{$a->context}中沒有{$a->capability}權限？';
$string['xroleassignments'] = '給{$a}的角色指派';
$string['xuserswiththerole'] = '具有"{$a->role}"角色的用戶數：';
