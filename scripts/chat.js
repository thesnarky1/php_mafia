/*Portions of this code originally come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend! However
  I've since revamped the code, and its now mine. However, I *still* recommend that book.*/
var chatURL = './chat.php';
var updateInterval = 1000;
var lastMessageID = $("chat_message:last input");
var debugMode = true;
var title = "Thieves Tavern Games";
var messageNum = 0;
var hasFocus = true;

$(setFocusEvents());

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
    idArray = chatResponse.getElementsByTagName("id");
    userArray = chatResponse.getElementsByTagName("user");
    dateArray = chatResponse.getElementsByTagName("date");
    textArray = chatResponse.getElementsByTagName("text");
    channelArray = chatResponse.getElementsByTagName("channel");
    displayMessages(idArray, userArray, dateArray, textArray, channelArray);
    if(idArray.length > 0 && idArray.item(idArray.length - 1).firstChild.data > 0) {
        lastMessageID = idArray.item(idArray.length - 1).firstChild.data;
    }
    setTimeout("requestNewMessages();", updateInterval);
}

function displayMessages(idArray, userArray, dateArray, textArray, channelArray) {
    for(var i = 0; i < idArray.length; i++) {
        var user = userArray.item(i).firstChild.data.toString();
        var date = dateArray.item(i).firstChild;
        var id = idArray.item(i).firstChild.data.toString();
        if(date) {
            date = date.data.toString();
        } else {
            date = false;
        }
        var text = textArray.item(i).firstChild.data.toString();
        var channel = channelArray.item(i).firstChild.data.toString();
        var htmlMessage = "<div class='chat_message'>\n";
        htmlMessage += "<input type='hidden' value='" + id + "' />\n";
        htmlMessage += "<span class='chat_message_channel'><img src='./images/roles/" + channel + "'/></span> ";
        if(date) {
            htmlMessage += "<span class='chat_message_date'>(" + date + ") </span>";
        } else {
            htmlMessage += ": ";
        }
        htmlMessage += "<span class='chat_message_user'>" + user + "</span>: ";
        htmlMessage += text; //toString()?
        htmlMessage += "</div>\n";
        if(id < 0 || id > lastMessageID) {
            displayMessage(htmlMessage);
        }
    }
}

function displayMessage(message) {
    var chatText = document.getElementById("chat_text");
    var scrollDown = (chatText.scrollHeight - chatText.scrollTop <= chatText.offsetHeight);
    chatText.innerHTML += message;
    chatText.scrollTop = scrollDown ? chatText.scrollHeight : chatText.scrollTop;
    if(!hasFocus) {
        messageNum++;
        updateTitle();
    }
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
    var currentMessage = document.getElementById("text_box");
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
