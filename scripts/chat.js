/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var chatURL = 'chat.php';
var xmlHttpGetMessages = createXmlHttpRequestObject();
var updateInterval = 1000;
var cache = new Array();
var lastMessageID = -1;

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

function init() {
    var textBox = document.getElementById("text_box");
    requestNewMessages();
}

function requestNewMessages() {
    var currentUser = document.getElementById("user_id").value;
    var currentUserHash = document.getElementById("user_hash").value;
    if(xmlHttpGetMessages) {
        try {
            if(xmlHttpGetMessages.readyState == 4 || 
               xmlHttpGetMessages.readyState == 0) {
                var params = "";
                if(cache.length > 0) {
                    params = cache.shift();
                } else {
                    params = "mode=RetrieveNew" + 
                             "&id=" + lastMessageIDi;
                }
                xmlHttpGetMessages.open("POST", chatURL, true);
                xmlHttpGetMessages.setRequestHeader("Content-Type",
                                                    "application/x-www-form-urlencoded");
                xmlHttpGetMessages.onreadystatechange = handleReceivingMessages;
                xmlHttpGetMessages.send(params);
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
    var response = xmlHttpGetMessages.reasponseText;
    if(response.indexOf("ERRNO") >= 0 || response.indexOf("error:") >= 0 ||
       response.length == 0) {
        throw(response.length == 0? "Void server response." : response);
    }
    response = xmlHttpGetMessages.responseXML.documentElement;
    idArray = response.getElementsByTagName("id");
    userArray = response.getElementsByTagName("user");
    dateArray = response.getElementsByTagName("date");
    textArray = response.getElementsByTagName("text");
    displayMessages(idArray, userArray, dateArray, textArray);
    if(idArray.length > 0) {
        lastMessageID = idArray.item(idArray.length - 1).firstChild.data;
    }
    setTimeout("requestNewMessages();", updateInterval);
}

function displayMessages(idArray, userArray, dateArray, textArray) {
    for(var i = 0; i < idArray.length; i++) {
        var user = userArray.item(i).firstChild.data.toString();
        var date = dateArray.item(i).firstChild.data.toString();
        var text = textArray.item(i).firstChild.data.toString();
        var htmlMessage = "<p style='chat_message'>\n";
        htmlMessage += "<span style='chat_date_user'>" + user + " (" + date + "): </span>";
        htmlMessage += text; //toString()?
        htmlMessage += "</p>\n";
        displayMessage(htmlMessage);
    }
}

function displayMessage(message) {
    var chatText = document.getElementById("chat_text");
    chatText.innerHTML += message;
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
    if(trim(currentMessage.value) != '' && trim(currentUser) != '') {
        params = "mode=SendAndRetrieveNew" + 
                 "&id=" + encodeURIComponent(lastMessageID) + 
                 "&user=" + encodeURIComponent(currentUser) +
                 "&hash=" + encodeURIComponent(currentUserHash) + 
                 "&message=" + encodeURIComponent(currentMessage.value);
        cache.push(params);
        currentMessage.value = "";
    }
}
