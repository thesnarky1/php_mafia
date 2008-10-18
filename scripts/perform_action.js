/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var actionURL = './perform_action.php';
var xmlHttpPerformAction = createXmlHttpRequestObject();
var debugMode = true;
var targetId = null;
var actionId = null;
var actionCache = new Array();
var myTimeout = null;


function createXmlHttpRequestObject() {
    var xmlHttp;
    try {
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                        "MSXML2.XMLHTTP.5.0",
                                        "MSXML2.XMLHTTP.4.0",
                                        "MSXML2.XMLHTTP.3.0",
                                        "MSXML2.XMLHTTP.0",
                                        "Microsoft.XMLHTTP");
        for(var i = 0; i<XmlHttpVersions.length && !xmlHttp; i++) {
            try {
                xmlHttp = new ActiveXObject(XmlHttpVersions[i]);
            } catch (e) {}
        }
    }
    if(!xmlHttp) {
    } else {
        return xmlHttp;
    }
}

/*
 * user_id = logged in user
 * user_hash = logged in user's hash
 * game_id = game_id
 * action = type of action.
 */
function performAction(actionId, targetId) {
    if(xmlHttpPerformAction) {
        try {
            if(xmlHttpPerformAction.readyState == 4 || 
               xmlHttpPerformAction.readyState == 0) {
                var actionParams = "";
                if(actionCache.length > 0) {
                    actionParams = actionCache.shift();
                } else {
                    user = document.getElementById("user_id").value;
                    userHash = document.getElementById("user_hash").value;
                    gameId = document.getElementById("game_id").value;
                    actionParams = "game_id=" + gameId + 
                             "&user_id=" + user + 
                             "&action_id=" + actionId + 
                             "&target_id=" + targetId +
                             "&user_hash=" + userHash;
                }
                //xmlHttpPerformAction.open("POST", actionURL, true);
                xmlHttpPerformAction.open("GET", actionURL+"?"+actionParams, true);
                xmlHttpPerformAction.setRequestHeader("Content-Type",
                                                       "application/x-www-form-urlencoded");
                xmlHttpPerformAction.onreadystatechange = handleReceivingAction;
                xmlHttpPerformAction.send(); //actionParams goes here
            } else {
                setTimeout("performAction();", updateInterval);
            }
        } catch (e) {
            displayError(e.toString());
        }
    }
}

function show_action_message(str) {
    var action_message = document.getElementById("action_message");
    action_message.innerHTML = str;
    action_message.style.display = "block";
}

function hide_action_message() {
    var action_message = document.getElementById("action_message");
    action_message.style.display = "none";
    myTimeout = null;
}

function handleReceivingAction() {
    if(xmlHttpPerformAction.readyState == 4) {
        if(xmlHttpPerformAction.status == 200) {
            try {
                if(myTimeout) {
                    clearTimeout(myTimeout);
                    myTimeout = null;
                }
                show_action_message(xmlHttpPerformAction.responseText);
                myTimeout = setTimeout("hide_action_message()", 5000);
            } catch(e) {
                displayError(e.toString());
            }
        } else {
            displayError(xmlHttpPerformAction.statusText);
        }
    }
}
