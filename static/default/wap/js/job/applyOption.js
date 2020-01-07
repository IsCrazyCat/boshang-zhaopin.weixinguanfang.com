var bath = $("#bath").val();
var today_str = (new Date()).format('yyyy-MM-dd');

// 出名单弹出层模板
var $listNameTemplate = $("#list-name-wrapper").clone();
var $interviewDetailLayerTemplate = $("#interview-detail-layer").clone();

/**
 * VIP高价
 */
function vipShareBindClick(){
    $('.vip-share.no-click-event').each(function () {
        $(this).removeClass("no-click-event");

        var invite = $(this)[0].dataset.invite;
        $(this).click(function () {
            var localStorageKey = "localStorage" + invite;
            var toVipSharePage = localStorage.getItem(localStorageKey);
            if (toVipSharePage) {
                location.href = bath + '/share2?invite=' + invite;
            } else {
                localStorage.setItem(localStorageKey, true);
                location.href = bath + '/regular';
            }
        });
    });
}

/**
 * 完善信息
 */
function perfectInformationBindClick(){
    $(".perfect-information.no-click-event").each(function () {
        $(this).removeClass("no-click-event");

        $(this).click(function () {
            location.href = bath + "/goBindBankCard";
        });
    });
}

function cancelApply(jobId) {
    $.post(bath + "/cancelJob", {
        jobId: jobId
    }, function (data) {
        if (data.response == "success") {
            $.toast("已成功取消报名");
            setTimeout(function() {
                location.reload();
            }, 2000);
        } else {
            $.alert(data.data.text, '提示!');
        }
    });
}

/**
 * 取消报名
 */
function cancelApplyBindClick(){
    $(".cancel-apply .no-click-event").each(function () {
        $(this).removeClass("no-click-event");

        var jobId = $(this)[0].dataset.jobId;
        $(this).click(function () {
            $.modal({
                title: '提示',
                text: '确认取消当前报名的岗位吗？',
                buttons: [
                    {
                        text: '再等等'
                    },
                    {
                        text: '确认取消',
                        onClick: function () {
                            cancelApply(jobId);
                        }
                    }
                ]
            });
        });
    });
}

/**
 * 出名单
 */
function myListNameBindClick() {
    $('.my-list-name.no-click-event').each(function () {
        $(this).removeClass("no-click-event");
        console.log("sajdlajs");

        var applyId = $(this)[0].dataset.applyId;
        $(this).click(function () {
            listName(applyId);
        });
    });
}
// 下拉操作表之 出名单
function listName(applyId) {
    $.post(bath + '/listNameDetail', {applyId: applyId}, function (data) {
        var listNameObject = $listNameTemplate.clone();
        if (data.response === 'success') {
            if (data.data.jobListNameInfo) {
                var jobListNameInfo = data.data.jobListNameInfo;
                listNameObject.find(".top-content-job-name").text(jobListNameInfo.jobName);
                if (jobListNameInfo.listNameDate) {
                    listNameObject.find(".top-content-time").text(jobListNameInfo.listNameDate).parent().show();
                }
            }
            if (!data.data.listNameList || data.data.listNameList.length == 0) {
                listNameObject.find('.no-list-name').show();
            } else {
                var listNameList = data.data.listNameList;
                var listNameLiTemplate = listNameObject.find('.list-name-li-template').clone().removeClass('list-name-li-template');

                for (var i = 0; i < listNameList.length; i++) {
                    var listNameLiObject = listNameLiTemplate.clone();
                    if (i > 0) {
                        listNameLiObject.addClass('li-not-first');
                    }
                    listNameLiObject.find('.list-name-li-title').text(listNameList[i].remark);
                    var dateText = formatDate(listNameList[i].listTime, 'yyyy年MM月dd日');
                    listNameLiObject.find('.list-name-li-text').text(dateText);

                    listNameLiObject.show();
                    listNameObject.find('.list-name-ul').append(listNameLiObject).show();
                }
            }

            $('#list-name-wrapper').html(listNameObject.html());
            $.popup('.popup-list-name');
        }
    }, 'json');
}

/**
 * 传厂牌
 */
function uploadWorkCardBindClick(){
    $('.upload-work-card.no-click-event').each(function () {
        $(this).removeClass("no-click-event");

        var applyId = $(this)[0].dataset.applyId;
        $(this).click(function () {
            location.href = bath + "/workcard?applyId=" + applyId;
        });
    });
}

