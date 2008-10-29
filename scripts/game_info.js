/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var gameInfoURL = './game_information.php';
var updateInterval = 1000;
var gameInfoCache = new Array();
var debugMode = true;
var boxesPerRow = 2;
var playerCount = 1;
var gameTracker = -1;
var forceUpdate = true;

$(function() {
   userId = $('#user_id')[0].value;
   userHash = $('#user_hash')[0].value;
   gameId = $('#game_id')[0].value;
   scrollChatBox();
   requestGameInformation();
   requestNewMessages();
  });

function requestGameInformation() {
    var gameInfoParams = {game_id:gameId, user_id:userId,
                          user_hash:userHash, game_tracker:gameTracker};
    if(forceUpdate) {
        gameInfoParams['force'] = 'true';
        forceUpdate = false;
    }
    $.get(gameInfoURL, 
          gameInfoParams,
          dealWithGameInformation);
}

function dealWithGameInformation(gameResponse) {
    if(gameResponse.getElementsByTagName("tracker").length > 0) {
        var needsUpdate = false;
        var tmpGameTracker = gameResponse.getElementsByTagName("tracker")[0].firstChild.data.toString();
        if(tmpGameTracker > gameTracker) {
            gameTracker = tmpGameTracker;
            needsUpdate = true;
        } else if(userId > 0) {
            needsUpdate = true;
        }
        if(needsUpdate) {
            var gamePhase = gameResponse.getElementsByTagName("phase")[0].firstChild.data.toString();
            $("#chat_channel").html(gameResponse.getElementsByTagName("channel")[0].firstChild.data.toString() + " Channel");
            $("#game_phase").html(gameResponse.getElementsByTagName("phase")[0].firstChild.data.toString());
            $("#game_turn").html(gameResponse.getElementsByTagName("turn")[0].firstChild.data.toString());
            var roleMessage = gameResponse.getElementsByTagName("role_instructions");
            if(roleMessage.length > 0) {
                roleMessage = roleMessage[0].firstChild.data.toString();
            } else {
                roleMessage = "";
            }
            if(roleMessage != "") {
                $("#role_instructions").html(roleMessage);
            }
            var actionMessage = gameResponse.getElementsByTagName("action");
            if(actionMessage.length > 0) {
                actionMessage = actionMessage[0].firstChild.data.toString();
            } else {
                actionMessage = "";
            }
            var bannerMessage = gameResponse.getElementsByTagName("banner");
            if(bannerMessage.length > 0) {
                bannerMessage = bannerMessage[0].firstChild.data.toString();
                bannerAction = gameResponse.getElementsByTagName("banner_action")[0].firstChild.data.toString();
            } else {
                bannerMessage = "";
                bannerAction = "";
            }
            var altBannerMessage = gameResponse.getElementsByTagName("alt_banner");
            if(altBannerMessage.length > 0) {
                altBannerMessage = altBannerMessage[0].firstChild.data.toString();
                altBannerAction = gameResponse.getElementsByTagName("alt_banner_action")[0].firstChild.data.toString();
            } else {
                altBannerMessage = "";
                altBannerAction = "";
            }
            var votesToLynch = gameResponse.getElementsByTagName("votes_required")[0].firstChild.data.toString();
            var voteTallyHTML = $("#vote_tally")[0];
            var voteTally = gameResponse.getElementsByTagName("vote_tally");
            if(voteTally.length > 0) {
                voteTallyHTML.innerHTML = "";
                for(var i = 0; i < voteTally.length; i++) {
                    var vote = voteTally.item(i);
                    var voteName = vote.getElementsByTagName("name")[0].firstChild.data.toString();
                    var voteVote = vote.getElementsByTagName("vote")[0].firstChild.data.toString();
                    voteTallyHTML.innerHTML += "<li class='game_player_list_alive'>" + voteName + ": " + voteVote + "</li>\n";
                }
            } else {
                voteTallyHTML.innerHTML = "";
            }
            playerArray = gameResponse.getElementsByTagName("player_list")[0].getElementsByTagName("player");
            var targetMessage = gameResponse.getElementsByTagName("target");
            if(targetMessage.length > 0) {
                targetMessage = targetMessage[0].firstChild.data.toString();
            } else {
                targetMessage = "No current target";
            }
            document.getElementById("target").innerHTML = targetMessage;
            displayPlayers(playerArray, gamePhase, bannerMessage, bannerAction, altBannerMessage, altBannerAction, actionMessage, votesToLynch);
        }
    }
    setTimeout("requestGameInformation();", updateInterval);
}

function displayPlayers(playerArray, gamePhase, bannerMessage, bannerAction, altBannerMessage, altBannerAction, action, votesToLynch) {
    var gameLynchHTML = document.getElementById("game_vote_to_lynch");
    var aliveListHTML = document.getElementById("game_alive_list");
    var deadListHTML = document.getElementById("game_dead_list");
    var playerBoxTable = document.getElementById("player_box_table");
    gameLynchHTML.innerHTML = votesToLynch;
    aliveListHTML.innerHTML = "";
    deadListHTML.innerHTML = "";
    playerTable = "";
    if(bannerMessage != "") {
        playerTable += "<div id='action_banner' onclick='performAction(" + bannerAction + ",0);'>" + bannerMessage + "</div>\n";
    }
    if(altBannerMessage != "") {
        playerTable += "<div id='action_banner' onclick='performAction(" + altBannerAction + ",0);'>" + altBannerMessage + "</div>\n";
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
        playerRole = player.getElementsByTagName("role_name");
        //If we have a role, display it. If not, display the faction. If not... well... display nothing!
        if(playerRole.length > 0) {
            playerRole = "(" + playerRole[0].firstChild.data.toString() + ")";
            playerFaction = player.getElementsByTagName("role_faction")[0].firstChild.data.toString();
        } else {
            playerRole = player.getElementsByTagName("role_faction");
            if(playerRole.length > 0) {
                playerRole = "(" + playerRole[0].firstChild.data.toString() + ")";
                playerFaction = player.getElementsByTagName("role_faction")[0].firstChild.data.toString();
            } else {
                playerRole = "(Unknown)";
            }
        }
        if(playerAlive == 'Y') {
            aliveListHTML.innerHTML += "<li class='game_player_list_alive'>" + 
                                       "<a href='./profile.php?id=" + playerId + ">" + 
                                       playerName + "</a> " + playerRole + "</li>\n";
            alivePlayers++;
        } else {
            deadListHTML.innerHTML += "<li class='game_player_list_dead'>" + 
                                       "<a href='./profile.php?id=" + playerId + ">" + 
                                       playerName + "</a> " + playerRole + "</li>\n";
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
        if(playerAlive == 'Y') {
            playerHTML += "<div id='player_box_alive' onclick='performAction(" + action + "," + playerId + ")'>";
            playerHTML += "<img src='./images/avatars/" + playerAvatar + "'>";
            playerHTML += "<p class='player_name'>" + playerName;
            if(playerRole) {
                playerHTML += "<br />" + playerRole;
            }
            playerHTML += "</p>";
            playerHTML += "</div>";
        } else {
            playerHTML += "<div id='player_box_dead'>";
            playerHTML += "<img src='./images/avatars/dead.png'>";
            playerHTML += "<p class='player_name'><span class='strikeout'>" + playerName + "</span>";
            if(playerRole) {
                playerHTML += "<br />" + playerRole;
            }
            playerHTML += "</p>";
            playerHTML += "</div>\n";
        }
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
