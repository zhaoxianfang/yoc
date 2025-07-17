//判断app环境;
function detect() {
    let equipmentType = "";
    let agent = navigator.userAgent.toLowerCase();
    let android = agent.indexOf("android");
    let iphone = agent.indexOf("iphone");
    let ipad = agent.indexOf("ipad");
    if (android != - 1) {
        equipmentType = "android";
    }
    if (iphone != - 1 || ipad != - 1) {
        equipmentType = "ios";
    }
    return equipmentType;
}

// 和uniapp 数据交互 =========
//发送消息到app;
function sendAppData(jsonObj) {
    //发送json;不能随意改变userAgent,因为发送消息,需判断平台
    console.log("H5发送消息给APP", jsonObj, JSON.stringify(jsonObj));
    if (/android/i.test(navigator.userAgent)) {
        //这是Android平台下浏览器
        window.app.sendData(JSON.stringify(jsonObj));
    } else {
        //对ios发送消息的方法;
        window.webkit.messageHandlers.sendData.postMessage(JSON.stringify(jsonObj));
    }
}

//接收到app的Json数据,app那边必须发送json字符串数据;
function receiveAppData(data) {
    console.log("H5收到APP的消息",data, JSON.stringify(data));
    var session_key = "uniapp_req_web_login";
    if ( !isEmpty(sessionStorage.getItem(session_key))) {
        // console.log('has login');
        // 已经登录过了
        return;
    }
    http_request(`/users/auth/uniapp_login`,data,function (succ) {
        console.log('succ',succ,JSON.stringify(succ));
        //返回数据 非200：失败 ；200：成功
        if (succ.code == 200) {
            // 登录成功
            sessionStorage.setItem(session_key, "is_login");
            sendAppData({
                type: "web_login", // 类型为web登录
                status: true // 状态为成功
            });
            window.location.reload();
        }else{
            sendAppData({
                type: "web_login", // 类型为web登录
                status: false // 状态为失败
            });
        }
    },function (err) {
        console.log("receiveAppData error:", JSON.stringify(err));
        // 错误信息 errorMsg
        sendAppData({
            type: "web_login", // 类型为web登录
            status: false // 状态为失败
        });
    },'POST');
}

// 和uniapp 数据交互 end ================
