function SwitchCourseMenu(obj){
    if(document.getElementById){
    var el = document.getElementById(obj);
    var ar = document.getElementById("coursemenudiv").getElementsByTagName("span"); //DynamicDrive.com change
        if(el.style.display != "block"){ //DynamicDrive.com change
            /*
            for (var i=0; i<ar.length; i++){
                if (ar[i].className=="submenu"){ //DynamicDrive.com change
                    ar[i].style.display = "none";
                }
            }
            */
            el.style.display = "block";
        }else{
            el.style.display = "none";
        }
    }
}