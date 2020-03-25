/**
 * @author BillXu
 * https://billxu0521.github.io/GA-project/ga_inject_lib.js
 */

/**
 * 預設GA_TRACE_CODE
 * @type String
 */
if (typeof(GA_TRACE_CODE) === "undefined") {
    GA_TRACE_CODE = "UA-89833109-1";
}

/**
 * 使用者ID的欄位 "dimension1"
 * @type String
 */
if (typeof(DIMENSION) === "undefined") {
    DIMENSION = "dimension1";
}

/**
 * 只有停留多久才記錄
 * 單位：秒
 * @type Integer
 */
if (typeof(STAY_SAVE_MIN_INTERVAL) === "undefined") {
    STAY_SAVE_MIN_INTERVAL = 3;
}

/**
 * 加上DEBUG的設定，以方便未來開關
 * @type Boolean
 * @author Pudding 20170203
 */
if (typeof(DEBUG) === "undefined") {
    DEBUG = true;
}

/**
 * 是否要載入額外的CSS
 * 沒事不需要做
 * @type String
 */
if (false) {
    CSS_URL = "";
}

// -------------------------------------------------

/**
 * 初始化載入ga
 * @param {function} _callback
 * @returns {undefined}
 */
window.ga_setup = function (_callback) {
	_console_log("1. 在插入GA之前");

    $.getScript("https://www.google-analytics.com/analytics.js", function () {
		_console_log("2. 插入GA了");

        var _user = get_user_id();
        ga('create', GA_TRACE_CODE, {'userId': _user});
        ga('send', 'pageview');
        ga('require', 'displayfeatures');
        ga('set', 'userId', _user); // 使用已登入的 user_id 設定 User-ID。
        ga('set', 'dimension1', _user);

		_console_log("3. GA設定了");

        auto_set_user_id(function () {
			_console_log("4. User ID設定好了");

            /**
             * 初始化載入
             */
            _console_log("Google analytics injected. User: " + _user);

            if (typeof(_callback) === "function") {
                $(function () {
                    setTimeout(function () {
                        setTimeout(function () {
                            //console.log("觸發一次捲動");
                            $(window).scroll();
                        }, 100);
                        _callback();
                    }, 1000);
                });
            }
        });
    });
};

/**
 * 取得使用者ID資料
 *
 * 如果window.name沒有資料，則會回傳anonymous
 * 不然會回傳window.name的資料
 *
 * @returns {window.name|window.get_user_id|DOMString|String}
 */
var get_user_id = function(){
	var _win = window;
	if (typeof(_win.top) === "object") {
		_win = _win.top;
	}

    if (_win.name === null
            || _win.name === undefined
            || _win.name.trim() === ""){
      return "anonymous";
    }
    else {
        return _win.name;
    }
};

/**
 * 取得連線者IP資料
 *
 *
 *
 */
var get_user_ip = function(){
    $.getJSON('http://ipinfo.io', function(data){
        //console.log(data['ip']);
        _console_log("get user ip: " + data['ip']);

        if(data !== null){
            return String(data['ip']);
        }else if(data === null){
            return "no ip";
        }
    });
};

USER_IP = undefined;
window.auto_set_user_id = function(_callback){

	_console_log("3.1. 開始 auto_set_user_id");

    if (get_user_id() === "anonymous") {
		_console_log("3.2. anonymous");

        getJSONP('https://ipinfo.io', function(data){
			_console_log("3.3. https://ipinfo.io");


            USER_IP = String(data['ip']);
            set_user_id(USER_IP);
            _console_log("Set user id in ip: " + USER_IP);
            if (typeof(_callback) === "function") {

				_console_log("3.4. ok");
                _callback();
            }
        });
    }
    else {
        if (typeof(_callback) === "function") {
            _callback();
        }
    }
};

window.getJSONP = function (_url, _callback) {
	$.ajax({
	  url: _url,
	  cache: false,
	  dataType: "jsonp",
	  success: _callback,
	  contentType: "application/json; charset=utf-8",
	  error: function (request, status, error) { alert(status + ", " + error); }
	});
};

if (!String.prototype.trim) {
  String.prototype.trim = function () {
    return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
  };
}


