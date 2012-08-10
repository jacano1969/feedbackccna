<script type="text/javascript">

function unclick(id) {

    id += "";

    if (document.getElementById("user".concat(id)).checked) {
        document.getElementById("lab_stars".concat(id)).hidden = true;
        document.getElementById("prez_stars".concat(id)).hidden = true;
        document.getElementById("Lab".concat(id)).value = 0;
        document.getElementById("Prez".concat(id)).value = 0;
        window["s_prez"+id].setValue(0, false);
        window["s_lab"+id].setValue(0, false);
    } else {
        document.getElementById("lab_stars".concat(id)).hidden = false;
        document.getElementById("prez_stars".concat(id)).hidden = false;
    }

    return false;

}

function checkAll(name) {

    var str = new Array(<?php echo implode(",", $list); ?>);

    if (document.getElementById("all_"+name).innerHTML == "Check All") {

        document.getElementById("all_"+name).innerHTML = "Uncheck All";
            new_val = 1;

    } else {

        document.getElementById("all_"+name).innerHTML = "Check All";
            new_val = 0;

    }

    if (name == "prez") {

        for (i = 0; i < str.length; i ++) {

            if (document.getElementById("user"+str[i]).checked == false) {

                document.getElementById("Prez"+str[i]).value = new_val;
                window["s_prez"+str[i]].setValue(new_val, false);

            }

        }

    } else {

        if (name == "lab") {

            for (i = 0; i < str.length; i ++) {

                if (document.getElementById("user"+str[i]).checked == false) {

                    document.getElementById("Lab"+str[i]).value = new_val;
                    window["s_lab"+str[i]].setValue(new_val, false);

                }

            }

        } else {

            if (name == "abs") {

                document.getElementById("all_prez").innerHTML == "Check All";
                document.getElementById("all_lab").innerHTML == "Check All";

                for (i = 0; i < str.length; i ++) {

                    document.getElementById("user"+str[i]).checked = new_val;
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
        unclick(str[i]);

    }

}

</script>

