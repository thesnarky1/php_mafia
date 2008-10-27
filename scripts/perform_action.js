/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var actionURL = './perform_action.php';
var targetId = null;
var actionId = null;
var actionCache = new Array();
var myTimeout = null;

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

function showActionMessage(response) {
    if(myTimeout) {
        clearTimeout(myTimeout);
        myTimeout = null;
    }
    str = response.getElementsByTagName("action_data")[0].firstChild.data.toString();
    $('#action_message').html(str).show('slow');
    myTimeout = setTimeout("hideActionMessage()", 5000);
}

function hideActionMessage() {
    $('#action_message').html(str).hide('slow');
    myTimeout = null;
}

