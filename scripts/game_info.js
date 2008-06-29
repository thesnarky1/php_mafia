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
                    var user = document.getElementById("user_id").value;
                    var userHash = document.getElementById("user_hash").value;
                    var gameId = document.getElementById("game_id").value;
                    params = "game_id=" + gameId + 
                             "&game_turn=" + gameTurn + 
                             "&game_phase=" + gamePhase +
                             "&user_id=" + user + 
                             "&user_hash=" + userHash;
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
            var gamePhaseHTML = document.getElementById("game_phase");
            var gameTurnHTML = document.getElementById("game_turn");
            gamePhaseHTML.innerHTML = tmpGamePhase;
            gameTurnHTML.innerHTML = tmpGameTurn;
            gamePhase = tmpGamePhase;
            gameTurn = tmpGameTurn;
            playerArray = response.getElementsByTagName("player_list")[0].getElementsByTagName("player");
            displayPlayers(playerArray);

        }
    }
    setTimeout("requestGameInformation();", updateInterval);
}

function displayPlayers(playerArray) {
    var gameLynchHTML = document.getElementById("game_vote_to_lynch");
    gameLynchHTML.innerHTML = "";
    var aliveListHTML = document.getElementById("game_alive_list");
    aliveListHTML.innerHTML = "";
    var deadListHTML = document.getElementById("game_dead_list");
    deadListHTML.innerHTML = "";
    var alivePlayers = 0;
    var deadPlayers = 0;
    for(var i = 0; i < playerArray.length; i++) {
        var player = playerArray.item(i);
        var playerName = player.getElementsByTagName("name")[0].firstChild.data.toString();
        var playerId = player.getElementsByTagName("id")[0].firstChild.data.toString();
        var playerAlive = player.getElementsByTagName("alive")[0].firstChild.data.toString();
        if(playerAlive == 'Y') {
            aliveListHTML.innerHTML += "<li class='game_player_list_alive'>" + 
                                       "<a href='./profile.php?user_id='" + playerId + "'>" + 
                                       playerName + "</a></li>\n";
            alivePlayers++;
        } else {
            var playerRole = player.getElementsByTagName("role_name")[0].firstChild.data.toString();
            deadListHTML.innerHTML += "<li class='game_player_list_dead'>" + 
                                       "<a href='./profile.php?user_id='" + playerId + "'>" + 
                                       playerName + "</a> (" + playerRole + ")</li>\n";
            deadPlayers++;
        }
    }
    document.getElementById("game_alive").innerHTML = alivePlayers;
    document.getElementById("game_dead").innerHTML = deadPlayers;
}

function trim(s) {
    return s.replace(/(^\s+)|(\s+$)/g, "");
}
