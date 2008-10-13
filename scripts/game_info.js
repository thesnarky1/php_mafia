/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var gameInfoURL = './game_information.php';
var xmlHttpGetInformation = createXmlHttpRequestObject();
var updateInterval = 1000;
var gameInfoCache = new Array();
var debugMode = true;
var boxesPerRow = 2;
var playerCount = 1;
var gameTracker = -1;


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
                if(gameInfoCache.length > 0) {
                    params = gameInfoCache.shift();
                } else {
                    var user = document.getElementById("user_id").value;
                    var userHash = document.getElementById("user_hash").value;
                    var gameId = document.getElementById("game_id").value;
                    params = "game_id=" + gameId + 
                             "&game_tracker=" + gameTracker + 
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
        var tmpGameTracker = response.getElementsByTagName("tracker")[0].firstChild.data.toString();
        if(tmpGameTracker > gameTracker) {
            gameTracker = tmpGameTracker;
            var gameChatHTML = document.getElementById("chat_channel");
            gameChatHTML.innerHTML = "";
            gameChatHTML.innerHTML = response.getElementsByTagName("channel")[0].firstChild.data.toString() + " Channel";
            var gamePhaseHTML = document.getElementById("game_phase");
            var gameTurnHTML = document.getElementById("game_turn");
            /*gamePhaseHTML.innerHTML = tmpGamePhase;*/
            gamePhaseHTML.innerHTML = response.getElementsByTagName("phase")[0].firstChild.data.toString();
            /*gameTurnHTML.innerHTML = tmpGameTurn;*/
            gameTurnHTML.innerHTML = response.getElementsByTagName("turn")[0].firstChild.data.toString();
            var bannerMessage = response.getElementsByTagName("banner");
            if(bannerMessage.length > 0) {
                bannerMessage = bannerMessage[0].firstChild.data.toString();
            } else {
                bannerMessage = "";
            }
            playerArray = response.getElementsByTagName("player_list")[0].getElementsByTagName("player");
            displayPlayers(playerArray, gamePhase, bannerMessage);
        }
    }
    setTimeout("requestGameInformation();", updateInterval);
}

function displayPlayers(playerArray, gamePhase, bannerMessage) {
    var gameLynchHTML = document.getElementById("game_vote_to_lynch");
    gameLynchHTML.innerHTML = "";
    var aliveListHTML = document.getElementById("game_alive_list");
    aliveListHTML.innerHTML = "";
    var deadListHTML = document.getElementById("game_dead_list");
    deadListHTML.innerHTML = "";
    var playerBoxTable = document.getElementById("player_box_table");
    playerTable = "";
    if(bannerMessage != "") {
        playerTable += "<div class='banner'>" + bannerMessage + "</div>\n";
    }
    playerTable += "<table align='center'>";
    var alivePlayers = 0;
    var deadPlayers = 0;
    playerCount = 1;
    for(var i = 0; i < playerArray.length; i++) {
        var playerHTML = "";
        var playerRole = false;
        var playerFaction = false;
        var player = playerArray.item(i);
        var playerName = player.getElementsByTagName("name")[0].firstChild.data.toString();
        var playerId = player.getElementsByTagName("id")[0].firstChild.data.toString();
        var playerAlive = player.getElementsByTagName("alive")[0].firstChild.data.toString();
        var playerAvatar = player.getElementsByTagName("avatar")[0].firstChild.data.toString();
        if(playerAlive == 'Y') {
            aliveListHTML.innerHTML += "<li class='game_player_list_alive'>" + 
                                       "<a href='./profile.php?id=" + playerId + ">" + 
                                       playerName + "</a></li>\n";
            alivePlayers++;
        } else {
            playerRole = player.getElementsByTagName("role_name")[0].firstChild.data.toString();
            playerFaction = player.getElementsByTagName("role_faction")[0].firstChild.data.toString();
            deadListHTML.innerHTML += "<li class='game_player_list_dead'>" + 
                                       "<a href='./profile.php?id=" + playerId + ">" + 
                                       playerName + "</a> (" + playerRole + ")</li>\n";
            deadPlayers++;
        }
        if(playerCount % boxesPerRow == 1) {
            playerTable += "<tr>\n";
        }
        playerHTML = "<td ";
        if(playerFaction) {
            playerHTML += "class='game_player_" + playerFaction + "'";
        } else {
            playerHTML += "class='game_player_Unknown'";
        }
        playerHTML += ">";
        playerHTML += "<div id='player_box'>";
        if(playerAlive == 'Y') {
            playerHTML += "<a href='#'>";
            playerHTML += "<img src='./images/avatars/" + playerAvatar + "'>";
            playerHTML += "<p class='player_name'>" + playerName + "</p>";
            playerHTML += "</a>";
        } else {
            playerHTML += "<img src='./images/avatars/dead.png'>";
            playerHTML += "<p class='player_name'><span class='strikeout'>" + playerName + "</span></p>";
        }
        playerHTML += "</div>";
        playerHTML += "</td>";
        playerTable += playerHTML;
        if(playerCount % boxesPerRow == 0) {
            playerTable += "</tr>\n";
        }
        playerCount++;
    }
    if((playerCount - 1) % boxesPerRow != 0) {
        playerTable += "</tr>\n";
    }
    playerTable += "</table>\n";
    playerBoxTable.innerHTML = playerTable;
    document.getElementById("game_alive").innerHTML = alivePlayers;
    document.getElementById("game_dead").innerHTML = deadPlayers;
}

function trim(s) {
    return s.replace(/(^\s+)|(\s+$)/g, "");
}