/**
 * 入职薪资
 */
function muSubsidyBindClick(){
    $('.my-subsidy.no-click-event').each(function () {
        $(this).removeClass("no-click-event");

        var applyId = $(this)[0].dataset.applyId;
        var jobName = $(this)[0].dataset.jobName;
        var advanceAmount = $(this)[0].dataset.advanceAmount;
        advanceAmount = advanceAmount / 100;
        var show = $(this)[0].dataset.show == 'true' ? true : false;
        var buttonLabel = $(this)[0].dataset.buttonLabel;

        $(this).click(function () {
            if (buttonLabel === '等待平台确认') {
                $.alert("请等待平台确认后再查看");
            }else if (buttonLabel === '等待平台处理') {
                $.alert("等待平台处理后再查看");
            } else {
                var subsidyImage = 0;
                if (buttonLabel === '平台已处理' || buttonLabel === '查看面试信息') {
                    subsidyImage = 1;
                }
                confirmInterviewInfo(applyId, jobName, show, subsidyImage);
            }
        });
    });
}

// 下拉操作表之 等待平台确认
// 确认面试信息
function confirmInterviewInfo(applyId, jobName, show, subsidyImage) {
    var applyData = {};
    $("#interview-detail-layer").remove();
    var $interviewDetailLayer = $interviewDetailLayerTemplate.clone();
    // 性别：奇男偶女
    var sexNumber = $('#sex').val();
    if (sexNumber >= 0 && sexNumber % 2 == 1) {
        sexNumber = 1;
    } else if (sexNumber >= 0 && sexNumber % 2 == 0) {
        sexNumber = 0;
    } else {
        // 没有身份信息
        $.alert("无法识别性别");
        return;
    }
    $.post(bath + '/platformConfirmTime', {applyId: applyId}, function (interviewInfo) {
        // console.log(interviewInfo);

        if (interviewInfo.response == 'success') {
            if (!interviewInfo.data.platformReturnFee) {
                $.alert("等待平台确认价格");
                return;
            }

            // 岗位名称
            $interviewDetailLayer.find('.interview-job-name .interview-list-text').text(jobName);
            applyData.jobName = jobName;
            // 劳务确认时间
            var laborConfirmTime = (new Date(interviewInfo.data.laborConfirmTime)).format('yyyy年MM月dd日 ');
            $interviewDetailLayer.find('.interview-labor-confirm-time .interview-list-text').text(laborConfirmTime);
            applyData.laborConfirmTime = laborConfirmTime;

            // 小时工薪资
            var hourlyPay = 0;
            if (interviewInfo.data.jobType == 0 && interviewInfo.data.hourlyPay != 0) {
                hourlyPay = accDiv(interviewInfo.data.hourlyPay, 100);
                $interviewDetailLayer.find('.interview-hourly-pay .interview-list-text').text(hourlyPay);
                $interviewDetailLayer.find('.interview-hourly-pay').show();
            }
            applyData.hourlyPay = hourlyPay;

            // 平台补贴数据
            var platformReturnFee = interviewInfo.data.platformReturnFee;
            // 普通价，VIP价
            var currentPay = 0;
            var maxPay = 0;
            if (sexNumber == 1) {
                currentPay = accDiv(platformReturnFee.currentPayMan, 100);
                maxPay = accDiv(platformReturnFee.maxPayMan, 100);
            } else if (sexNumber == 0) {
                currentPay = accDiv(platformReturnFee.currentPayWoman, 100);
                maxPay = accDiv(platformReturnFee.maxPayWoman, 100);
            }
            applyData.currentPay = currentPay;
            applyData.maxPay = maxPay;
            // 返费类型
            applyData.type = platformReturnFee.returnFeeType;

            var payContent = '';
            if (platformReturnFee.returnFeeType == 0) {
                // 按小时补
                if (interviewInfo.data.jobType == 0) {
                    // 小时工
                    payContent = '牛职补足至：' + (hourlyPay + maxPay) + '元/小时';
                } else {
                    // 正式工
                    payContent = '除工资外牛职补：' + maxPay + '元/小时';
                }
                if (platformReturnFee.returnFeeDurationType) {
                    if (platformReturnFee.returnFeeDurationType == 1) {
                        // 补几个月
                        applyData.durationTime = '补' + platformReturnFee.returnFeeDurationTimes + '个月';
                    } else if (platformReturnFee.returnFeeDurationType == 2) {
                        // 截止日期
                        applyData.durationTime = (new Date(platformReturnFee.returnFeeDurationEndDate)).format('截止至 yyyy年MM月dd日');
                    } else if (platformReturnFee.returnFeeDurationType == 3) {
                        // 长期
                        applyData.durationTime = '长期补';
                    }
                }
            } else if (platformReturnFee.returnFeeType == 1) {
                // 按天补
                var _day = 0;
                for (var i = 0; i < platformReturnFee.returnFeeDetails.length; i++) {
                    // 最大打卡天数
                    var day = platformReturnFee.returnFeeDetails[i].returnFeeDetailFullTime;
                    if (day > _day){
                        _day = day;
                    }
                }
                applyData.day = _day;
                payContent = '除工资外牛职补：'+ maxPay + '元';
            } else if (platformReturnFee && platformReturnFee.returnFeeType == 2) {
                // 按月补
                payContent = '除工资外牛职补：'+ maxPay + '元/月';
            }
            // 补贴金额
            $interviewDetailLayer.find('.interview-subsidy .interview-list-text').text(maxPay);

            if (platformReturnFee.fare > 0) {
                $interviewDetailLayer.find('.interview-fare .interview-list-text').text(accDiv(platformReturnFee.fare, 100));
                $interviewDetailLayer.find('.interview-fare').show();
                applyData.fare = accDiv(platformReturnFee.fare, 100);
            }
            // 在职状态
            $interviewDetailLayer.find('.interview-work-status .interview-list-text').text(personStatus(interviewInfo.data.personStatus));

            if (platformReturnFee.returnFeeRemark) {
                $interviewDetailLayer.find('.interview-remark .interview-list-text').text(platformReturnFee.returnFeeRemark);
                $interviewDetailLayer.find('.interview-remark').show();
                applyData.remark = platformReturnFee.returnFeeRemark;
            } else {
                applyData.remark = '无';
            }
            if (show) {
                if (subsidyImage == 1) {
                    generate(applyData);
                } else {
                    $interviewDetailLayer.find('.interview-close').css('display', 'flex');
                    $interviewDetailLayer.find('#interview-confirm').click(function () {
                        $('#interview-detail-layer-wrapper').hide();
                        $('#interview-detail-layer-dimmer').hide();
                    });

                    $('#interview-detail-layer-wrapper').append($interviewDetailLayer);
                    $('#interview-detail-layer-wrapper').show();
                    $('#interview-detail-layer-dimmer').show();
                }
            } else { // 确认
                $interviewDetailLayer.find('.interview-unconfirmed').css('display', 'flex');
                $interviewDetailLayer.find('#interview-false').click(function () {
                    $('#interview-detail-layer-wrapper').hide();
                    $('#interview-detail-layer-dimmer').hide();
                    feedbackChoose(applyId, jobName, (new Date(interviewInfo.data.laborConfirmTime)).format('yyyy-MM-dd '), maxPay);
                });
                $interviewDetailLayer.find('#interview-true').click(function () {
                    $('#interview-detail-layer-wrapper').hide();
                    $('#interview-detail-layer-dimmer').hide();
                    $.post(bath + '/interviewTimeRight', {applyId: applyId}, function (data1) {
                        if (data1.response == 'success' && data1.data == 1) {
                            generate(applyData);
                        } else {
                            layer.open({
                                content: '确认失败！'
                                , skin: 'msg'
                                , time: 2 //2秒后自动关闭
                            });
                        }
                    }, 'json');
                });
                $('#interview-detail-layer-wrapper').append($interviewDetailLayer);
                $('#interview-detail-layer-wrapper').show();
                $('#interview-detail-layer-dimmer').show();
            }
        }
    }, 'json');
}