Number.prototype.padLeft = function(base,chr){
    var  len = (String(base || 10).length - String(this).length)+1;
    return len > 0? new Array(len).join(chr || '0')+this : this;
};

var _get_time = function () {
    var d = new Date;
    d = [
        //d.getFullYear(),
        //(d.getMonth()+1).padLeft(),
        //d.getDate().padLeft(),
        d.getHours().padLeft(),
        d.getMinutes().padLeft(),
        d.getSeconds().padLeft()
        ].join('');
    return d;
};

USER_TIMER = 0;

/**
 * 將ID資訊記錄到視窗屬性中
 * @param {String} _customUserId
 */
window.set_user_id = function (_customUserId){

    var date = new Date();
    var mm = date.getMonth() + 1; // getMonth() is zero-based
    var dd = date.getDate();
    // var hh = date.getHours();
    // var minutes = date.getMinutes();
    // var ss = date.getSeconds();

    date = [date.getFullYear(),
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
            // (hh>9 ? '' : '0') + hh,
            // (minutes>9 ? '' : '0') + minutes,
            // (ss>9 ? '' : '0') + ss
           ].join('');

    if (typeof(_customUserId) === "undefined") {
        _customUserId = get_user_id();
    }
    _customUserId = _customUserId.trim();
    _customUserId = _customUserId + "-" + date;

    _console_log("Set user id: " + _customUserId);


    var _win = window;
	if (typeof(_win.top) === "object") {
		_win = _win.top;
	}

    if (_win.name !== _customUserId) {
        //ga("send", "event", "end_exp", _win.name);
    }

    _win.name = _customUserId;

    ga('create', GA_TRACE_CODE, {'userId': _customUserId});
    ga('set', 'userId', _customUserId); // 使用已登入的 user_id 設定 User-ID。
    ga('set', DIMENSION, _customUserId);

    // 改用統一取得header的方式
    var _name_header = _get_element_name();
    //ga("send", "event", "start_exp", _name_header);
    //set_user_timer();

};

window.set_user_id_by_trigger = function (_trigger_selector, _user_id_getter) {
	if ($(_trigger_selector).length > 0) {
		set_user_id(_user_id_getter());
	}
};

window.start_exp = function (_customUserId) {
    return window.set_user_id(_customUserId);
};

var set_user_timer = function () {
    USER_TIMER = (new Date()).getTime();
};

/**
 * 結束本次實驗，重置資訊
 */
window.fin_exp = function (){
    //var _time = (new Date()).getTime() - USER_TIMER;
    //_time = parseInt(_time / 1000, 10);

	var _hash = location.hash;
	if (_hash !== "") {
		_hash = "#" + _hash;
	}

    var _name = get_user_id() + ": " + _get_time() + ": " + window.location.pathname + window.location.search + _hash;

	var _win = window;
	if (typeof(_win.top) === "object") {
		_win = _win.top;
	}

    _win.name = '';
    //_console_log('end_exp: ' + _name + ", sec: " + _time);
    _console_log('end_exp: ' + _name);

    //ga("send", "event", "end_exp", _name);
    auto_set_user_id();
};

window.end_exp = function () {
    return window.fin_exp();
};


// -------------------------------------------------------------

/**
 * 偵測滑鼠移上去的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String|Function} _name
 */
window.ga_mouse_over_event = function (_selector, _event_type, _name) {
    var _event_key = 'mouse_over';
    $(_selector).mouseover(function () {
        var _name_data = _get_element_name($(this), _event_type, _name);

        _console_log([_event_type, _name_data, _event_key]);
        ga("send", "event", _event_type, _name_data, _event_key);
    });
};

