
var $bath = $("#bath").val();

// 小时工生成补贴证明背景图
var img_xiaoshigong = $bath + '/static/images/xiaoshigong1.png';
// 正式工生成补贴证明背景图
var img_zhengshigong = $bath + '/static/images/zhengshigong1.png';
// 小时工待填充数据坐标
var xsg_position = { data1: {x:220 , y:196 }, data2: {x:220 , y:249 }, data3: {x:220 , y:301 }, data4: {x:220 , y:348 }, data5: {x:220 , y:402 }, data6: {x: 220 , y: 450 }, data7: {x:142 , y:499 }  };
// 正式工待填充数据坐标
var zsg_position = { data1: {x:220 , y:190 }, data2: {x:220 , y:242 }, data3: {x:220 , y:297 }, data4: {x:220 , y:348 }, data5: {x:220 , y:404 }, data6: {x: 220, y: 453}, data7: {x:142 , y:496 } };

function generate(applyData) {
    $('#subsidy-detail .subsidy-detail-header').text(applyData.jobName);
    var customerUsername = $("#customerUsername").val();
    var customerId = $("#customerId").val();

    var _data = {};
    // 会员姓名
    _data.data1 = customerUsername.stringLimit(8, '…');
    // 劳务确认时间
    _data.data2 = applyData.laborConfirmTime;
    // 岗位名称
    _data.data3 = applyData.jobName.stringLimit(8, '…');
    if (applyData.type == '0') {
        _data.background = img_xiaoshigong;
        _data.position = xsg_position;
        // 补贴周期
        _data.data4 = applyData.durationTime;
        // 普通价格
        _data.data5 = (applyData.hourlyPay + applyData.currentPay) + '元/小时';
        // VIP价格
        _data.data6 = (applyData.hourlyPay + applyData.maxPay) + '元/小时';
    } else if (applyData.type == '1') {
        _data.background = img_zhengshigong;
        _data.position = zsg_position;
        // 打卡天数
        _data.data4 = applyData.day + '天';
        // 正常价格
        _data.data5 = applyData.currentPay + '元';
        // VIP价格
        _data.data6 = applyData.maxPay + '元';
    } else if (applyData.type == '2') {
        _data.background = img_zhengshigong;
        _data.position = zsg_position;
    }
    // 备注
    if (applyData.remark) {
        _data.data7 = applyData.remark.stringLimit(50);
    }

    xQRCode.generateQRCode({
        elid: 'subsidy-image',
        qrcode: {
            width: 120,
            height: 120,
            padding: 5,
            typeNumber: -1,
            correctLevel: QRErrorCorrectLevel.H,
            background: "#ffffff",
            foreground: "#012b51",
            position: {x: 320, y: 580},
            text: 'https://niuzhigongzuo.com/login?personCode=' + customerId
        },
        width: 533,
        height: 708,
        mainImage: _data.background,
        texts: [
            { text: _data.data1||'--', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data1.x, y: _data.position.data1.y },
            { text: _data.data2||'0000年00月00日', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data2.x, y: _data.position.data2.y },
            { text: _data.data3||'牛职工作网', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data3.x, y: _data.position.data3.y },
            { text: _data.data4||'--', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data4.x, y: _data.position.data4.y },
            { text: _data.data5||'--', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data5.x, y: _data.position.data5.y },
            { text: _data.data6||'--', color: '#fdfefe', fontSize: '23px', textAlign: 'left', fontWeight: 'bold', textBaseline: 'middle', x: _data.position.data6.x, y: _data.position.data6.y },
            { text: _data.data7||'无', color: '#e5f0f9', fontSize: '19px', textAlign: 'left', fontWeight: 'normal', textBaseline: 'middle', x: _data.position.data7.x, y: _data.position.data7.y, isWrap: true }
        ],
        images: [{image: $bath + '/static/images/seal.png', x: 560, y: 435, w: 210, h: 210}]
    });
    $('.subsidy-detail-layer').show();
}

$('.close-subsidy-detail-btn').click(function () {
    $('.subsidy-detail-layer').hide();
});