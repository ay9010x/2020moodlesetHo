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
 * Strings for component 'tool_lp', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_lp
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = '動作';
$string['activities'] = '活動';
$string['addcohorts'] = '新增同期生';
$string['addcohortstosync'] = '添加同期生到同步';
$string['addcompetency'] = '新增核心能力';
$string['addcoursecompetencies'] = '新增課程核心能力';
$string['addcrossreferencedcompetency'] = '新增交互參照的核心能力';
$string['addingcompetencywillresetparentrule'] = '添加一個新的核心能力將會移除在 \'{$a}\'設定的規則。你要繼續嗎？';
$string['addnewcompetency'] = '增加新的核心能力';
$string['addnewcompetencyframework'] = '增加新核心能力架構';
$string['addnewplan'] = '增加新學習計畫';
$string['addnewtemplate'] = '增加新學習計畫樣版';
$string['addnewuserevidence'] = '添加新證據';
$string['addtemplatecompetencies'] = '添加核心能力到學習計畫樣版';
$string['aisrequired'] = '\'{$a}\' 是必要的';
$string['aplanswerecreated'] = '已建立{$a}個學習計畫';
$string['aplanswerecreatedmoremayrequiresync'] = '已建立{$a}個學習計畫；在下一次同步時將會建立更多。';
$string['assigncohorts'] = '指派同期生';
$string['averageproficiencyrate'] = '依據這一樣板，完成學習計畫後的平均精熟率是 {$a} %';
$string['cancelreviewrequest'] = '取消審核的請求';
$string['cannotaddrules'] = '這一核心能力無法被配置';
$string['cannotcreateuserplanswhentemplateduedateispassed'] = '新學習計畫無發建立，因為樣版的截止日期已經過了或即將到了。';
$string['cannotcreateuserplanswhentemplatehidden'] = '當這樣板被隱藏時，無法建立新學習計畫';
$string['category'] = '類目';
$string['chooserating'] = '選擇一個評等...';
$string['cohortssyncedtotemplate'] = '同期生全部採用這一學習計畫樣版';
$string['competenciesforframework'] = '{$a}的核心能力';
$string['competenciesmostoftennotproficient'] = '大多數人在完成學習計畫後仍無法達到精熟的核心能力';
$string['competenciesmostoftennotproficientincourse'] = '大多數人在這課程無法達到精熟的核心能力';
$string['competencycannotbedeleted'] = '核心能力\'{$a}\'無法被刪除';
$string['competencycreated'] = '核心能力已建立';
$string['competencycrossreferencedcompetencies'] = '{$a}個交互參照的核心能力';
$string['competencyframework'] = '核心能力架構';
$string['competencyframeworkcreated'] = '核心能力架構已建立';
$string['competencyframeworkname'] = '名稱';
$string['competencyframeworkroot'] = '無上層核心能力';
$string['competencyframeworks'] = '核心能力架構';
$string['competencyframeworkupdated'] = '核心能力架構已更新';
$string['competencyoutcome_complete'] = '標示為已完成';
$string['competencyoutcome_evidence'] = '附上一個證據';
$string['competencyoutcome_none'] = '無';
$string['competencyoutcome_recommend'] = '推薦這核心能力';
$string['competencypicker'] = '核心能力挑選器';
$string['competencyrule'] = '核心能力規則';
$string['competencyupdated'] = '核心能力已更新';
$string['completeplan'] = '已完成這一學習計畫';
$string['completeplanconfirm'] = '把學習計畫 \'{$a}\' 設定為已經完成？如果這樣做，所有用戶的核心能力的目前狀態將會被記錄，而這學習計畫將成為唯讀狀態。';
$string['configurecoursecompetencysettings'] = '設定課程核心能力';
$string['configurescale'] = '配置量尺';
$string['coursecompetencies'] = '課程核心能力';
$string['coursecompetencyratingsarenotpushedtouserplans'] = '在這一課程的核心能力評等不會影響學習計畫';
$string['coursecompetencyratingsarepushedtouserplans'] = '在這一課程的核心能力評等在學習計畫上會立即更新';
$string['coursecompetencyratingsquestion'] = '當一個課程的核心能力被評等時，這一評等會更新在學習計畫上的核心能力，或它只應用在這課程？';
$string['coursesusingthiscompetency'] = '連結到這一核心能力的課程';
$string['coveragesummary'] = '在 {$a->competenciescount} 個核心能力中涵蓋了 {$a->competenciescoveredcount} 個 ( {$a->coveragepercentage} % )';
$string['createlearningplans'] = '建立學習計畫';
$string['createplans'] = '建立學習計畫';
$string['crossreferencedcompetencies'] = '交互參照的核心能力';
$string['default'] = '預設';
$string['deletecompetency'] = '要刪除核心能力 \'{$a}\'?';
$string['deletecompetencyframework'] = '要刪除核心能力架構 \'{$a}\'?';
$string['deletecompetencyparenthasrule'] = '要刪除核心能力"{$a}"？這將會移除為它的上層設定的規則';
$string['deleteplan'] = '確定要刪除這學習計畫 \'{$a}\'？';
$string['deleteplans'] = '刪除這學習計畫';
$string['deletetemplate'] = '確定要刪除學習計畫樣版\'{$a}\'？';
$string['deletetemplatewithplans'] = '這一樣板已經有學習計畫相連結。你必須表明要如何處理這些學習計畫。';
$string['deletethisplan'] = '刪除這一學習計畫';
$string['deletethisuserevidence'] = '刪除這一證據';
$string['deleteuserevidence'] = '要刪除這一先備學習 \'{$a}\'的證據？';
$string['description'] = '說明';
$string['duedate'] = '截止日期';
$string['duedate_help'] = '學習計畫應該完成的日期';
$string['editcompetency'] = '編輯核心能力';
$string['editcompetencyframework'] = '編輯核心能力架構';
$string['editplan'] = '編輯學習計畫';
$string['editrating'] = '編輯評等';
$string['edittemplate'] = '編輯這一學習計畫樣版';
$string['editthisplan'] = '編輯這一學習計畫';
$string['editthisuserevidence'] = '編輯這一證據';
$string['edituserevidence'] = '編輯證據';
$string['evidence'] = '證據';
$string['findcourses'] = '尋找課程';
$string['frameworkcannotbedeleted'] = '這核心能力架構 \'{$a}\' 不能被刪除';
$string['hidden'] = '隱藏';
$string['hiddenhint'] = '(隱藏)';
$string['idnumber'] = '辨識號碼';
$string['inheritfromframework'] = '從核心能力架構承接(預設)';
$string['itemstoadd'] = '添加的項目';
$string['jumptocompetency'] = '跳到核心能力';
$string['jumptouser'] = '跳到用戶';
$string['learningplancompetencies'] = '學習計畫核心能力';
$string['learningplans'] = '學習計畫';
$string['levela'] = '層次{$a}';
$string['linkcompetencies'] = '連結核心能力';
$string['linkcompetency'] = '連結核心能力';
$string['linkedcompetencies'] = '連結的核心能力';
$string['linkedcourses'] = '連結的課程';
$string['linkedcourseslist'] = '連結的課程：';
$string['listcompetencyframeworkscaption'] = '條列核心能力架構';
$string['listofevidence'] = '證據的清單';
$string['listplanscaption'] = '學習計畫的清單';
$string['listtemplatescaption'] = '條列學習計畫範本';
$string['loading'] = '裝載中...';
$string['locatecompetency'] = '放置核心能力';
$string['managecompetenciesandframeworks'] = '管理核心能力和架構';
$string['modcompetencies'] = '課程核心能力';
$string['modcompetencies_help'] = '課程核心能力連結到這活動';
$string['move'] = '移動';
$string['movecompetency'] = '移動核心能力';
$string['movecompetencyafter'] = '到\'{$a}\'之後';
$string['movecompetencyframework'] = '移動核心能力架構';
$string['movecompetencytochildofselfwillresetrules'] = '移動這核心能力將會移除它自己的規則，以及為它的上層和目的地所設的規則，你要繼續嗎？';
$string['movecompetencywillresetrules'] = '移動這核心能力將會移除為它的上層和目的地所設的規則，你要繼續嗎？';
$string['moveframeworkafter'] = '把核心能力架構移動到\'{$a}\'之後';
$string['movetonewparent'] = '搬到新地方';
$string['myplans'] = '我的學習計畫';
$string['nfiles'] = '{$a} 個檔案';
$string['noactivities'] = '沒有活動';
$string['nocompetencies'] = '在這一架構中沒有核心能力被建立';
$string['nocompetenciesincourse'] = '沒有核心能力被連結到這一課程';
$string['nocompetenciesinevidence'] = '沒有核心能力連結到這一證據';
$string['nocompetenciesinlearningplan'] = '沒有核心能力被連結到這一學習計畫';
$string['nocompetenciesintemplate'] = '沒有核心能力被連結到這一學習計畫樣版';
$string['nocompetencyframeworks'] = '尚未建立核心能力架構';
$string['nocompetencyselected'] = '沒有核心能力被選出來';
$string['nocrossreferencedcompetencies'] = '沒有其他核心能力被交互參照這一核心能力';
$string['noevidence'] = '沒有證據';
$string['nofiles'] = '沒有檔案';
$string['nolinkedcourses'] = '沒有課程連結到這一核心能力';
$string['noparticipants'] = '沒有發現參與者';
$string['noplanswerecreated'] = '沒有學習計畫被建立';
$string['notemplates'] = '目前沒有學習計畫範本';
$string['nourl'] = '沒有網址';
$string['nouserevidence'] = '還沒有添加先備學習的證據';
$string['nouserplans'] = '還沒有建立學習計畫';
$string['oneplanwascreated'] = '已經建立一個學習計畫';
$string['outcome'] = '結果';
$string['parentcompetency'] = '上層';
$string['parentcompetency_edit'] = '編輯上層';
$string['parentcompetency_help'] = '界定這核心能力將會添加到的上層。它可以是在同一架構內的其他核心能力，或者是代表頂層核心能力的核心能力架構的根。';
$string['path'] = '路徑：';
$string['planapprove'] = '可以開始使用';
$string['plancompleted'] = '學習計畫已完成';
$string['plancreated'] = '學習計畫已建立';
$string['plandescription'] = '描述';
$string['planname'] = '名稱';
$string['plantemplate'] = '選出學習計畫樣版';
$string['plantemplate_help'] = '一個透過樣版建立的學習計畫將會包含配合這樣版的一系列核心能力。若更新這個樣版，將會反映到任何依據這樣版建立的學習計畫。';
$string['planunapprove'] = '送回到草稿狀態';
$string['planupdated'] = '學習計畫已更新';
$string['pluginname'] = '學習計畫';
$string['points'] = '分數';
$string['pointsgivenfor'] = '給{$a}\'的分數';
$string['proficient'] = '精熟';
$string['progress'] = '進度';
$string['rate'] = '評等';
$string['ratecomment'] = '證據的註記';
$string['rating'] = '評等';
$string['ratingaffectsonlycourse'] = '對核心能力進行評等，只更新在這一課程的核心能力';
$string['ratingaffectsuserplans'] = '對核心能力進行評等，也會更新在所有學習計畫的核心能力';
$string['reopenplan'] = '重新開啟這學習計畫';
$string['reopenplanconfirm'] = '要重新開啟學習計畫\'{$a}\'？ 如果這樣做，這用戶先前完成計畫時所記錄的核心能力狀態將會被刪除，且這計畫將會再次激活。';
$string['requestreview'] = '請求審查';
$string['reviewer'] = '審查者';
$string['reviewstatus'] = '審查狀況';
$string['savechanges'] = '儲存改變';
$string['scale'] = '量尺';
$string['scale_help'] = '量尺可以用來測量核心能力的精熟程度。在選定一種量尺之後，需要對它進行配置。