/**
 * 滑鼠移入跟移除的功能
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_over_out_event = function(_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_over_out_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    if (_selector_length_caller(_selector, window.ga_mouse_over_out_event, _event_type, _name) === false) {
        return;
    }

    var _id = GA_TIMER.length;
    GA_TIMER.push(false);
    var _event_key = "mouse_over_out";
    var _classname = _get_event_classname(_event_key, _event_type);

    var _obj = $(_selector + ":not(." + _classname + ")");
    _obj.mouseover(function() {
        var _name_data = _get_element_name(_obj, _selector, _name);
        GA_TIMER[_id] = (new Date()).getTime();
        _console_log([_event_type, _event_key + ": start", _name_data, GA_TIMER[_id]]);
    });

    _obj.mouseout(function() {
        var _name_data = _get_element_name(_obj, _selector, _name);
        //_name_data = window.location.pathname + ": " + _name_data;
        var _interval = ((new Date()).getTime() - GA_TIMER[_id])/1000;
        _interval = parseInt(_interval, 10);
        if (_interval > STAY_SAVE_MIN_INTERVAL) {
            _console_log([_event_type, _event_key +  + ": end", _name_data, _interval, "記錄"]);
            ga("send", "event", _event_type, _name_data, _event_key, _interval);
        }
        else {
            _console_log([_event_type, _event_key + ": end", _name_data, _interval, "不記錄"]);
        }
        GA_TIMER[_id] = false;
    });
};

var _get_event_classname = function (_event_key, _event_type) {
    var _classname = _event_key + _event_type;
    _classname = _classname.split(":").join("");
    _classname = _classname.split(";").join("");
    _classname = _classname.split(" ").join("");
    return _classname;
};

/**
 * 滑鼠抓起跟放開的功能
 * https://www.w3schools.com/jsref/event_ondrag.asp
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_drag_event = function(_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_drag_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    if (_selector_length_caller(_selector, window.ga_mouse_drag_event, _event_type, _name) === false) {
        return;
    }

    var _id = GA_TIMER.length;
    GA_TIMER.push(false);
    var _event_key = "drag";
    var _classname = _get_event_classname(_event_key, _event_type);

    var _obj = $(_selector + ":not(." + _classname + ")");
    _obj.on("dragstart", function() {
        var _name_data = _get_element_name(_obj, _selector, _name);
        GA_TIMER[_id] = (new Date()).getTime();
        _console_log([_event_type, _event_key + ": start", _name_data, GA_TIMER[_id]]);
    });

    _obj.on("dragend", function() {
        var _name_data = _get_element_name(_obj, _selector, _name);
        var _interval = (new Date()).getTime() - GA_TIMER[_id];
        _interval = parseInt(_interval/1000, 10);
        _console_log([_event_type, _event_key +  + ": end", _name_data, _interval, "記錄"]);
        ga("send", "event", _event_type, _name_data, _event_key, _interval);
        GA_TIMER[_id] = false;
    });
};

/**
 * 偵測滑鼠點擊的事件
 * @param {String} _selector CSS的選取器
 * @param {String} _event_type GA event type (field name)
 * @param {String} _name GA other information
 */
window.ga_mouse_click_event = function (_selector, _event_type, _name) {

    try {
        if ($(_selector).length === 0) {
            setTimeout(function () {
                window.ga_mouse_click_event(_selector, _event_type, _name);
            }, 1000);
            return;
        }
    }
    catch (e) {
        console.log("ERROR SELECTOR: " + _selector);
    }

    var _event_key = 'mouse_click';
    var _classname = _get_event_classname(_event_key, _event_type);

    $(_selector + ":not(." + _classname + ")").click(function () {
        //var _name_data = _get_element_name(this, _selector, _name);
        //_console_log([_event_type, _name_data, _event_key]);
        //ga("send", "event", _event_type, _name_data, _event_key);
        ga_mouse_click_event_trigger(this, _selector, _name, _event_type, _event_key);
    }).addClass(_classname);
};

window.ga_mouse_click_event_trigger = function (_obj, _selector, _name, _event_type, _event_key) {
    var _name_data = _get_element_name(_obj, _selector, _name);
    _console_log([_event_type, _name_data, _event_key]);
    ga("send", "event", _event_type, _name_data, _event_key);
};