$('#interview-detail-layer-dimmer').click(function () {
    $('#interview-detail-layer-dimmer').hide();
    $('#interview-detail-layer-wrapper').hide();
});


// 确认面试信息内部 操作表
function feedbackChoose(applyId, jobName, interviewTime, interviewSubsidy) {
    var buttons1 = [
        {
            text: '请选择',
            label: true
        },
        {
            text: '面试时间错误',
            onClick: function () {
                interview(applyId, jobName, interviewTime, interviewSubsidy, 'date-error');
            }
        },
        {
            text: '补贴金额错误',
            onClick: function () {
                interview(applyId, jobName, interviewTime, interviewSubsidy, 'subsidy-error');
            }
        },
        {
            text: '面试时间与补贴金额错误',
            onClick: function () {
                interview(applyId, jobName, interviewTime, interviewSubsidy, 'all-error');
            }
        }
    ];
    var buttons2 = [
        {
            text: '取消',
            bg: 'danger'
        }
    ];
    var groups = [buttons1, buttons2];
    $.actions(groups);
}

// 确认面试信息内部操作表 错误选项
function interview(applyId, jobName, interviewTime, interviewSubsidy, feedback) {
    $('#feedback-wrapper .platform-job-name').text(jobName);
    $('#feedback-wrapper .platform-time').text(interviewTime);
    $('#feedback-wrapper .platform-subsidy').text(interviewSubsidy);
    $('#feedback-apply-id').val(applyId);

    $('#interviewDate').val(today_str);
    $('#interviewSubsidy').val('');
    if (feedback == 'date-error') {
        $('#interview-error-tag').val(1);
        $('.customer-date').show();
        $('.customer-subsidy').hide();
    } else if (feedback == 'subsidy-error') {
        $('#interview-error-tag').val(2);
        $('.customer-date').hide();
        $('.customer-subsidy').show();
    } else {
        $('#interview-error-tag').val(3);
        $('.customer-date').show();
        $('.customer-subsidy').show();
    }
    $('#feedback-wrapper').show();
}

