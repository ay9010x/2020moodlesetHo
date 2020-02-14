/**
 * Scroll to center
 * @author Pulipuli Chen 20180508
 */
//jQuery(function () {
scroll_to_center = function () {
    var _top = $(document).height() / 2;
    _top = _top - $(window).height() / 2;
    //_top = _top - 140;
    $(document).scrollTop(_top);


    var _left = $(document).width() / 2;
    _left = _left - $(window).width() / 2;
    $(document).scrollLeft(_left);
};
//});