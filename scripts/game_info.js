/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var gameInfoURL = './game_information.php';
var xmlHttpGetInformation = createXmlHttpRequestObject();
var updateInterval = 1000;
var cache = new Array();
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

function init_info() {
    requestGameInformation();
}

function requestGameInformation() {
    if(xmlHttpGetInformation) {
        try {
            if(xmlHttpGetInformation.readyState == 4 || 
               xmlHttpGetInformation.readyState == 0) {
                var params = "";
                if(cache.length > 0) {
                    params = cache.shift();
                } else {
                    var game_id = document.getElementById("game_id").value;
                    params = "game_id=" + game_id + 
                             "&game_turn=" + gameTurn + 
                             "&game_phase=" + gamePhase;
                }
                //xmlHttpGetInformation.open("POST", gameInfoURL, true);
                xmlHttpGetInformation.open("GET", gameInfoURL+"?"+params, true);
                xmlHttpGetInformation.setRequestHeader("Content-Type",
                                                       "application/x-www-form-urlencoded");
                xmlHttpGetInformation.onreadystatechange = handleReceivingInformation;
                xmlHttpGetInformation.send(); //params goes here
            } else {
                setTimeout("requestGameInformation();", updateInterval);
            }
        } catch (e) {
            displayError(e.toString());
        }
    }
}

function handleReceivingInformation() {
    if(xmlHttpGetInformation.readyState == 4) {
        if(xmlHttpGetInformation.status == 200) {
            try {
                readInformation();
            } catch(e) {
                displayError(e.toString()); //Status is 200...
            }
        } else {
            displayError(xmlHttpGetInformation.statusText);
        }
    }
}

function readInformation() {
    var response = xmlHttpGetInformation.responseText;
    if(response.indexOf("ERRNO") >= 0 || response.indexOf("error:") >= 0 ||
       response.length == 0) {
        throw(response.length == 0? "Void server response." : response);
    }
    response = xmlHttpGetInformation.responseXML.documentElement;
    if(response.getElementsByTagName("phase").length > 0) {
        var tmpGamePhase = response.getElementsByTagName("phase")[0].firstChild.data.toString();
        var tmpGameTurn = response.getElementsByTagName("turn")[0].firstChild.data.toString();
        if(tmpGamePhase != gamePhase || tmpGameTurn != gameTurn) {
            var votes = response.getElementsByTagName("votes")[0].firstChild.data.toString();
            var gamePhaseHTML = document.getElementById("game_phase");
            var gameTurnHTML = document.getElementById("game_turn");
            gamePhaseHTML.innerHTML = tmpGamePhase;
            gameTurnHTML.innerHTML = tmpGameTurn;
            gamePhase = tmpGamePhase;
            gameTurn = tmpGameTurn;
            aliveArray = response.getElementsByTagName("alive")[0].getElementsByTagName("player");
            deadArray = response.getElementsByTagName("dead")[0].getElementsByTagName("player");
            displayPlayers(aliveArray, deadArray);
        }
    }
    setTimeout("requestGameInformation();", updateInterval);
}

function displayPlayers(aliveArray, deadArray) {
    var aliveListHTML = document.getElementById("game_alive_list");
    aliveListHTML.innerHTML = "";
    var deadListHTML = document.getElementById("game_dead_list");
    deadListHTML.innerHTML = "";
    for(var i = 0; i < aliveArray.length; i++) {
        var player_name = aliveArray.item(i).getElementsByTagName("name")[0].firstChild.data.toString();
        var player_id = aliveArray.item(i).getElementsByTagName("id")[0].firstChild.data.toString();
        aliveListHTML.innerHTML += "<li class='game_player_list_alive'>" + 
                                   "<a href='./profile.php?user_id='" + player_id + "'>" + 
                                   player_name + "</a></li>\n";
    }
    for(var i = 0; i < deadArray.length; i++) {
        var player_name = deadArray.item(i).getElementsByTagName("name")[0].firstChild.data.toString();
        var player_id = deadArray.item(i).getElementsByTagName("id")[0].firstChild.data.toString();
        var player_role = deadArray.item(i).getElementsByTagName("role")[0].firstChild.data.toString();
        deadListHTML.innerHTML += "<li class='game_player_list_dead'>" + 
                                   "<a href='./profile.php?user_id='" + player_id + "'>" + 
                                   player_name + "</a> (" + player_role + ")</li>\n";
    }
}

function trim(s) {
    return s.replace(/(^\s+)|(\s+$)/g, "");
}
