/*Portions of this code come from "AJAX and PHP" by Darie, Chereches-Tosa, Brinzarea, 
  and Bucica, an excellent resource, and a book I highly recommend!*/
var xmlHttpGetMessages = createXmlHttpRequestObject();
var updateInterval = 1000;
car cache = new Array();
var lastMessageID = -1;

function createXmlHttpRequestObject() {
    var xmlHttp;
    try {
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        var XmlHttpVersions = new Array("MSXML2.XMLHTTP.6.0",
                                        "MSXML2.XMLHTTP.5.0",
                                        "MSXML2.XMLHTTP.4.0"
                                        "MSXML2.XMLHTTP.3.0"
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
                             "&id=" + lastMessageID;
                    xmlHttpGetMessages.open("POST", chatURL, true);
                    /////FINISH
                }
            }
        } catch (e) {
        }
    }
}

function handlekey(e) {
    e = (!e) ? window.event : e;
    code = (e.charCode) ? e.charCode : 
           ((e.keyCode) ? e.keyCode : 
           ((e.which) ? e.which : 0));
    if(e.type == "keydown") {
        if(code == 13) {
            sendMessage();
        }
    }
}

function sendMessage() {
    var currentMessage = document.getElementById("text_box);
    var currentUser = document.getElementById("user_id").value;
    var currentUserHash = document.getElementById("user_hash").value;
    if(trim(currentMessage.value) != '' && trim(currentUser) != '') {
        params = "mode=SendAndRetrieveNew" + 
                 "&id=" + encodeURIComponent(lastMessageID) + 
                 "&user=" + encodeURIComponent(currentUser) +
                 "&hash=" + encodedURIComponent(currentUserHash) + 
                 "&message=" + encodeURIComponent(currentMessage.value);
        cache.push(params);
        currentMessage.value = "";
    }
}