/**
 * 偵測滑鼠按下的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_down_event = function (_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_down_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    var _event_key = 'mouse_down';
    var _classname = _get_event_classname(_event_key, _event_type);

    $(_selector + ":not(." + _classname + ")").mousedown(function () {
        var _name_data = _get_element_name(this, _selector, _name);
        _console_log([_event_type, _name_data, _event_key]);
        ga("send", "event", _event_type, _name_data, _event_key);
    }).addClass(_classname);
};

/**
 * 偵測滑鼠按下的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_send_event = function (_event_type, _name) {
    var _event_key = 'send_event';
    var _name_data = _get_element_name(document, "", _name);
    _console_log([_event_type, _name_data, _event_key]);
    ga("send", "event", _event_type, _name_data, _event_key);
};

/**
 * 偵測滑鼠按下的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_touch_event = function (_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_touch_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    var _event_key = 'touch';
    var _classname = _get_event_classname(_event_key, _event_type);

      $(_selector + ":not(." + _classname + ")").on("touchstart",function () {
          var _name_data = _get_element_name(this, _selector, _name);

          _console_log([_event_type, _name_data, _event_key]);
          ga("send", "event", _event_type, _name_data, _event_key);
      }).addClass(_classname);
};

/**
 * 偵測表單改變的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_input_change_event = function (_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_input_change_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    var _event_key = 'input_change';
    var _classname = _get_event_classname(_event_key, _event_type);

    $(_selector + ":not(." + _classname + ")").change(function () {
        var _input_name = $(this).attr("name");
        var _name_data = _get_element_name(this, _selector, _input_name + "=" + $(this).val());
        _console_log([_event_type, _name_data, _event_key]);
        ga("send", "event", _event_type, _name_data, _event_key);
    }).addClass(_classname);
};

/**
 * 偵測表單改變的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_input_keydown_enter_event = function (_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_input_keydown_enter_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    var _event_key = 'input_keydown_enter';
    var _classname = _get_event_classname(_event_key, _event_type);

    $(_selector + ":not(." + _classname + ")").keydown(function (_e) {
        //console.log([$(this).prop("tagName").toLowerCase(), _e.keyCode]);
        if ($(this).prop("tagName").toLowerCase() === "input" && _e.keyCode === 13) {
            var _input_name = $(this).attr("name");
            var _name_data = _get_element_name(this, _selector, _input_name + "=" + $(this).val());

            _console_log([_event_type, _name_data, _event_key]);
            ga("send", "event", _event_type, _name_data, _event_key);
        }
    }).addClass(_classname);
};

/**
 * 偵測表單送出的事件
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_submit_event = function (_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_submit_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    if (_selector_length_caller(_selector, window.ga_submit_event, _event_type, _name) === false) {
        return;
    }
    var _event_key = "form_submit";
    var _classname = _get_event_classname(_event_key, _event_type);

    var _obj = $(_selector);
    var _tag_name = _obj.prop("tagName").toLowerCase();

    if (_tag_name !== "form") {
        var _form = _obj.parents("form:first");
        if (_form.length === 0) {
            return;
        }
        else {
            _obj = _form;
        }
    }

    window.DENY_SUBMIT = true;
    _obj.submit(function () {
        if (window.DENY_SUBMIT === false) {
          return;
        }
        // 蒐集form裡面的資料
        if (_name === undefined) {
             var _ary = _obj.serializeArray();
             var _data = {};
             for (var _i = 0; _i < _ary.length; _i++) {
                 var _name = _ary[_i].name;
                 if (_name === "__RequestVerificationToken") {
                     continue;
                 }
                 var _value = _ary[_i].value;
                 _data[_name] = _value;
             }
            _name = JSON.stringify(_data).trim();

            if (_name === "") {
                _name = undefined;
            }
        }

        var _name_data = _get_element_name(this, _selector, _name);

        _console_log([_event_type, _name_data, _event_key]);
        ga("send", "event", _event_type, _name_data, _event_key);
        var _form = $(this);
        if (_form.prop("tagName").toLowerCase() !== "form") {
            _form = _form.parents("form:first");
        }
        setTimeout(function () {
            window.DENY_SUBMIT = false;
            _form.submit();
        }, 1000);
        //return false ;
    });
};



/**
 * 偵測畫面捲動的事件
 * 可偵測物件是否出現在畫面中，並計算時間
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_scroll_in_out_event = function(_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_scroll_in_out_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    if (_selector_length_caller(_selector, window.ga_mouse_scroll_in_out_event, _event_type, _name) === false) {
        return;
    }

    var _event_key = 'scroll_in_out';
    var _classname = _get_event_classname(_event_key, _event_type);

    var _id = GA_TIMER.length;
    GA_TIMER.push(false);

    var _window = $(window);

    // 捲動時偵測
    _window.scroll(function() {
        //console.log(["觸發", _selector]);
        var _obj = $(_selector);
        var _name_data = _get_element_name(_obj, _selector, _name);

        var _obj_top = _obj.offset().top;
        var _obj_bottom = _obj_top + _obj.height();
        var _scroll_top_border = _window.scrollTop();
        var _scroll_bottom_border = _scroll_top_border + _window.height();

        var _is_obj_under_scorll_top = (_obj_top > _scroll_top_border);
        var _is_obj_above_scorll_bottom = (_obj_bottom < _scroll_bottom_border);

        var _is_obj_display_in_window = (_is_obj_under_scorll_top && _is_obj_above_scorll_bottom);

        if (_is_obj_display_in_window === false && GA_TIMER[_id] === false) {
            // 沒事
        }
        else if (_is_obj_display_in_window === true && GA_TIMER[_id] === false) {
            // 進入了，開始記錄事件
            GA_TIMER[_id] = (new Date()).getTime();
            _console_log([_event_type, _event_key + ": start", _name_data, GA_TIMER[_id]]);
        }
        else if (_is_obj_display_in_window === true && GA_TIMER[_id] !== false) {
            // 沒事
        }
        else if (_is_obj_display_in_window === false && GA_TIMER[_id] !== false) {
            // 離開了
            var _interval = parseInt(((new Date()).getTime() - GA_TIMER[_id])/1000, 10);
            if (_interval > STAY_SAVE_MIN_INTERVAL) {
                _console_log([_event_type, _event_key + ": end", _name_data, _interval, "記錄"]);
                ga("send", "event", _event_type, _name_data, "scroll_in", _interval);
            }
            else {
                _console_log([_event_type, _event_key + ": end", _name_data, _interval, "不記錄"]);
            }
            GA_TIMER[_id] = false;
        }
    });
};

/**
 * 偵測畫面捲動的事件
 * 可偵測物件是否出現在畫面中，並計算時間
 * @param {String} _selector
 * @param {String} _event_type
 * @param {String} _name
 */