// 修改面试信息 内部的提交按钮
$('#feedback-confirm-btn').on('click', function () {
    $.ajax({
        url: bath + "/confirmInterview",
        type: "post",
        dataType: "json",
        data: $('#confirm-interview-form').serializeArray(),
        success: function (data) {
            if (data.response == "success") {
                $.alert('确认面试时间成功！', function () {
                    location.reload();
                });
            } else {
                $.alert(data.data.text || '确认异常，请重试！');
            }
        }
    });
});
$('#feedback-cancel-btn').click(function () {
    $('#feedback-wrapper').hide();
});


// 状态
function personStatus(status) {
    switch (status) {
        case 0:
            return '在职';
        case 1:
            return '离职';
        case 2:
            return '用户取消';
        default:
            return '--';
    }
}

function formatDate(date, pattern) {
    if (!date) {
        return "--";
    }
    var date = new Date(date);
    if (date == "Invalid Date") {
        return "--";
    }
    var fm_year = date.getFullYear();
    var fm_month = date.getMonth() + 1;
    var fm_day = date.getDate();
    if (fm_month < 10) {
        fm_month = "0" + fm_month;
    }
    if (fm_day < 10) {
        fm_day = "0" + fm_day;
    }
    var str = pattern.replace("yyyy", fm_year).replace("MM", fm_month).replace("dd", fm_day);
    return str;
}

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null)
        return decodeURI(r[2]);
    return null;
}

$(function () {
    var queryApplyId = getQueryString('applyId');

    $('.my-subsidy').each(function () {
        var applyId = $(this)[0].dataset.applyId;
        var jobName = $(this)[0].dataset.jobName;
        var advanceAmount = $(this)[0].dataset.advanceAmount;
        advanceAmount = advanceAmount / 100;
        var show = $(this)[0].dataset.show == 'true' ? true : false;
        var buttonLabel = $(this)[0].dataset.buttonLabel;

        if (queryApplyId == applyId) {
            if (buttonLabel === '等待平台确认') {
                $.alert("请等待平台确认后再查看");
            } else {
                var subsidyImage = 0;
                if (buttonLabel === '平台已处理' || buttonLabel === '查看面试信息') {
                    subsidyImage = 1;
                }
                confirmInterviewInfo(applyId, jobName, show, subsidyImage);
            }
        }
    });

    // 出名单
    myListNameBindClick();
    // 上传厂牌
    uploadWorkCardBindClick();
    // 入职薪资
    muSubsidyBindClick();
    // VIP高价
    vipShareBindClick();
    // 完善信息
    perfectInformationBindClick();
    // 取消报名
    cancelApplyBindClick();
});