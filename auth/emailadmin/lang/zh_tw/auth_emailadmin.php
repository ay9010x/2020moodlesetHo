<?php


 
defined('MOODLE_INTERNAL') || die();

$string['auth_emailadmindescription'] = '<p>Email-based self-registration with Admin confirmation enables a user to create their own account via a \'Create new account\' button on the login page. The site admins then receive an email containing a secure link to a page where they can confirm the account. Future logins just check the username and password against the stored values in the Moodle database.</p><p>Note: In addition to enabling the plugin, email-based self-registration with admin confirmation must also be selected from the self registration drop-down menu on the \'Manage authentication\' page.</p>';
$string['auth_emailadminnoemail'] = 'Tried to send you an email but failed!';
$string['auth_emailadminrecaptcha'] = 'Adds a visual/audio confirmation form element to the signup page for email self-registering users. This protects your site against spammers and contributes to a worthwhile cause. See http://www.google.com/recaptcha/learnmore for more details. <br /><em>PHP cURL extension is required.</em>';
$string['auth_emailadminrecaptcha_key'] = '啟動 reCAPTCHA 字詞驗證元件';

$string['auth_emailadminsettings'] = 'Settings';
$string['auth_emailadminuserconfirmation'] = '
親愛的使用者 {$a->firstname}, 您好:

歡迎來到 <a href=http://210.65.47.16/>MoodleSET</a> ! 您的帳號已經開通了。 (如果您未做任何的申請，請忽略此郵件。)
 
您的帳號為<b>系所管理員</b>權限，我們有建立一個 <a href=https://www.facebook.com/groups/moodleset/>FB社團</a> 協助此次活動進行, 這將有助於測試 <a href=http://210.65.47.16/>MoodleSET</a> 功能, 與意見回饋。

很高興有您的加入，期待 MoodleSET 平台可以為 貴校帶來數位學習的優質運用，如果您需要幫助，歡迎造訪我們的網站<a href=http://www.click-ap.com>http://www.click-ap.com</a>，或寫信到<a href=mailto:moodleset@click-ap.com>moodleset@click-ap.com</a>，我們將盡力協助您解決問題。
 
 
什麼是<a href=http://www.click-ap.com/moodleset>MoodleSET</a>？

共享經濟的基本概念是將沒有充分利用的資源(或閒置的資源)共享給別人，提高資源利用率(符合環保的概念)，並從中獲得回報；共享經濟的理念是共同使用而不獨自佔有，共享經濟的本質是互助和互利。

在相同的理念下，國立交通大學著手推動台灣共享教育(Sharing Education Taiwan，SET)，因此成立「校園共享版moodleset平台」。
 
期待您在MoodleSET平台營造出教與學的樂趣，並提升線上學習的優質環境。
 
謝謝您。
Sincerely,
 
Christine Tan, MoodleSET開發團隊
 
<a href=http://www.click-ap.com/moodleset>維聖資訊科技有限公司</a><br/>

<a href=http://www.click-ap.com>http://www.click-ap.com</a><br/>

Click-AP Software Technology Ltd.<br/>

<a href=mailto:moodleset@click-ap.com>moodleset@click-ap.com</a><br/>
<a href=tel:886-4%203608%200088>Tel:886-4-3608-0088</a><br/>
14-5. no 238. JinHua Nr. Rd. North dist. Taichung, Taichung Municipality, Taiwan<br/>
以上電子郵件係由署名者發出，如內容涉及本公司與受文者之權益，仍以本公司正式書面文件為憑。<br/>
';

$string['auth_emailadminconfirmation'] = '
Hi Moodle Admin,

A new account has been requested at \'{$a->sitename}\' with  the following data:

Any specific user field example:
user->lastname: {$a->lastname}

核准系所管理員新帳號, 請連到這個網址:

{$a->link}

In most mail programs, this should appear as a blue link which you can just click on.  If that doesn\'t work, then cut and paste the address into the address line at the top of your web browser window.

You can also confirm accounts from within Moodle by going to Site Administration -> Users

';
$string['auth_emailadminconfirmationsubject'] = '{$a} : 系所管理員-帳號申請';
$string['auth_emailadminconfirmsent'] = '<p>
Your account has been registered and is pending confirmation by the administrator. You should expect to either receive a confirmation or to be contacted for further clarification.</p>
';
$string['auth_emailadminnotif_failed'] = 'Could not send registration notification to: ';
$string['auth_emailadminnoadmin'] = 'No admin found based on notification strategy. Please check auth_emailadmin configuration.';
$string['auth_emailadminnotif_strategy_key'] = 'Notification strategy:';
$string['auth_emailadminnotif_strategy'] = 'Defines the strategy to send the registration notifications. Available options are "first" admin user, "all" admin users or one specific admin user.';
$string['auth_emailadminnotif_strategy_first'] = 'First admin user';
$string['auth_emailadminnotif_strategy_all'] = 'All admin users';

$string['pluginname'] = 'Email with Admin-confirm';
