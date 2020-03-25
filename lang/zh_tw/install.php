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
 * Strings for component 'install', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   install
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['admindirerror'] = '指定的管理目錄不正確';
$string['admindirname'] = '管理目錄';
$string['admindirsetting'] = '有些網頁寄存主機會使用 /admin 作為特定的網址，讓您能夠存取控制面板或做其他事情。不幸地，這會跟標準的Moodle管理網頁的位址相衝突。您可以修正這一錯誤：在您的安裝過程中，重新命名管理者目錄，然後將新的目錄名稱放在這兒。例如:<br/> <br /><b>moodleadmin</b><br /> <br />這將可修正Moodle的admin連結。';
$string['admindirsettinghead'] = '設定管理目錄 ...';
$string['admindirsettingsub'] = '有些網頁寄存主機會使用 /admin 作為特定的網址，讓您能夠存取控制面板或做其他事情。不幸地，這會跟標準的Moodle管理網頁的位址相衝突。您可以修正這一錯誤：在您的安裝過程中，重新命名管理者目錄，然後將新的目錄名稱放在這兒。例如:<br/> <br /><b>moodleadmin</b><br /> <br />這將可修正Moodle的admin連結。';
$string['availablelangs'] = '可使用的語言包';
$string['caution'] = '注意';
$string['chooselanguage'] = '選擇一種語言';
$string['chooselanguagehead'] = '選擇一種語言';
$string['chooselanguagesub'] = '請選擇在安裝過程中所用的語言。這語言也將會當作這網站的預設語言。但稍後您可以根據需要再重新選擇。';
$string['cliadminemail'] = '新管理員email地址';
$string['cliadminpassword'] = '新管理員密碼';
$string['cliadminusername'] = '管理員帳號用戶名稱';
$string['clialreadyconfigured'] = '檔案 config.php  已經存在，若你要這一網站上安裝Moodle，請使用admin/cli/install_database.php';
$string['clialreadyinstalled'] = '檔案 config.php  已經存在，若你要在這一網站為Moodle升級，請使用admin/cli/install_database.php';
$string['cliinstallfinished'] = '安裝已經成功地完成。';
$string['cliinstallheader'] = 'Moodle {$a} 命令列安裝程式';
$string['climustagreelicense'] = '在無互動模式，你必須經指定---同意--授權選向來同意授權。';
$string['cliskipdatabase'] = '跳過資料庫的安裝';
$string['clitablesexist'] = '資料庫的資料表已經呈現出來。命令列介面安裝不能繼續。';
$string['compatibilitysettings'] = '檢查您的PHP設定...';
$string['compatibilitysettingshead'] = '檢查您的PHP設定...';
$string['compatibilitysettingssub'] = '您的伺服器必須通過所有測試才能夠正確執行 Moodle。';
$string['configfilenotwritten'] = '這個安裝程式無法自動將您所選擇的設定建立成config.php檔，這可能是因為Moodle目錄無法寫入。您可以手動複製下面的程式碼到Moodle的根目錄下，建立名為config.php的檔案中。';
$string['configfilewritten'] = 'config.php已經成功建立';
$string['configurationcomplete'] = '設定完成';
$string['configurationcompletehead'] = '設定完成';
$string['configurationcompletesub'] = 'Moodle會嘗試將設定資料儲存在您的Moodle根目錄中。';
$string['database'] = '資料庫';
$string['databasehead'] = '資料庫設定';
$string['databasehost'] = '資料庫主機';
$string['databasename'] = '資料庫名稱';
$string['databasepass'] = '資料庫密碼';
$string['databaseport'] = '資料庫端';
$string['databasesocket'] = 'Unix socket';
$string['databasetypehead'] = '選擇資料庫裝置';
$string['databasetypesub'] = 'Moodle支援好幾種類型的資料庫伺服器。若你不知道使用哪一種類型，請聯絡你的伺服器管理員。';
$string['databaseuser'] = '資料庫用戶名稱';
$string['dataroot'] = '資料目錄';
$string['datarooterror'] = '您所指定的\'資料目錄\'找不到或無法建立。請更正路徑，或者手動建立該目錄。';
$string['datarootpermission'] = '資料目錄存取授權';
$string['datarootpublicerror'] = '你指定的"資料目錄"是可以經由網路直接存取的，你必須使用不同的目錄。';
$string['dbconnectionerror'] = '無法連到您指定的資料庫,請查檢您的資料庫設定';
$string['dbcreationerror'] = '建立資料庫錯誤,無法以您給的資料庫名稱建立資料表';
$string['dbhost'] = '主機位址';
$string['dbpass'] = '密碼';
$string['dbport'] = '埠';
$string['dbprefix'] = '資料表名稱的前置字元';
$string['dbtype'] = '類型';
$string['directorysettings'] = '<p>請確認Moodle安裝的位置。</p>