window.ga_mouse_scroll_in_event = function(_selector, _event_type, _name) {
    if ($(_selector).length === 0) {
        setTimeout(function () {
            window.ga_mouse_scroll_in_event(_selector, _event_type, _name);
        }, 1000);
        return;
    }

    if (_selector_length_caller(_selector, window.ga_mouse_scroll_in_event, _event_type, _name) === false) {
        return;
    }

    var _event_key = 'scroll_in_out';
    var _classname = _get_event_classname(_event_key, _event_type);

    var _id = GA_TIMER.length;
    GA_TIMER.push(false);

    var _window = $(window);

    var _check_is_obj_display_in_window = function (_obj) {
        var _obj_top = _obj.offset().top;
        var _obj_bottom = _obj_top + _obj.height();
        var _scroll_top_border = _window.scrollTop();
        var _scroll_bottom_border = _scroll_top_border + _window.height();

        var _is_obj_under_scorll_top = (_obj_top > _scroll_top_border);
        var _is_obj_above_scorll_bottom = (_obj_bottom < _scroll_bottom_border);

        var _is_obj_display_in_window = (_is_obj_under_scorll_top && _is_obj_above_scorll_bottom);
        return _is_obj_display_in_window;
    };

    // 捲動時偵測
    _window.scroll(function() {
        //console.log(["觸發", _selector]);
        var _obj = $(_selector);
        var _name_data = _get_element_name(_obj, _selector, _name);

        var _is_obj_display_in_window = _check_is_obj_display_in_window(_obj);

        if (_is_obj_display_in_window === false && GA_TIMER[_id] === false) {
            // 沒事
        }
        else if (_is_obj_display_in_window === true && GA_TIMER[_id] === false) {
            // 進入了，開始記錄事件
            GA_TIMER[_id] = (new Date()).getTime();
            _console_log([_event_type, _event_key + ": start", _name_data, GA_TIMER[_id]]);

            setTimeout(function () {
                if (_check_is_obj_display_in_window(_obj)) {
                    var _interval = parseInt(((new Date()).getTime() - GA_TIMER[_id])/1000, 10);
                    _console_log([_event_type, _event_key + ": end", _name_data, _interval, "記錄"]);
                    ga("send", "event", _event_type, _name_data, "scroll_in", _interval);
                }
                GA_TIMER[_id] = false;
            }, STAY_SAVE_MIN_INTERVAL * 1000);
        }
        /*
        else if (_is_obj_display_in_window === true && GA_TIMER[_id] !== false) {
            // 沒事
        }
        else if (_is_obj_display_in_window === false && GA_TIMER[_id] !== false) {
            // 離開了
            var _interval = parseInt(((new Date()).getTime() - GA_TIMER[_id])/1000, 10);
            if (_interval > STAY_SAVE_MIN_INTERVAL) {
                _console_log([_event_type, _event_key + ": end", _name_data, _interval, "記錄"]);
                ga("send", "event", _event_type, _name_data, "scroll_in", _interval);
            }
            else {
                _console_log([_event_type, _event_key + ": end", _name_data, _interval, "不記錄"]);
            }
            GA_TIMER[_id] = false;
        }
        */
    });
};


