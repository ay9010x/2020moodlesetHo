# Google Analytics Injection (for Taipei Travel)
GA偵測事件

1. 活動開始，記錄使用者 start_exp("使用者名字")
2. 活動結束 end_exp()

=====================

# TODO (主要請看Issues)
https://github.com/billxu0521/GA-project/issues
- 計時器功能錯誤，請見#TODO ga_inject_lib.js
- 變數名稱規範： 區域變數或函數前面要加底線，字與字中間用底線隔開，例如「_var」；固定的常數要全部大寫，字與字之間用底線隔開，例如「MOUSE_CONFIG」
- 不要直接用function宣告函數，應用 var _func = function () { /*script*/ }; 宣告函數。注意最後的「;」
- 許多函式缺少說明，函式前面的說明全部改成
- ga("send", "event"...) 最後還要加上事件類型，像是"click"或"mouseover"

# 匯入(安裝)教學
* 注意：要將 page_inject_js 中的*.js對應到網站中的網址，例如「config/www.travel.taipei.js」對應到「 https://www.travel.taipei/ 」。
每個網頁都會套用不同的事件偵測，所以匯入匯出必須要能批次處理多個網頁

1. 選擇函式庫(Libraries)分頁

![2017-03-15 1 20 06](https://cloud.githubusercontent.com/assets/3469079/23913333/95c05158-091d-11e7-8ab2-86e4cb826bfe.png)

2. 輸入三組函式庫：

- www.travel.taipei / https://billxu0521.github.io/GA-project/config/www.travel.taipei.js

![2017-03-15 1 24 55](https://cloud.githubusercontent.com/assets/3469079/23913522/3c1d2152-091e-11e7-880c-84c6156c5655.png)


3. 切換至網站(Sites)分頁，選擇Add new site
![2017-03-15 1 26 02](https://cloud.githubusercontent.com/assets/3469079/23913617/858a0a44-091e-11e7-9d8b-3cc42abf9b4d.png)

4. 左上角輸入台北旅遊網網址 「https://www.travel.taipei/*」 (注意*星號)，JS欄位要寫上 「 DEGUB = true; 」
![2017-03-15 1 26 42](https://cloud.githubusercontent.com/assets/3469079/23913612/820a21ec-091e-11e7-8f1c-3840f44c2668.png)

5. 開啟設定，如圖打勾
![2017-03-15 1 27 39](https://cloud.githubusercontent.com/assets/3469079/23913681/a6708940-091e-11e7-90c5-4bead762743f.png)

6. 右上角點擊「Save」即可使用
![2017-03-15 1 27 47](https://cloud.githubusercontent.com/assets/3469079/23913685/a939dafa-091e-11e7-924f-cfce86290936.png)

# =============================

# 設定userID教學
1. 啟動偵測腳本
![2017-03-15 1 27 39](https://cloud.githubusercontent.com/assets/3469079/23931423/07338932-096d-11e7-92f6-08b9a158bdab.png)

2. 進入台北旅遊網 https://www.travel.taipei
![2017-03-15 10 48 00](https://cloud.githubusercontent.com/assets/3469079/23931418/04a16446-096d-11e7-9c70-03beaa1906e5.png)

3. 右鍵選擇檢查
![Uploading 螢幕快照 2017-03-15 上午10.48.05.png…]()

4. 分頁切換到console
![2017-03-15 10 48 14](https://cloud.githubusercontent.com/assets/3469079/23931403/fbda431e-096c-11e7-976a-e4ee104b8565.png)

5. 確認有無"Google analytics injected."
![2017-03-15 10 48 27](https://cloud.githubusercontent.com/assets/3469079/23931426/0a313864-096d-11e7-99ce-2ce3fdc4a419.png)

6. 在6.字樣下方輸入 set_user_id("欲輸入ID")
![2017-03-15 10 49 42](https://cloud.githubusercontent.com/assets/3469079/23931455/265965b6-096d-11e7-90ca-b11a0b104ad8.png)

7. 若出現"Set UserID;輸入ID"表示設定成功
![2017-03-15 10 49 47](https://cloud.githubusercontent.com/assets/3469079/23931458/2b2bc570-096d-11e7-8c46-7aff367e1a3b.png)


# 參考連結
- GitHub Project Homepage: https://github.com/billxu0521/GA-project
- Google Analytics: https://analytics.google.com/analytics/web/#realtime/rt-overview/a89833109w133278856p137301362/
- 臺北旅遊網: https://www.travel.taipei/
- Google Chrome: https://www.google.com.tw/intl/zh-TW/chrome/
- (Chrome Extension) User JavaScript and CSS: https://chrome.google.com/webstore/detail/user-javascript-and-css/nbhcbdghjpllgmfilhnhkllmkecfmpld?utm_source=chrome-app-launcher-info-dialog
- ga_inject.js: http://billxu0521.github.io/GA-project/ga_inject.js
- event_type_code 事件編號記錄： https://docs.google.com/spreadsheets/d/1MtMtw9lKLDTUzfBd6Ld0fAe_FGe5u-Mlkh5WfZiH5qM/edit

# Code Reference
- 變數名稱規範： 區域變數或函數前面要加底線，字與字中間用底線隔開，例如「_var」；固定的常數要全部大寫，字與字之間用底線隔開，例如「MOUSE_CONFIG」
- 不要直接用function宣告函數，應用 var _func = function () { /*script*/ }; 宣告函數。注意最後的「;」