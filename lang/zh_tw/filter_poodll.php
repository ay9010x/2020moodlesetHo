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
 * Strings for component 'filter_poodll', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   filter_poodll
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activate'] = '要啟用PoodLL嗎?';
$string['alwayshtml5'] = '總是使用HTML5';
$string['audiotranscode'] = '自動轉換到mp3';
$string['audiotranscodedetails'] = '將記錄/上傳的影音檔轉換為MP3格式,然後存儲到Moodle.這適用於在tokyo.poodll.com上進行的錄製,或如果使用FFMPEG所上傳的錄製記錄.';
$string['autotryports'] = '無法連線時自動嘗試不同的通訊埠';
$string['awssdkversion'] = 'AWS SDK';
$string['awssdkversion_desc'] = 'PoodLL雲端錄製功能使用Amazon Web Services (AWS).支援版本3.x,但不隨PoodLL一起提供.AWS SDK的2.x版適用於PHP 5.3或更高版本.您應該不需要改變這個項目,但是如果您需要變更,請聯繫PoodLL支援.';
$string['bandwidth'] = '學生連接.位元組/秒 <br/>影響網路攝影機的品質';
$string['bgtranscode_audio'] = '在背景執行轉換MP3';
$string['bgtranscodedetails_audio'] = '這比在使用端等待執行更可靠.但在轉檔完成前使用者將不會得到音訊檔.只能執行在使用FFMPEG和Moodle 2.7或更高版本才能運作.對於使用MP3錄音器的MP3錄音,在瀏覽器中轉換進行,而不是在伺服器上進行. 因此不會使用到伺服器端的轉換（FFMPEG）.';
$string['bgtranscodedetails_video'] = '這比在使用端等待執行更可靠.但在轉檔完成前使用者將不會得到視訊檔.只能執行在使用FFMPEG和Moodle 2.7或更高版本才能運作.';
$string['bgtranscode_video'] = '在背景執行轉換MP4';
$string['bundle'] = '綁定';
$string['burntrose_recorder'] = '紅玫瑰';
$string['cameraback'] = '後';
$string['camerafront'] = '前';
$string['cancel'] = '取消';
$string['capturefps'] = '視訊記錄器畫面擷取速度FPS';
$string['captureheight'] = '視訊記錄器擷取高度';
$string['capturewidth'] = '視訊記錄器畫面擷取大小';
$string['dataset'] = '資料集';
$string['dataset_desc'] = 'Poodll允許您從資料庫中提取資料集以便在您的樣板中使用.這是一個進階功能.請在$DB->get_records_sql填入SQL查詢指令';
$string['datasetvars'] = '資料集變數';
$string['datasetvars_desc'] = '放一個以逗號分隔的變數列表,組成SQL的變數.您可以,也或許將會想要在這使用到變數.';
$string['default_camera'] = '預設攝影機';
$string['defaultwhiteboard'] = '預設白板';
$string['expired'] = '因為PoodLL註冊已過期所以不會顯示,請尋求您的教師/管理員在PoodLL.com續訂註冊.';
$string['exportdiagnostics'] = '匯出';
$string['extensions'] = '延伸檔名';
$string['extensions_desc'] = '這個過濾器可以解析的副檔案名稱清單';
$string['extensionsettings'] = '延伸檔案設定';
$string['ffmpeg'] = '使用FFMPEG轉換上傳的媒體檔';
$string['ffmpeg_details'] = 'FFMPEG必須安裝在您的Moodle伺服器和系統路徑上.它必需支援轉換mp3的功能,所以請在命令列上試試,例如ffmpeg -i somefile.flv somefile.mp3';
$string['filtername'] = 'PoodLL過濾器';
$string['filter_poodll_audioplayer_heading'] = '音訊播放器設定';
$string['filter_poodll_camera_heading'] = '網路攝影機設定';
$string['filter_poodll_flashcards_heading'] = '閃卡設定';
$string['filter_poodll_html5recorder_heading'] = 'HTML5錄音器設定';
$string['filter_poodll_mic_heading'] = '麥克風設定';
$string['filter_poodll_mp3recorder_heading'] = 'MP3錄音器設定';
$string['filter_poodll_network_heading'] = 'PoodLL網路設定';
$string['filter_poodll_registration_explanation'] = 'PoodLL 3需要有一個註冊碼.如果您還沒有,請造訪Poodll.com來取得.';
$string['filter_poodll_registration_heading'] = '註冊您的PoodLL';
$string['filter_poodll_videogallery_heading'] = '視訊相簿設定';
$string['filter_poodll_videoplayer_heading'] = '視訊播放器設定';
$string['filter_poodll_whiteboard_heading'] = '白板設定';
$string['flashcardstype'] = '閃卡類型';
$string['generalsettings'] = '一般設定';
$string['handle'] = '支援 {$a}';
$string['highquality'] = '高';
$string['html5recorder_skin'] = 'HTML5記錄器外觀';
$string['insert'] = '插入';
$string['license_details'] = '<br> -------------- <br> 授權類型: {$a->license_type} <br> 授權期限(JST): {$a->expire_date} <br> 註冊的URL: {$a->registered_url}';
$string['lowquality'] = '低';
$string['mediumquality'] = '中';
$string['miccanpause'] = '允許暫時停止(只有MP3記錄器)';
$string['micecho'] = '麥克風回聲';
$string['micgain'] = '麥克風增益';
$string['micloopback'] = '麥克風循環播放';
$string['micrate'] = '麥克風頻率';
$string['micsilencelevel'] = '麥克風無聲級別';
$string['mobileandwebkit'] = 'Mobile + Webkit瀏覽器(如Safari,Chrome 等)';
$string['mobile_audio_quality'] = '音訊品質';
$string['mobileonly'] = '僅限行動裝置';
$string['mobile_os_version_warning'] = '<p>您的作業系統版本太低</p>
		<p>Android需要版本4或更高版本.</p>
		<p>iOS需要版本6或更高版本.</p>';
