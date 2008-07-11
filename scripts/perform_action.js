/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var actionURL = './perform_action.php';
var xmlHttpPerformAction = createXmlHttpRequestObject();
var gamePhase = 0;
var gameTurn = 0;
var debugMode = true;

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
function performAction(user_id, user_hash, game_id, action, action_target) {
    if(xmlHttpPerformAction) {
        try {
            if(xmlHttpPerformAction.readyState == 4 || 
               xmlHttpPerformAction.readyState == 0) {
                var params = "";
                if(cache.length > 0) {
                    params = cache.shift();
                } else {
                    var user = document.getElementById("user_id").value;
                    var userHash = document.getElementById("user_hash").value;
                    var gameId = document.getElementById("game_id").value;
                    params = "game_id=" + gameId + 
                             "&game_turn=" + gameTurn + 
                             "&game_phase=" + gamePhase +
                             "&user_id=" + user + 
                             "&user_hash=" + userHash;
                }
                //xmlHttpPerformAction.open("POST", actionURL, true);
                xmlHttpPerformAction.open("GET", actionURL+"?"+params, true);
                xmlHttpPerformAction.setRequestHeader("Content-Type",
                                                       "application/x-www-form-urlencoded");
                xmlHttpPerformAction.onreadystatechange = handleReceivingAction;
                xmlHttpPerformAction.send(); //params goes here
            } else {
                setTimeout("performAction();", updateInterval);
            }
        } catch (e) {
            displayError(e.toString());
        }
    }
}

function handleReceivingAction() {
    if(xmlHttpPerformAction.readyState == 4) {
        if(xmlHttpPerformAction.status == 200) {
            try {
                //readInformation();
            } catch(e) {
                displayError(e.toString()); //Status is 200...
            }
        } else {
            displayError(xmlHttpPerformAction.statusText);
        }
    }
}

//function readInformation() {
//    var response = xmlHttpPerformAction.responseText;
//    if(response.indexOf("ERRNO") >= 0 || response.indexOf("error:") >= 0 ||
//       response.length == 0) {
//        throw(response.length == 0? "Void server response." : response);
//    }
//    response = xmlHttpPerformAction.responseXML.documentElement;
//    if(response.getElementsByTagName("phase").length > 0) {
//        var tmpGamePhase = response.getElementsByTagName("phase")[0].firstChild.data.toString();
//        var tmpGameTurn = response.getElementsByTagName("turn")[0].firstChild.data.toString();
//        if(tmpGamePhase != gamePhase || tmpGameTurn != gameTurn) {
//            var gameChatHTML = document.getElementById("chat_channel");
//            gameChatHTML.innerHTML = "";
//            gameChatHTML.innerHTML = response.getElementsByTagName("channel")[0].firstChild.data.toString() + " Channel";
//            var gamePhaseHTML = document.getElementById("game_phase");
//            var gameTurnHTML = document.getElementById("game_turn");
//            gamePhaseHTML.innerHTML = tmpGamePhase;
//            gameTurnHTML.innerHTML = tmpGameTurn;
//            gamePhase = tmpGamePhase;
//            gameTurn = tmpGameTurn;
//            var bannerMessage = "Fail";
//            var bannerMessage = response.getElementsByTagName("banner");
//            if(bannerMessage.length > 0) {
//                bannerMessage = bannerMessage[0].firstChild.data.toString();
//            } else {
//                bannerMessage = "";
//            }
//            playerArray = response.getElementsByTagName("player_list")[0].getElementsByTagName("player");
//            displayPlayers(playerArray, gamePhase, bannerMessage);
//        }
//    }
//    setTimeout("performAction();", updateInterval);
//}
