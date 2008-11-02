var actionURL = './perform_action.php';
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
    var str = $(response).find("action_data")[0];
    $('#action_message').html(str).show('slow');
    myTimeout = setTimeout("hideActionMessage()", 5000);
}

function hideActionMessage() {
    $('#action_message').html(str).hide('slow');
    myTimeout = null;
}

