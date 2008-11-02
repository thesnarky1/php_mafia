/*Portions of this code originally come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend! However
  I've since revamped the code, and its now mine. However, I *still* recommend that book.*/
var chatURL = './chat.php';
var updateInterval = 1000;
var lastMessageID = 0;
var title = "Thieves Tavern Games";
var messageNum = 0;
var hasFocus = true;

$(setFocusEvents());
$(setTimeout("getLastMessageID()", 500));

function getLastMessageID() {
    lastMessageID = $(".chat_message:last input").attr("value");
}

/*
  setFocusEvents comes from 
  http://odondo.wordpress.com/2007/08/28/javascript-and-cross-browser-window-focus/
 */
function setFocusEvents() {
    if(navigator.appName == "Microsoft Internet Explorer") {
        document.onfocusout = function() { onWindowBlur(); };
        document.onfocusin = function() { onWindowFocus(); };
    } else {
        window.onblur = function() { onWindowBlur(); };
        window.onfocus = function() { onWindowFocus(); };
    }
}


function onWindowBlur() {
    hasFocus = false;
}

function onWindowFocus() {
    if(!hasFocus) {
        document.title = title;
        hasFocus = true;
        messageNum = 0;
    }
}

function updateTitle() {
    if(messageNum > 0) {
        document.title = "[" + messageNum + "] " + title;
    } else {
        document.title = title;
    }
}

function scrollChatBox() {
    //var chatText = $("#chat_text")[0];
    //chatText.scrollTop = chatText.scrollHeight;
}

function requestNewMessages() {
    var chatParams = {mode:'RetrieveNew', game_id:gameId,
                      user_hash: userHash, user_id:userId, 
                      id:lastMessageID};
    $.get(chatURL, chatParams, readMessages);
}

function readMessages(chatResponse) {
    var chatText = $('#chat_text');
    var chatTextObject = chatText.get(0);
    $(chatResponse).find('message').each(
        function() {
            var id = $(this).find('id')[0].firstChild.data.toString();
            var user = $(this).find('user')[0].firstChild.data.toString();
            var date = $(this).find('date')[0].firstChild.data.toString();
            var text = $(this).find('text')[0].firstChild.data.toString();
            var channel = $(this).find('channel')[0].firstChild.data.toString();
            var newMessage = $("<div>").addClass('chat_message');
            newMessage.append($("<input type='hidden'/>").attr("value", id));
            newMessage.append($("<span>").addClass("chat_message_channel").append("<img src='./images/roles/" + channel + "'/> "));
            newMessage.append($("<span>").addClass("chat_message_date").append("(" + date + ") "));
            newMessage.append($("<span>").addClass("chat_message_user").append(user + ": "));
            newMessage.append(text);
            if(id < 0 || id > lastMessageID) {
                var scrollDown = (chatTextObject.scrollHeight - chatTextObject.scrollTop <= chatTextObject.offsetHeight);
                chatText.append(newMessage);
                chatTextObject.scrollTop = scrollDown ? chatTextObject.scrollHeight : chatTextObject.scrollTop;
                //Update our last message id if its smaller than this one.
                if(id > lastMessageID) {
                    lastMessageID = id;
                }
    
                //Update the title if we're not on top
                if(!hasFocus) {
                    messageNum++;
                    updateTitle();
                }
            }
        }
    );
    setTimeout("requestNewMessages();", updateInterval);
}

function displayError(message) {
    displayMessage("Error accessing the server! " + (debugMode ? "<br />" + message : ""));
}

function handleKey(e) {
    e = (!e) ? window.event : e;
    code = (e.charCode) ? e.charCode : 
           ((e.keyCode) ? e.keyCode : 
           ((e.which) ? e.which : 0));
    if(e.type == "keydown") {
        if(code == 13) {
            sendMessage();
            var chatBox = document.getElementById("text_box");
            chatBox.value = "";
        }
    }
}

function sendMessage() {
    var currentMessage = $("#text_box")[0];
    if($.trim(currentMessage.value) != '' && $.trim(userId) != '') {
        chatParams = {mode:'SendAndRetrieveNew',  
                      id:encodeURIComponent(lastMessageID),
                      user_id:encodeURIComponent(userId),
                      user_hash:encodeURIComponent(userHash),
                      game_id:encodeURIComponent(gameId),
                      message:currentMessage.value};
        $.get(chatURL, chatParams, readMessages);
        currentMessage.value = "";
    }
}