$string['mobilesettings'] = 'iOS App 設定';
$string['mobile_show'] = '顯示可移動';
$string['mobile_show_desc'] = '當使用者是在iOS設備上,而不是錄音器時,他們會看到一個“上傳/錄製”按鈕和“使用PoodLL app”按鈕.取消勾選此項目可隱藏PoodLL app按鈕.';
$string['mobile_video_quality'] = '視訊品質';
$string['mp3_nocloud'] = '不使用雲端';
$string['mp3_nocloud_details'] = '請不要將Flash mp3記錄上傳到雲端進行轉換和複製.';
$string['mp3opts'] = 'FFMPEG MP3轉換選項';
$string['mp3opts_details'] = '如果您希望讓FFMPEG決定,請保留空白. 任何您放在這裡的東西將出現在[ffmpeg -i myfile.xx ]和[ myfile.mp3 ]';
$string['mp3skin'] = 'MP3記錄器外觀';
$string['mp3skin_details'] = '如果您想要使用一個記錄器外觀,ala主題,請在此輸入它的名稱.否則請輸入none.';
$string['mp4opts'] = 'FFMPEG MP4轉換選項';
$string['mp4opts_details'] = '如果您希望讓FFMPEG決定,請保留空白. 任何您放在這裡的東西將出現在[ffmpeg -i myfile.xx]和[myfile.mp4]';
$string['neverhtml5'] = '絕不使用HTML5';
$string['normal'] = '標準的';
$string['picqual'] = '目標網路攝影機的品質1 - 10';
$string['plain_recorder'] = '樸素';
$string['player'] = '播放器 {$a}';
$string['pluginname'] = 'PoodLL過濾器';
$string['poodll:candownloadmedia'] = '可以下載媒體';
$string['poodllsupportinfo'] = 'PoodLL支援資訊';
$string['presets'] = '使用先前設定好的自動樣板';
$string['presets_desc'] = 'PoodLL提供了一些預設的先前設定,您可以直接就套用,或是幫助您開始使用自有的樣板.請在這裡選擇其中一個,或是僅新增您的自有樣板.您可以點擊上面的綠色框將樣板匯出.您可以藉由拖曳到綠色框中來匯入.';
$string['recorderorder'] = '優先的記錄器順序';
$string['recorderorder_desc'] = 'PoodLL將選擇最好的記錄器.如果使用者的瀏覽器和平台支援它.請您在這裡設定它的順序.';
$string['recui_audiogain'] = '音訊增益';
$string['recui_audiorate'] = '音訊頻率';
$string['recui_awaitingconfirmation'] = '等待確認';
$string['recui_btnupload'] = '錄製或選擇檔案';
$string['recui_cancelsnapshot'] = '取消';
$string['recui_close'] = '關閉';
$string['recui_continue'] = '繼續';
$string['recui_converting'] = '轉檔';
$string['recui_echo'] = '回聲抑制';
$string['recui_inaudibleerror'] = '我們無法聽到您的聲音.請檢查flash和瀏覽器的權限.';
$string['recui_loopback'] = '回送';
$string['recui_nothingtosaveerror'] = '沒有記錄任何東西.很抱歉,儲存失敗了.';
$string['recui_off'] = '關';
$string['recui_ok'] = '確定';
$string['recui_on'] = '開';
$string['recui_openrecorderapp'] = 'PoodLL App';
$string['recui_pause'] = '暫停';
$string['recui_play'] = '播放';
$string['recui_record'] = '錄製';
$string['recui_recordorchoose'] = '記錄或選擇';
$string['recui_save'] = '儲存';
$string['recui_silencelevel'] = '無聲級別';
$string['recui_stop'] = '停止';
$string['recui_takesnapshot'] = '拍照';
$string['recui_time'] = '時間:';
$string['recui_timeouterror'] = '抱歉.您的請求超時了.';
$string['recui_uploadafile'] = '上傳檔案';
$string['recui_uploaderror'] = '發生錯誤,您的檔案尚未上傳.';
$string['recui_uploading'] = '上傳';
$string['recui_uploadsuccess'] = '已成功上傳';
$string['registrationkey'] = '註冊碼';
$string['registrationkey_explanation'] = '請在此輸入您的PoodLL註冊碼.您可以從網站獲取它.<a href=\'https://poodll.com/poodll-3-2\'>https://poodll.com/poodll-3-2</a>';
$string['serverhttpport'] = 'PoodLL 伺服器通訊埠(HTTP)';
$string['serverid'] = 'PoodLL伺服器Id';
$string['servername'] = 'PoodLL主機位址';
$string['serverport'] = 'PoodLL伺服器通訊埠(RTMP)';
$string['settings'] = 'PoodLL過濾器設定';
$string['showdownloadicon'] = '在播放器顯示下載圖示';
$string['sitedefault'] = '站點預設';
$string['size'] = '尺寸';
$string['studentcam'] = '優先使用的攝影機設備名稱';
$string['studentmic'] = '優先使用的麥克風設備名稱';
$string['supportinfo'] = '支援資訊';
$string['template'] = '樣板的主體{$a}';
$string['templatealternate'] = '替代的內容 (樣板 {$a})';
$string['templatealternate_desc'] = '當客製化的CSS和JavaScript內容不能使用時可以使用的內容. 目前,當樣板由Web服務處理時, 可用於行動應用上的內容.';
$string['templatealternate_end'] = '替代的內容結束 ((樣板 {$a})';
$string['templatealternate_end_desc'] = '關閉包含帶有開始和結束PoodLL標籤的使用者內容樣板的備用內容標記';
$string['templatecount'] = '樣板數目';
$string['templatecount_desc'] = '您可以擁有的樣板數量.預設值為20個.';
$string['templatedefaults'] = '預設變數(樣板{$a})';
$string['templatedefaults_desc'] = '定義以逗號分隔的name=value pairs的預設值.例如width=800,height=900,feeling=joy';
$string['template_desc'] = '請把樣板放在這哩,並用@@記號包圍定義的變數,例如@@variable@@';
$string['templateend'] = '結束標記(樣板{$a})';
$string['templateend_desc'] = '如果你的樣板包含使用者內容,例如資訊框,請將關閉標籤放在此處.使用者將會輸入像{POODLL:mytag_end}來關閉過濾器.';
$string['templateheading'] = 'Poodll樣板的設定 {$a}';
$string['templateheadingcss'] = 'CSS/Style設定.';
$string['templateheadingjs'] = 'Javascript設定';
$string['templateinstructions'] = '說明(樣板{$a})';
$string['templateinstructions_desc'] = '在此輸入的任何指令將顯示在PoodLL atto表單上,如果這個樣板可以在那裡顯示.請保持簡短扼要否則會導致外觀不佳.';
$string['templatekey'] = '用來識別樣板的識別字串{$a}';
$string['templatekey_desc'] = '這個識別字串應該是一個單字,並且只包含數字和字母、底線、連字符和點.';
$string['templatename'] = '樣板的顯示名稱{$a}';
$string['templatename_desc'] = '名稱可以包含數字和字母、底線、連字符和點.';
$string['templatepageheading'] = '(T): {$a}';
$string['templatepageplayerheading'] = '(P): {$a}';
$string['templatepagewidgetheading'] = '(W): {$a}';
$string['templaterequire_amd'] = '透過AMD載入';
$string['templaterequire_amd_desc'] = 'AMD是一個javascript載入機制.如果您上傳或連結到javascript libraries在您的樣板中.您必須取消勾選它.它只有在Moodle 2.9或更高版本才有作用.';
$string['templaterequire_css'] = '需要CSS (樣板{$a})';
$string['templaterequire_css_desc'] = '此樣板需要的外部CSS檔案的連接(僅限1個).選填項目.';
$string['templaterequire_jquery'] = '需要JQuery (樣板{$a})';
$string['templaterequire_jquery_desc'] = '最好不要勾選此項目.許多非AMD樣板需要JQuery.在此勾選將載入JQuery,但不是很好.您的佈景主題可能已經載入JQuery了,如果沒有,請將此字串加入到網站管理 -> 外觀 -> 額外的HTML (放在HEAD標籤內):<br/>  &lt;script src="https://code.jquery.com/jquery-1.11.2.min.js"&gt;&lt;/script&gt;';
$string['templaterequire_js'] = '需要JS (樣板{$a})';
$string['templaterequire_js_desc'] = '此樣板需要的外部JS檔案的連接(僅限1個).選填項目.';
$string['templaterequire_js_shim'] = '匯出Shim(樣板{$a})';
$string['templaterequire_js_shim_desc'] = '如果您需要shim那就必須輸入 shim的匯出值';
$string['templatescript'] = '客製化 JS (樣板{$a})';
$string['templatescript_desc'] = '如果您的樣板需要運行自定義javascript,請在此處輸入.它將在頁面上加載所有元素後運行.';
$string['template_showatto'] = '在Atto中顯示(樣板{$a})';
$string['template_showatto_desc'] = '在Atto的PoodLL小工具對話框中顯示此工具的按鈕和表單.';
$string['template_showplayers'] = '在播放器清單中顯示(樣板{$a})';
$string['template_showplayers_desc'] = '在相關的播放器下拉選單顯示連結的附檔名.';
$string['templatestyle'] = '客製化 CSS (樣板{$a})';
$string['templatestyle_desc'] = '輸入任一個您的樣板在此處使用的自定義CSS.樣板變數將不會在此運作.只是普通的舊CSS.';
$string['tiny'] = '極小的';
$string['transcode_heading'] = '音訊/視訊檔案轉換設定';
$string['unregistered'] = '因為PoodLL它沒有註冊所以不會顯示.請尋求您的老師/管理員在PoodLL.com註冊PoodLL.';
$string['uploadkey'] = '上傳密鑰';
$string['uploadkey_desc'] = 'PoodLL雲端記錄要求上傳密鑰以進行記錄.您應該會在註冊PoodLL時收到此訊息通知.請在此處輸入您的上傳密鑰.';
$string['uploadsecret'] = '上傳金鑰';
$string['uploadsecret_desc'] = 'PoodLL雲端記錄需要一個上傳的金鑰.您應該會在註冊PoodLL時收到此訊息通知.請在此處輸入您的上傳金鑰';
$string['usecloudrecording'] = '雲端錄製';
$string['usecloudrecording_desc'] = 'PoodLL雲端影音錄製.提供影音內容的轉換和其他在雲端上的服務.PoodLL iOS app需要這個項目,html5音訊和視訊記錄器也是.這些所記錄的檔案並不會存在雲端上.';
$string['useplayer'] = '{$a} 播放器';
$string['useplayerdesc'] = '您所選的播放器將使用來自適當的樣板資訊.';
$string['value'] = '值';
$string['videotranscode'] = '自動轉換到mp4';
$string['videotranscodedetails'] = '將記錄/上傳的影音檔轉換為MP4格式,然後存儲到Moodle.這適用於在tokyo.poodll.com上進行的錄製,或如果使用FFMPEG所上傳的錄製記錄.';
$string['wboardautosave'] = '自動儲存(毫秒)';
$string['wboardautosave_details'] = '當使用者在X毫秒後停止繪製時,儲存繪圖.0代表不會自動儲存';
$string['wboardheight'] = '白板預設高度';
$string['wboardwidth'] = '白板預設寬度';
$string['whiteboardsave'] = '儲存圖片';
$string['widgetsettings'] = '小工具設定';