<p><b>網站位址:</b>
指定將存取Moodle的完整網站位址
如果您的網站可以透過多個網址進入，請選擇您的學生們最自然會使用的那個。網址不要包含結尾的斜線。</p>

<p><b>Moodle目錄:</b>
指定此安裝的完整目錄。請確認大小寫是正確的。
</p>

<p><b>資料目錄:</b>
您必須給Moodle存放上傳檔案的空間。這個目錄必須是可以給網站伺服器使用者(通常是\'nobody\'或\'apache\')讀取和"寫入"的權限。但請注意，這個目錄不應該透過網站瀏覽就可以讀取。';
$string['directorysettingshead'] = '請確認 Moodle 安裝的目錄位置';
$string['directorysettingssub'] = '<b>網站位址：</b> 指定存取 Moodle 的完整網址，如果您的網站可以透過多個網址存取，請選擇學生最容易記住的那一個。網址的末尾不要有斜線。<br />
<b>Moodle 目錄：</b> 指定安裝的完整路徑，請確認英文大小寫是否正確。 <br />
<b>資料目錄：</b> 您需要設定一個 Moodle 可以儲存上傳檔案的位置，這個位置要能夠讓網頁伺服器用戶(通常是 \'nobody\' 或 \'apache\')讀取與寫入，但是建議不要放在能夠直接透過網址存取的位置。若此目錄不存在，安裝程式將會自動建立一個。';
$string['dirroot'] = 'Moodle目錄';
$string['dirrooterror'] = '此\'Moodle目錄\'設定似乎不正確-我們無法在這兒找到Moodle安裝程式。下列數值已經重設。';
$string['download'] = '下載';
$string['downloadlanguagebutton'] = '下載 "{$a}" 語言包';
$string['downloadlanguagehead'] = '下載語言包';
$string['downloadlanguagenotneeded'] = '您可以用預設的語言包 "{$a}" 繼續安裝過程。';
$string['downloadlanguagesub'] = '您現在可以選擇下載一個語言包然後用指定的語言繼續安裝過程。<br /><br />如果您無法下載語言包，安裝過程會繼續以英文繼續進行。（只要安裝完成，您還是可以下載、安裝其他的語言包）';
$string['doyouagree'] = '你是否同意？(yes/no)';
$string['environmenthead'] = '檢查您的環境中...';
$string['environmentsub'] = '正在檢查系統的相關元件來確認是否符合安裝需求';
$string['environmentsub2'] = '每一個Moodle版本都有一些PHP版本的最低要求和一堆強制開啟的PHP擴展。在進行安裝或升級之前都需要作完整的環境檢查。<br />
若你不知道要怎樣新的PHP版本或啟用PHP擴展，請聯絡伺服器管理員。';
$string['errorsinenvironment'] = '環境檢查失敗!';
$string['fail'] = '失敗';
$string['fileuploads'] = '檔案上傳';
$string['fileuploadserror'] = '這應該開啟';
$string['fileuploadshelp'] = '<p>你的伺服器似乎已取消檔案上傳功能。</p>
<p>Moodle還是可以安裝，但是沒有這個功能，您就無法上傳課程資料，或新用戶的個人資料圖檔。</p>
<p>若要啟動檔案上傳功能，您(或您的系統管理員)必須要編輯您系統上的主要php.ini檔案，將<b>file_uploads</b> 設定值改為 \'1\'。</p>';
$string['inputdatadirectory'] = '資料目錄：';
$string['inputwebadress'] = '網頁位址：';
$string['inputwebdirectory'] = 'Moodle目錄：';
$string['installation'] = '安裝';
$string['langdownloaderror'] = '很不幸地，語言“{$a}”無法下載安裝。此安裝過程將以英文繼續進行。';
$string['langdownloadok'] = '語言“{$a}”已經成功安裝了。安裝過程將會以此語言繼續。';
$string['memorylimit'] = '記憶體限制';
$string['memorylimiterror'] = 'PHP 執行之記憶體設定過低,您可能稍後會遇到一些問題';
$string['memorylimithelp'] = '<p>你的伺服器的PHP記憶體上限目前設定為{$a}。</p>
<p>稍後它可能會造成Moodle記憶體的問題，尤其當您啟動了很多的模組以及有大量的用戶之後。</p>
<p>若可能，建議您將PHP的上限設得高一點，比如40M。
以下有幾種方式您可以試試：</p>
<ol>
<li>如果可以的話，用<i>--enable-memory-limit</i>重新編譯PHP。讓Moodle自己設定記憶體上限。
<li>如果您可以更改 php.ini 檔，就改變<b>memory_limit</b> 這個設定值，例如，改到40M。如果您無法更改這個檔案，您可以請寄存主機的管理者幫您做。</li>
<li>在一些PHP伺服器上，您可以在Moodle目錄下，建立.htaccess檔，包含這行:

<blockquote>php_value memory_limit 40M</blockquote>

<p>然而，在某些伺服器上，這可能造成<b>所有的</b> PHP 網頁無法運作(當您查看這些網頁時，您就會看到錯誤訊息) 因此，您就必須將 .htaccess 檔案移除。</li>
</ol>';
$string['mssqlextensionisnotpresentinphp'] = 'PHP的 MSSQL擴充套件並未適當安裝，因此無法與SQL*Server連通。請檢查您的php.ini檔案或重新編譯PHP。';
$string['mysqliextensionisnotpresentinphp'] = 'PHP的MySQLi延伸套件沒有做適當的設置，以致無法與MySQL溝通，請檢查您的php.ini檔案或重新編譯PHP。';
$string['nativemariadb'] = 'MariaDB (native/mariadb)';
$string['nativemariadbhelp'] = '此資料庫是用來儲存大多數Moodle的設定和資料，且必須在此配置。

資料庫名稱、用戶名稱、密碼是必填欄位，表格前綴(prefix)則可有可無。

若資料庫目前不存在，但你指定的用戶有這一建立資料庫的權限，Moodle將會依據授權和設定試圖建立一個新資料庫。

此趨動程式不能與以前的MyISAM引擎相容。';
$string['nativemssql'] = 'SQL*Server FreeTDS (native/mssql)';
$string['nativemssqlhelp'] = '現在你需要配置大多數Moodle資料要存放的資料庫。<br />
這資料庫必須已經被建立，且有用戶名稱和密碼可以存取它。它強制使用資料表接首字。';
$string['nativemysqli'] = '改進的 MySQL (native/mysqli)';
$string['nativemysqlihelp'] = '<p>資料庫是用來存放大多數的Moodle設定和資料，你必須在此配置它。</p>
<p>資料庫名稱、用戶名稱、和密碼是必填欄位，而資料表接首字則可有可無。</p>
<p>若資料庫目前不存在，但你指定的用戶有權限，Moodle將會試圖建立一個新資料庫包含有正確的權限和設定。</p>';
$string['nativeoci'] = 'Oracle (native/oci)';
$string['nativeocihelp'] = '現在你需要配置大多數Moodle資料要存放的資料庫。<br />
這資料庫必須已經被建立，且有用戶名稱和密碼可以存取它。它強制使用資料表接首字。';
$string['nativepgsql'] = 'PostgreSQL (native/pgsql)';
$string['nativepgsqlhelp'] = '<p>資料庫是用來存放大多數的Moodle設定和資料，你必須在此配置它。</p>
<p>資料庫名稱、用戶名稱、密碼和資料表接首字是必填欄位。</p>
<p>資料庫必須已經存在，且這用戶必須有權限去讀和寫它。</p>';
$string['nativesqlsrv'] = 'SQL*Server Microsoft (native/sqlsrv)';
$string['nativesqlsrvhelp'] = '現在你需要配置大多數Moodle資料要存放的資料庫。<br />
這資料庫必須已經被建立，且有用戶名稱和密碼可以存取它。它強制使用資料表接首字。';
$string['nativesqlsrvnodriver'] = '可使用PHP的SQL伺服器的Microsoft驅動程式，沒有安裝或沒有配置適當。';
$string['nativesqlsrvnonwindows'] = '可使用PHP的SQL伺服器的Microsoft驅動程式，只可以用於Windows作業系統。';
$string['ociextensionisnotpresentinphp'] = 'PHP的OCI8擴充套件並未適當安裝，因此無法與Oracle連通。請檢查您的php.ini檔案或重新編譯PHP。';
$string['pass'] = '測試通過';
$string['paths'] = '路徑';
$string['pathserrcreatedataroot'] = '資料目錄 ({$a->dataroot})無法由這安裝程式建立';
$string['pathshead'] = '確認路徑';
$string['pathsrodataroot'] = '資料根目錄是無法寫入的';
$string['pathsroparentdataroot'] = '上層目錄({$a->parent})是不可寫入的。安裝程式無法建立資料目錄({$a->dataroot})。';
$string['pathssubadmindir'] = '有些網站主機使用/admin這個網址來瀏覽控制面版或其他功能。很不幸，這個設定和Moodle管理頁面的標準路徑產生衝突。這個問題可以解決，只需在您的安裝目錄中把admin更換名稱，然後把新名稱輸入到這裡。例如<em>moodleadmin</em>這麼做會改變Moodle中的管理連接。';
$string['pathssubdataroot'] = '<p>你需要有一個目錄讓Moodle可以儲存所有的用戶上傳的檔案。</p><p>這目錄對於網頁伺服器用戶(通常是"www-data"、"nobody"或"apache")而言，應該是可讀的和<b>可寫的</b>。</p>
<p>它必須是不能透過網際網路直接存取的。</p>
<p>若此目錄目前不存在，這安裝過程將會試著建立它。</p>';
$string['pathssubdirroot'] = '包含Moodle程式碼的目錄的完整路徑';
$string['pathssubwwwroot'] = '可以進入使用Moodle的完整網址，也就是用戶為了要使用Moodle，而需要輸入到瀏覽器的網址列的地址。

不可能使用多個網址來存取Moodle，如果您的網站有多個公開網址，您必須選擇一個最簡單的網址，並把其他的網址都設定為永久重新導向。

如果您的網站可以透過網際網路，也可以透過內部網路來瀏覽，那麼在此請設定公開的網址。

如果目前的網址不正確，請在你的瀏覽器的網址列中更改網址，並重新安裝。';
$string['pathsunsecuredataroot'] = '資料根(Dataroot)目錄的位置不安全';
$string['pathswrongadmindir'] = '管理目錄不存在';
$string['pgsqlextensionisnotpresentinphp'] = 'PHP的PGSQL擴充套件並未適當安裝，因此無法與PostgreSQL連通。請檢查您的php.ini檔案或重新編譯PHP。';
$string['phpextension'] = '{$a} PHP擴展';
$string['phpversion'] = 'PHP版本';
$string['phpversionhelp'] = '<p>Moodle 需要的PHP版本至少要4.3.0或是5.1.0 (5.0.x有一些已知的問題) </p>
<p>您目前執行的版本是{$a}</p>
<p>您必須更新您的 PHP 或在有更新版本的主機上進行安裝！(若是5.0.x，你可以下降到4.4.x 版本)</p>';
$string['releasenoteslink'] = '要知道Moodle的這一版本的資訊，請參見 {$a}';
$string['safemode'] = '安全模式';
$string['safemodeerror'] = 'Moodle 在安全模式啟動時可能會發生錯誤';
$string['safemodehelp'] = '<p>Moodle在開啟安全模式時，可能會有許多的問題，而不只是無法建新檔案。
<p>安全模式通常只有在繁雜的公眾網頁寄放主機上才會啟動，所以您可能必須尋找新的網站寄放公司來放置您的Moodle網站。</p>
<p>如果您願意，您可以繼續安裝，但稍後就可能會有一些問題出現。</p>';
$string['sessionautostart'] = 'Session自動啟動';
$string['sessionautostarterror'] = '這應該關閉';
$string['sessionautostarthelp'] = '<p>Moodle 需要有session功能的支援,沒有它無法正確動作 .</p>
<p>Sessions 可以在 php.ini 檔案中啟動,請尋找其中 session.auto_start 參數.</p>';
$string['sqliteextensionisnotpresentinphp'] = 'PHP對於SQLite擴展沒有適當的設定。
請檢查你的php.ini或重新編譯PHP。';
$string['upgradingqtypeplugin'] = '升級試題/類型外掛套件';
$string['welcomep10'] = '{$a->installername} ({$a->installerversion})';
$string['welcomep20'] = '這個頁面是提醒您已經成功安裝與啟動 <strong>{$a->packname} {$a->packversion}</strong> ，恭喜！';
$string['welcomep30'] = '這一<strong>{$a->installername}</strong>的版本包含了一些可以建立<strong>Moodle</strong>執行環境的應用程序：';
$string['welcomep40'] = '這個軟體還包含了<strong>Moodle {$a->moodlerelease} ({$a->moodleversion})</strong>。';
$string['welcomep50'] = '使用本軟體包所包含的所有應用程序時，應遵循它們各自的授權協議。整個<strong>{$a->installername}</strong> 軟體都是<a href="http://www.opensource.org/docs/definition_plain.html">開放原始碼</a> ，並且是在 <a href="http://www.gnu.org/copyleft/gpl.html">GPL</a> 授權協議下發佈。';
$string['welcomep60'] = '接下來的一些頁面將會透過一些簡單的步驟引導您配置和設定在你電腦上的 <strong>Moodle</strong> 。
您可以接受這些預設值，或是針對你自己的需要來調整。';
$string['welcomep70'] = '點選 "下一步" 按鈕，繼續設定<strong>Moodle</strong>.';
$string['wwwroot'] = '網站位址';
$string['wwwrooterror'] = '指定網路位址不存在-這個Moodle 系統並不在您指定的地方';