// ------------------------------------

/**
 * 讀取CSS
 * @param {String} _css_url
 */
var _load_css = function (_css_url) {
    //console.log([typeof(_css_url), _css_url ]);
    if (typeof(_css_url) !== "string" || _css_url.trim() === "") {
        return;
    }
    else {
        _css_url = _css_url.trim();
    }
    if (DEBUG === true){
        //console.log("include CSS: " + _css_url);
    }
    var head  = document.getElementsByTagName('head')[0];
    var link  = document.createElement('link');
    //link.id   = cssId;
    link.rel  = "stylesheet";
    link.type = "text/css";
    link.href = _css_url + "?_=" + (new Date()).getTime();
    //link.media = 'all';

    head.appendChild(link);
};

if (typeof(CSS_URL) === "string") {
	_load_css(CSS_URL);
}


/**
 * 取得元素的可讀取元素
 * @param {String} _ele
 * @param {String} _event_type
 * @param {String} _name
 * @returns {String|get_element_name._name}
 *
 * window.location.pathname + ": " +
 */
var _get_element_name = function (_ele, _event_type, _name) {

	var _hash = location.hash;
	if (_hash !== "") {
		_hash = "#" + _hash;
	}

    var _name_header = get_user_id() + ": " + USER_IP + ": " + _get_time() + ": " + window.location.pathname + window.location.search + _hash;

    if (_ele !== undefined) {
        _name_header = _name_header + ": ";
    }
    else {
        return _name_header;
    }

    _ele = $(_ele);
    if (typeof(_name) === "string") {
        return _name_header + _name;
    }
    else if (typeof(_name) === "function") {
        return _name_header + _name(_ele);
    }

    try {
        if(_ele.attr("title")){
          _name = _ele.attr("title");
        } else if (_ele.text()) {
          _name = _ele.text();
        } else if (_ele.attr("alt")){
          _name = _ele.attr("alt");
        } else if (_ele.attr("src")){
          _name = _ele.attr("src");
        } else if (_ele.attr("data-src")){
          _name = _ele.attr("data-src");
        } else if (_ele.attr("className")){
          _name = _ele.attr("className");
        } else{
          _name = _event_type;
        }
    }
    catch (e) {}

    if (typeof(_name) === "string") {
        _name = _name.trim();

        while (_name.indexOf("  ") > -1) {
            _name = _name.split("  ").join(" ");
        }
    }

    _name = _name_header + _name;

    return _name;
};

/**
 * 偵測物件數量
 * 0個不執行
 * 2個以上，變成for loop執行
 * @param {type} _selector
 * @param {type} _callback
 * @returns {Boolean}
 */
var _selector_length_caller = function (_selector, _function, _event_type, _name) {
    var _obj_list = $(_selector);
    if (_obj_list.length === 0) {
        return false;
    }
    else if (_obj_list.length > 1) {
        // 如果要鎖定的物件很多個，應該用這種方式來避免重複
        if (typeof(_function) === "function") {
            for (var _i = 0; _i < _obj_list.length; _i++) {
                _function(_obj_list.eq(_i), _event_type, _name);
            }
        }
        return false;
    }
    return true;
};

