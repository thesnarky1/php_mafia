/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var actionURL = './perform_action.php';
var debugMode = true;
var targetId = null;
var actionId = null;
var actionCache = new Array();
var myTimeout = null;

$(function() {
   userId = document.getElementById("user_id").value;
   userHash = document.getElementById("user_hash").value;
   gameId = document.getElementById("game_id").value;
  });

/*
 * actionId = type of action.
 * targetId = target of your action
 */
function performAction(actionId, targetId) {
    actionParams = {game_id:gameId, user_id:userId,
                    action_id:actionId, target_id:targetId,
                    user_hash:userHash};
    $.get(actionURL, actionParams, showActionMessage);
}

function show_action_message(str) {
    if(myTimeout) {
        clearTimeout(myTimeout);
        myTimeout = null;
    }
    var action_message = document.getElementById("action_message");
    action_message.innerHTML = str;
    action_message.style.display = "block";
    myTimeout = setTimeout("hide_action_message()", 5000);
}

function hide_action_message() {
    var action_message = document.getElementById("action_message");
    action_message.style.display = "none";
    myTimeout = null;
}

