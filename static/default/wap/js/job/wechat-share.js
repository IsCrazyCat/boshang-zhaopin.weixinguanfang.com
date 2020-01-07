// 引入JS文件
// 在需要调用JS接口的页面引入如下JS文件，（支持https）：http://res.wx.qq.com/open/js/jweixin-1.2.0.js

function alertStr(str) {
    $('.page-group').append('<div class="tip-box" style="z-index:99999999;position: absolute;top: 30%;right: 0;bottom: 0;left: 0;"><div class="tip-body"><h3>' + str + '</h3></div></div>');
    setTimeout(function () {
        $('.tip-box').remove();
    }, 1500);
}

$.post(
    'https://niuzhigongzuo.com/wx/getWechatJsTicket',
    {url: location.href.split('#')[0]},
    function (data) {
        wx.config({
            debug: false,
            appId: data.appId,
            timestamp: data.timestamp,
            nonceStr: data.nonceStr,
            signature: data.signature,
            jsApiList: [
                'onMenuShareTimeline',
                'onMenuShareAppMessage'
            ]
        });
    }
);

wx.ready(function () {

    // 判断当前版本是否支持指定 JS 接口，支持批量判断
    wx.checkJsApi({
        jsApiList: [
            'onMenuShareTimeline',
            'onMenuShareAppMessage'
        ],
        success: function (res) {
            console.log(JSON.stringify(res));
        }
    });

    // 获取“分享到朋友圈”按钮点击状态及自定义分享内容接口
    wx.onMenuShareTimeline({
        title: $('#wx-share-title').val(),
        link: location.href,
        imgUrl: $('#wx-share-imgUrl').val(),
        success: function () {
            alertStr('分享成功！');
        },
        cancel: function () {
            alertStr('用户取消分享！');
        },
        fail: function (res) {
            console.log(JSON.stringify(res));
            alertStr('分享失败！');
        }
    });

    // 获取“分享给朋友”按钮点击状态及自定义分享内容接口
    wx.onMenuShareAppMessage({
        title: $('#wx-share-title').val(),
        desc: $('#wx-share-desc').val(),
        link: location.href,
        imgUrl: $('#wx-share-imgUrl').val(),
        type: 'link',
        success: function () {
            alertStr('分享成功！');
        },
        cancel: function () {
            alertStr('用户取消分享！');
        },
        fail: function (res) {
            console.log(JSON.stringify(res));
            alertStr('分享失败！');
        }
    });
});

// 步骤五：通过error接口处理失败验证
wx.error(function (res) {
    console.log(res);
});