*被選出作為"預設"的項目，就是當核心能力自動完成時所給的評等。
*那些被選為"精熟"指標的項目，它們的數值將會將會在被評等時標示為精熟。';
$string['scalevalue'] = '量尺數值';
$string['search'] = '搜尋中...';
$string['selectcohortstosync'] = '選擇同期學生來進行同步';
$string['selectcompetencymovetarget'] = '選一個這核心能力要移過去的位置：';
$string['selectedcompetency'] = '選出的核心能力';
$string['selectuserstocreateplansfor'] = '選擇要為那些用戶建立學習計畫';
$string['sendallcompetenciestoreview'] = '送出所有審查過的核心能力作為先前學習 \'{$a}\'的證據';
$string['sendcompetenciestoreview'] = '把核心能力送去審查';
$string['shortname'] = '名稱';
$string['sitedefault'] = '(網站預設)';
$string['startreview'] = '開始審查';
$string['state'] = '狀態';
$string['status'] = '狀態';
$string['stopreview'] = '完成審查';
$string['stopsyncingcohort'] = '停止同步同期學生';
$string['taxonomies'] = '分類架構';
$string['taxonomy_add_behaviour'] = '新增行為';
$string['taxonomy_add_competency'] = '新增核心能力';
$string['taxonomy_add_concept'] = '新增概念';
$string['taxonomy_add_domain'] = '新增領域';
$string['taxonomy_add_indicator'] = '新增指標';
$string['taxonomy_add_level'] = '新增層次';
$string['taxonomy_add_outcome'] = '新增結果';
$string['taxonomy_add_practice'] = '新增練習';
$string['taxonomy_add_proficiency'] = '新增精熟';
$string['taxonomy_add_skill'] = '新增技巧';
$string['taxonomy_add_value'] = '新增價值';
$string['taxonomy_edit_behaviour'] = '編輯行為';
$string['taxonomy_edit_competency'] = '編輯核心能力';
$string['taxonomy_edit_concept'] = '編輯概念';
$string['taxonomy_edit_domain'] = '編輯領域';
$string['taxonomy_edit_indicator'] = '編輯指標';
$string['taxonomy_edit_level'] = '編輯層次';
$string['taxonomy_edit_outcome'] = '編輯成果';
$string['taxonomy_edit_practice'] = '編輯練習';
$string['taxonomy_edit_proficiency'] = '編輯精熟';
$string['taxonomy_edit_skill'] = '編輯技巧';
$string['taxonomy_edit_value'] = '編輯價值';
$string['taxonomy_parent_behaviour'] = '上層的行為';
$string['taxonomy_parent_competency'] = '上層的核心能力';
$string['taxonomy_parent_concept'] = '上層的概念';
$string['taxonomy_parent_domain'] = '上層的領域';
$string['taxonomy_parent_indicator'] = '上層的指標';
$string['taxonomy_parent_level'] = '上層的層次';
$string['taxonomy_parent_outcome'] = '上層的結果';
$string['taxonomy_parent_practice'] = '上層的練習';
$string['taxonomy_parent_proficiency'] = '上層的精熟';
$string['taxonomy_parent_skill'] = '上層的技巧';
$string['taxonomy_parent_value'] = '上層的價值';
$string['taxonomy_selected_behaviour'] = '選出的行為';
$string['taxonomy_selected_competency'] = '選出的核心能力';
$string['taxonomy_selected_concept'] = '選出的概念';
$string['taxonomy_selected_domain'] = '選出的領域';
$string['taxonomy_selected_indicator'] = '選出的指標';
$string['taxonomy_selected_level'] = '選出的層次';
$string['taxonomy_selected_outcome'] = '選出的結果';
$string['taxonomy_selected_practice'] = '選出的練習';
$string['taxonomy_selected_proficiency'] = '選出的精熟';
$string['taxonomy_selected_skill'] = '選出的技巧';
$string['taxonomy_selected_value'] = '選出的價值';
$string['template'] = '學習計畫樣版';
$string['templatebased'] = '根據樣板的';
$string['templatecohortnotsyncedwhileduedateispassed'] = '若這樣板已經過期，同期學生無法被同步化';
$string['templatecohortnotsyncedwhilehidden'] = '當這一樣板被隱藏時，同期學生無法被同步化';
$string['templatecompetencies'] = '學習計畫樣版核心能力';
$string['templatecreated'] = '學習計畫樣版已建立';
$string['templatename'] = '名稱';
$string['templates'] = '學習計畫範本';
$string['templateupdated'] = '學習計畫樣版已更新';
$string['totalrequiredtocomplete'] = '總共需要完成的';
$string['unlinkcompetencycourse'] = '從這課程中取消核心能力 \'{$a}\' 的連結？';
$string['unlinkcompetencyplan'] = '從這學習計畫中取消核心能力 \'{$a}\' 的連結？';
$string['unlinkcompetencytemplate'] = '是否從學習計畫樣版中取消核心能力\'{$a}\'的連結？';
$string['unlinkplanstemplate'] = '從樣版中取消學習計畫的連結';
$string['unlinkplantemplate'] = '從學習計畫樣版中取消連結';
$string['unlinkplantemplateconfirm'] = '取消學習計畫 \'{$a}\'與其樣版的連結？如此一來對於樣版所做的任何變更將不會再套用到這學習計畫。此一動作是無法取消的。';
$string['uponcoursecompletion'] = '一旦課程完成：';
$string['uponcoursemodulecompletion'] = '一旦活動完成：';
$string['usercompetencyfrozen'] = '這一記錄現在被凍結了。當用戶的學習計畫被標示為已完成時，它反映用戶的核心能力狀態。';
$string['userevidence'] = '先前學習的證據';
$string['userevidencecreated'] = '先前學習的證據已被建立';
$string['userevidencedescription'] = '描述';
$string['userevidencefiles'] = '檔案';
$string['userevidencename'] = '名稱';
$string['userevidencesummary'] = '摘要';
$string['userevidenceupdated'] = '先前學習的證據已被更新';
$string['userevidenceurl'] = '網址';
$string['userevidenceurl_help'] = '網址必須以 \'http://\' 或 \'https://\'開頭。';
$string['viewdetails'] = '檢視細節';
$string['visible'] = '可見的';
$string['visible_help'] = '核心能力架構在進行設定時或更新到新版本時，可以被隱藏起來。';
$string['when'] = '何時';
$string['xcompetencieslinkedoutofy'] = '在{$a->y}個核心能力中有{$a->x}個有連結到課程';
$string['xcompetenciesproficientoutofy'] = '在{$a->y}個核心能力中有{$a->x}個已經精熟';
$string['xcompetenciesproficientoutofyincourse'] = '在此課程中有{$a->y}個核心能力，你已經精熟{$a->x}個。';
$string['xplanscompletedoutofy'] = '在這樣版中，有{$a->y}個學習計化已完成 {$a->y}個';
