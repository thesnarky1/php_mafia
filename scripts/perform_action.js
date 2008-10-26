/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var actionURL = './perform_action.php';
var debugMode = true;
var targetId = null;
var actionId = null;
var actionCache = new Array();
var myTimeout = null;

$(function() {
   userId = $('#user_id')[0].value;
   userHash = $('#user_hash')[0].value;
   gameId = $('#game_id')[0].value;
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

function showActionMessage(str) {
    if(myTimeout) {
        clearTimeout(myTimeout);
        myTimeout = null;
    }
    $('#action_message').html(str).attr('display', 'block');
    myTimeout = setTimeout("hideActionMessage()", 5000);
}

function hideActionMessage() {
    $('#action_message').attr('display', 'none');
    myTimeout = null;
}

