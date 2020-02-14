// Check the sum of all weights to be 100%, otherwise no saving
// by YCJ using jQuery
$(function () {
	var valcheck=function() {
	  var total = 0;
	  
    $("[id^='weight']").each(function () {
        var v = parseFloat(this.value);
        if (!isNaN(v)) {
            total += Math.round(10*v)/10;
        } else {
            this.value = '';
        }
    });
    
    $("font.specialHero").remove();   // Reset the display of sum
    if(total == 100) {
    	$(".column-weight.level1.c1.lastcol").append('<font size=5 class=specialHero><b>'+total+' %</b></font>');   // Display the sum of all weights
    	$("input.advanced").show();
    } else {
    	$(".column-weight.level1.c1.lastcol").append('<font size=5 class=specialHero><b>'+total.toFixed(1)+' %  </b><font size=3 color=red>Be 100%</font></font>');   // Display the sum of all weights
    	$("input.advanced").hide();     // disable the button
    };
  };
  
	   valcheck();
	
     $("input[id^='weight']").change(valcheck);
     
		 $(document).on('keyup keypress', 'form input[type="text"]', function(e) {
			  if(e.which == 13) {
			  	valcheck();
			  	
			    e.preventDefault();
			    return false;
			  }
		 });
});
