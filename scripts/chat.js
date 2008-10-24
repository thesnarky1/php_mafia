/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var chatURL = './chat.php';
var xmlHttpGetMessages = createXmlHttpRequestObject();
var updateInterval = 1000;
var chatCache = new Array();
var lastMessageID = -1;
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

//function init_chat() {
//    requestNewMessages();
//}

Ext.onReady(requestNewMessages());

function requestNewMessages() {
    if(xmlHttpGetMessages) {
        try {
            if(xmlHttpGetMessages.readyState == 4 || 
               xmlHttpGetMessages.readyState == 0) {
                var params = "";
                if(chatCache.length > 0) {
                    params = chatCache.shift();
                } else {
                    var user = document.getElementById("user_id").value;
                    var user_hash = document.getElementById("user_hash").value;
                    var game_id = document.getElementById("game_id").value;
                    params = "mode=RetrieveNew" + 
                             "&game_id=" + game_id + 
                             "&user_hash=" + user_hash + 
                             "&user_id=" + user + 
                             "&id=" + lastMessageID;
                }
                //xmlHttpGetMessages.open("POST", chatURL, true);
                xmlHttpGetMessages.open("GET", chatURL+"?"+params, true);
                xmlHttpGetMessages.setRequestHeader("Content-Type",
                                                    "application/x-www-form-urlencoded");
                xmlHttpGetMessages.onreadystatechange = handleReceivingMessages;
                xmlHttpGetMessages.send(); //params goes here
            } else {
                setTimeout("requestNewMessages();", updateInterval);
            }
        } catch (e) {
            displayError(e.toString());
        }
    }
}

function handleReceivingMessages() {
    if(xmlHttpGetMessages.readyState == 4) {
        if(xmlHttpGetMessages.status == 200) {
            try {
                readMessages();
            } catch(e) {
                displayError(e.toString());
            }
        } else {
            displayError(xmlHttpGetMessages.statusText);
        }
    }
}

function readMessages() {
    var response = xmlHttpGetMessages.responseText;
    if(response.indexOf("ERRNO") >= 0 || response.indexOf("error:") >= 0 ||
       response.length == 0) {
        throw(response.length == 0? "Void server response." : response);
    }
    response = xmlHttpGetMessages.responseXML.documentElement;
    idArray = response.getElementsByTagName("id");
    userArray = response.getElementsByTagName("user");
    dateArray = response.getElementsByTagName("date");
    textArray = response.getElementsByTagName("text");
    channelArray = response.getElementsByTagName("channel");
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
        var htmlMessage = "<p class='chat_message'>\n";
        htmlMessage += "<span class='chat_message_channel'><img src='./images/roles/" + channel + "'/></span> ";
        if(date) {
            htmlMessage += "<span class='chat_message_date'>(" + date + ") </span>";
        } else {
            htmlMessage += ": ";
        }
        htmlMessage += "<span class='chat_message_user'>" + user + "</span>: ";
        htmlMessage += text; //toString()?
        htmlMessage += "</p>\n";
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
}

function displayError(message) {
    displayMessage("Error accessing the server! " + (debugMode ? "<br />" + message : ""));
}

function trim(s) {
    return s.replace(/(^\s+)|(\s+$)/g, "");
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
    var currentUser = document.getElementById("user_id").value;
    var currentUserHash = document.getElementById("user_hash").value;
    var game = document.getElementById("game_id").value;
    if(trim(currentMessage.value) != '' && trim(currentUser) != '') {
        params = "mode=SendAndRetrieveNew" + 
                 "&id=" + encodeURIComponent(lastMessageID) + 
                 "&user_id=" + encodeURIComponent(currentUser) +
                 "&user_hash=" + encodeURIComponent(currentUserHash) + 
                 "&game_id=" + encodeURIComponent(game) + 
                 "&message=" + encodeURIComponent(currentMessage.value);
        chatCache.push(params);
        currentMessage.value = "";
    }
}