/**
 * 顯示偵錯訊息: 簡易資訊
 * @param {String} _message
 */
var _console_log = function (_message) {
    if (DEBUG === true){
        if (typeof(_message) === "object" && typeof(_message.length) === "number") {
            _message = _message.join(", ");
        }
        CONSOLE_LOG.push(_message);

        setTimeout(function () {
            if (CONSOLE_LOG.length > 0) {
                var _m = CONSOLE_LOG.join("\n");
                var _d = new Date();
                _m = "[" + _d.getHours() + ":" + _d.getMinutes() + ":"+ _d.getSeconds() + "]\n" + _m;
                //console.log(_m);
                CONSOLE_LOG = [];
            }
        }, 1);
        //console.log(_message);
    }
};

window.ga_display_timer = function (_style) {
    if ($("#ga_display_timer").length > 0) {
        return;
    }

    var _timer = $('<div id="ga_display_timer"></div>').appendTo('body');
    _timer.css({
        "position": "fixed",
        //"bottom": 0,
        //"left": 0,
        "background-color": "#FFF",
        "font-size": "10px",
        "opacity": 0.5,
        "color":  "#333",
        "z-index": 999999,
        "padding": "1px",
        "line-height": "12px"
    });

    if (_style === undefined || _style === 9) {
        _timer.css({
            "top": 0,
            "right": 0,
            "border-radius": "0 0 0 5px"
        });
    }
    else if (_style === 3) {
        _timer.css({
            "bottom": 0,
            "right": 0,
            "border-radius": "5px 0 0 0"
        });
    }
    else if (_style === 1) {
        _timer.css({
            "bottom": 0,
            "left": 0,
            "border-radius": "0 5px 0 0"
        });
    }
    else if (_style === 1) {
        _timer.css({
            "top": 0,
            "left": 0,
            "border-radius": "0 5px 0 0"
        });
    }

    setInterval(function () {
        var d = new Date;
        d = [
            d.getMinutes().padLeft(),
            d.getSeconds().padLeft()
        ].join(':');
        _timer.text(d);
    }, 1000);
};

window.enable_screen_recorder_link = function () {
    $("body").keydown(function (_e) {
        if (_e.keyCode === 82) {
            window.open("https://www.apowersoft.tw/free-online-screen-recorder", "apowersoft");
        }
    });
};

window.enable_screen_recorder = function () {

    if ($("#screen_recorder").length > 0) {
        return;
    }

    var _screen_recorder = $('<div id="screen_recorder" class="start-screen-recording-big"><div><div class="rec-dot"></div><span>網頁錄影開始</span></div></div>');
    _screen_recorder.css({
        "position": "fixed",
        "height": "72px",
        "top": "calc(50vh - 36px)",
        "width": "300px",
        "left": "calc(50vw - 150px)"
    });
    _screen_recorder.click(function () {
        $(this).hide();
    });
    _screen_recorder.hide();
    _screen_recorder.appendTo($("body"));

    $.getScript("//api.apowersoft.com/screen-recorder?lang=tw", function () {
        $("body").keydown(function (_e) {
            if (_e.keyCode === 82) {
                _screen_recorder.find(".apower-powerby").hide();
                _screen_recorder.show();
            }
        });
    });
};

window.getCookie = function(cname) {
	var name = cname + "=";
	var decodedCookie;
	//var decodedCookie = decodeURIComponent(document.cookie);
	try {
		decodedCookie = decodeURIComponent(document.cookie);
	}
	catch (e) {
		decodedCookie = document.cookie;
	}

	var ca = decodedCookie.split(';');
	for(var i = 0; i <ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			var result = c.substring(name.length, c.length);
			result = decodeURIComponent(result);
			// encodeURI / decodeURI
			// encodeURIComponent / dece..
			// escape / unescape
			return result;
		}
	}
	return "";
};

// -----------------------------------------------

CONSOLE_LOG = [];

/**
 * 顯示偵錯訊息: 詳細資訊
 * @param {String} _message
 */
var _console_trace = function (_message) {
    if (DEBUG === true){
        console.trace(_message);
    }
};

/**
 * 搭配 各種技術器使用
 * @type Array
 */
var GA_TIMER = [];
