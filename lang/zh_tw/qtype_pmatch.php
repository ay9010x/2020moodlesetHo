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
 * Strings for component 'qtype_pmatch', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_pmatch
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmoreanswerblanks'] = '{no} 更多答案的空白';
$string['addmoresynonymblanks'] = '{no} 更多同義詞的空白';
$string['allowsubscript'] = '允許使用下標';
$string['allowsuperscript'] = '允許使用上標';
$string['answer'] = '答案：{$a}';
$string['answeringoptions'] = '輸入答案的選項';
$string['answermustbegiven'] = '如果有成績或回饋的話，必顧輸入一個答案';
$string['answerno'] = '答案{$a}';
$string['answeroptions'] = '答案選項';
$string['anyotheranswer'] = '任何其他答案';
$string['applydictionarycheck'] = '檢查學生的拼字';
$string['caseno'] = '不，大小寫不重要';
$string['casesensitive'] = '大小寫是否要一致';
$string['caseyes'] = '是，大小寫要符合';
$string['combinedcontrolnamepmatch'] = '文字輸入';
$string['converttospace'] = '轉換以下的字符為空隔';
$string['correctanswers'] = '正確問題';
$string['env_dictmissing'] = '已安裝了已安裝語言{$a->humanfriendlylang}的缺少拼字檢查字典{$a->langforspellchecker}';
$string['env_dictmissing2'] = '學生嘗試了此語言\'{$a}\'的拼字檢查。但此語言的拼字檢查字典尚未安裝';
$string['env_dictok'] = '已安裝了已安裝語言{$a->humanfriendlylang}的拼字檢查字典{$a->langforspellchecker}';
$string['environmentcheck'] = 'pmatch題型的環境檢查';
$string['env_peclnormalisationmissing'] = 'Unicode正規化的PECL套件看來沒有正確地安裝';
$string['env_peclnormalisationok'] = 'Unicode正規化的PECL套件已正確地安裝';
$string['env_pspellmissing'] = 'Pspell庫看來沒有正確地安裝';
$string['env_pspellok'] = 'Pspell庫看已正確地安裝';
$string['errors'] = '請解決以下問題：{$a}';
$string['err_providepmatchexpression'] = '您必須在此提供一個pmatch表述';
$string['extenddictionary'] = '增加這些詞語到字典中';
$string['filloutoneanswer'] = '使用模式配對句法來描述正確答案。您必須提供至少一個可能的答案。留空的答案將不被使用。第一個配對的答案將會用作決定分數及回饋。';
$string['forcelength'] = '如果答案超過20字';
$string['forcelengthno'] = '不要發出警告';
$string['forcelengthyes'] = '警告過長的答案並邀請回答者縮短答案';
$string['ie_illegaloptions'] = '表述中的非法選項"match<strong><em>{$a}</em></strong>()".';
$string['ie_lastsubcontenttypeorcharacter'] = '或字符必定不能完結在"{$a}"的子內容';
$string['ie_lastsubcontenttypeworddelimiter'] = '單詞分隔符不能完結在"{$a}"的子內容';
$string['ie_missingclosingbracket'] = '缺少了代碼段"{$a}"的右括號';
$string['ie_nofullstop'] = '句號符號不能在pmatch表述中使用。（可以使用數字中的點數）';
$string['ie_nomatchfound'] = '在模式配對段的錯誤';
$string['ie_unrecognisedexpression'] = '不能識別的表述';
$string['ie_unrecognisedsubcontents'] = '在不能識別的中的不能識別子內容';
$string['inputareatoobig'] = '"{$a}"界定的輸入範圍太大。輸入範圍大小限制至150字符闊度及100字符的長度。';
$string['nomatchingsynonymforword'] = '沒有輸入單詞的同義詞。刪除單詞或輸入其同義詞。';
$string['nomatchingwordforsynonym'] = '您尚未輸入與單詞相同的同義詞。刪除單詞或輸入其同義詞。';
$string['notenoughanswers'] = '此題型需要只少{$a}答案';
$string['pleaseenterananswer'] = '請輸入一答案';
$string['pluginname'] = '模式配對';
$string['pluginnameadding'] = '增加模式配對題目';
$string['pluginnameediting'] = '編輯模式配對題目';
$string['pluginname_help'] = '要回答（可能包括圖片的）題目，回答者輸入短語。這可能有多個不同分數的正確答案。如果已選擇了「區分大小寫」，輸入"Word" 或 "word"會令您將會得到不同的分數。';
$string['pluginnamesummary'] = '允許包括數句的短語作答案，用作對比多個以OU模式配對句法形式的標準答案。';
$string['repeatedword'] = '此詞語在同義詞清單中出現多於一次';
$string['spellcheckerenchant'] = '迷惑拼寫檢查庫';
$string['spellcheckernull'] = '沒有可用的拼寫檢查';
$string['spellcheckerpspell'] = 'Pspell拼寫檢查庫';
$string['spellcheckertype'] = '拼寫檢查庫';
$string['spellcheckertype_desc'] = '選擇那一個拼寫檢查庫。將會自動在安裝時設定正確數值。';
$string['spellingmistakes'] = '下列的單詞不在的們的字典中：{$a}。請修改您的拼寫。';
$string['subsuponelineonly'] = '上／下標編輯器只能用在一行高的輸入框';
$string['synonym'] = '同義詞';
$string['synonymcontainsillegalcharacters'] = '同義詞包含了非法字符';
$string['synonymsheader'] = '界定答案中的文字同義詞';
$string['synonymsno'] = '同義詞{$a}';
$string['testquestionactualmark'] = '真正分數';
$string['testquestionformheader'] = '標記要上載的回答';
$string['testquestionforminfo'] = '您需要上載有兩欄的CSV檔案。第一欄包含了回答的期進分數，第二欄將會包含了回答。檔案的第一行應包括欄標題。';
$string['testquestionformtitle'] = '模式配對題目的測試工具';
$string['testquestionformuploadlabel'] = '已標記的回答';
$string['testquestionheader'] = '測試題目：{$a}';
$string['testquestionresponse'] = '回答';
$string['testquestionresultsheader'] = '測驗結果：{$a}';
$string['testquestionresultssummary'] = '標記為正確：<b>{$a->correct}</b>，錯誤地標記為錯誤<b>{$a->incorrectlymarkedwrong}</b>，錯誤地標記為正確<b>{$a->incorrectlymarkedright}</b>。';
$string['testquestionuploadanother'] = '上載其他項目';
$string['testthisquestion'] = '測試此題目';
$string['toomanywords'] = '您的答案太長。請修改至不長於20未的答案。';
$string['unparseable'] = '我們不明白在這的字符及標點"{$a}"';
$string['wordcontainsillegalcharacters'] = '文字包括了非法字符';
$string['wordwithsynonym'] = '文字';
