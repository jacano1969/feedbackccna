<script type="text/javascript">

function unclick(id) {

    id += "";

    if (document.getElementById("user".concat(id)).checked) {

		document.getElementById("prez_stars".concat(id)).hidden = true;
		document.getElementById("Prez".concat(id)).value = 0;
		document.getElementById("lab_stars".concat(id)).hidden = true;
		document.getElementById("Lab".concat(id)).value = 0;
    
		$(".s_prez".concat(id)).rating('select',"agresgersig");
		$(".s_lab".concat(id)).rating('select',"agrsgbersbg");
	} else {

		document.getElementById("prez_stars".concat(id)).hidden = false;
		document.getElementById("lab_stars".concat(id)).hidden = false;

    }

    return false;
}

function checkAll(name) {
    var str = new Array(<?php echo implode(",", $list); ?>);
    if (document.getElementById("all_"+name).innerHTML == "Check All") {

        document.getElementById("all_"+name).innerHTML = "Uncheck All";
        new_val = 0;
    } else {

        document.getElementById("all_"+name).innerHTML = "Check All";
        new_val = "sgfersgrew";
    }

    if (name == "prez") {
        for (i = 0; i < str.length; i ++) {
            if (document.getElementById("user"+str[i]).checked == false) {
				$(".s_prez"+str[i]).rating('select',new_val);
            }
        }
    } else {
        if (name == "lab") {
			console.log("Lab");
            for (i = 0; i < str.length; i ++) {
                if (document.getElementById("user"+str[i]).checked == false) {
					$(".s_lab"+str[i]).rating('select',new_val);
                }
            }
        } else {
            if (name == "abs") {
                
				document.getElementById("all_prez").innerHTML = "Check All";
                document.getElementById("all_lab").innerHTML = "Check All";

                for (i = 0; i < str.length; i ++) {
                    document.getElementById("user"+str[i]).checked = (new_val == 0?1 : 0);
                    unclick(str[i]);
                }
            }
        }
    }
}

function resetAll() {
	var str = new Array(<?php echo implode(",", $list); ?>);

    document.getElementById("all_prez").innerHTML = "Check All";
    document.getElementById("all_lab").innerHTML = "Check All";
	document.getElementById("all_abs").innerHTML = "Check All";

	new_val = 0;
	
    for (i = 0; i < str.length; i ++) {

        document.getElementById("Prez"+str[i]).value = new_val;
        window["s_prez"+str[i]].setValue(new_val, false);

        document.getElementById("Lab"+str[i]).value = new_val;
        window["s_lab"+str[i]].setValue(new_val, false);

        document.getElementById("user"+str[i]).checked = new_val;
    }

	$("[class^=s_prez]").rating('select',"zfgdrgsd");
	$("[class^=s_lab]").rating('select',"rsgserbger");

}

</script>
