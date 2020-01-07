var bath = $("#bath").val();
var username = $('#customerUsername').val();
var today_str = (new Date()).format('yyyy-MM-dd');
var indexLayer;

// 已折叠的岗位申请
var $applyItemTemplate = $(".apply-item-template").clone().removeClass("apply-item-template").removeClass("nz-hide");
// 已折叠的岗位申请里面的内容
var $contentItemTextTemplate = $(".content-item-text-template").clone().removeClass("content-item-text-template").removeClass("nz-hide");

$(window).off().on('hashchange',function () {
    nzHash();
});
nzHash();
function nzHash() {
    var hashApplyId;
    if(!location.hash) {
        return;
    }
    var hash = location.hash.match(/applyId=\d{4,5}/g);
    if(!hash){
        return;
    }else {
        hashApplyId = hash[0].split('=')[1];
    }
    var arr = [];
    var filter;
    $('.index-items li').forEach(function (value,index) {
        var moreAction = $(value).find('.more-action')[0];
        var show = moreAction.dataset.show == 'true' ? true : false;
        var buttonLabel = moreAction.dataset.buttonLabel;
        var subsidyImage = (buttonLabel === '平台已处理' || buttonLabel === '查看面试信息') ? 1 : 0;
        arr.push({
            applyId : moreAction.dataset.applyId,
            jobName : moreAction.dataset.jobName,
            show : show,
            subsidyImage : subsidyImage
        });
    });
    filter = arr.filter(function (value,index) {
        return value.applyId === hashApplyId;
    });
    if(filter.length) {
        confirmInterviewInfo(filter[0].applyId, filter[0].jobName, filter[0].show, filter[0].subsidyImage);
    }else {
        layer.close(indexLayer);
    }
}

/**
 * 查看岗位申请详情
 */
function showJobDetailBindClick(){
    $(".show-job-detail.no-click-event").each(function () {
        $(this).removeClass("no-click-event");

        var applyId = $(this)[0].dataset.applyId;
        $(this).click(function () {
            location.href = bath + "/applyDetail?applyId=" + applyId;
        });
    });
}

/**
 * 初始化，绑定点击事件
 */
function initBindClickEvent(){
    // 查看岗位申请详情
    showJobDetailBindClick();

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
}
initBindClickEvent();

$.init();




// // 下拉操作表之 借支功能
// function borrow(applyId) {
//     $.post(bath + '/advance?action=customer', {applyId: applyId}, function (data) {
//         if (data.response === 'success') {
//             $.alert('借支申请已提交', function () {
//                 window.location.reload();
//             });
//         } else {
//             $.alert(data.data.text, function () {
//                 window.location.reload();
//             });
//         }
//     });
// }



$(".fold-btn").click(function () {
    var foldStatus = $(this).dataset().foldStatus;
    if (foldStatus == 1) {
        $(this).attr("data-fold-status", "0");
        $(this).find("span").text("展开全部工作");
        $(this).find("img").attr("src", "/static/images/apply-down.png");

        // 已展开，收起
        $(".negative-apply").hide().remove();
        $(".click-loading-more").addClass("nz-hide").unbind()
    } else {
        $(this).attr("data-fold-status", "1");
        $(this).find("span").text("收起全部工作");
        $(this).find("img").attr("src", "/static/images/apply-up.png");

        // 展开
        unfoldNegativeApply(1);
    }
});

/**
 * 加载消极的报名记录
 *
 * @param pageNumber
 */
function unfoldNegativeApply(pageNumber) {
    $.post(
        bath + "/getNegativeApply"
        , {
            pageNumber: pageNumber,
            pageSize: 20
        }, function (data) {
            // console.log(data);
            if (data.response == "success"){
                // 性别
                var sex = data.data.sex;

                for (var i =0; i < data.data.applyPage.content.length; i++){
                    // console.log(i);

                    var $applyItem = $applyItemTemplate.clone();
                    var jobApply = data.data.applyPage.content[i];
                    // 企业图片
                    var imageUrl = "";
                    if (!jobApply.job.mediationId && jobApply.job.company.companyImageList){
                        // 平台岗位，并且有企业图片
                        if (jobApply.job.company.companyImageList.length > 0){
                            imageUrl = jobApply.job.company.companyImageList[0].imageCosSourceUrl.replace('http://niuzhigongzuo-1251799515.cossh.myqcloud.com', 'https://cos.niuzhigongzuo.com');
                        } else {
                            imageUrl = "/static/images/jobJpg.jpg";
                        }
                    } else if (jobApply.job.mediationId && jobApply.job.mediationId > 0) {
                        // 自营岗位
                        var imageObj = eval("(" + jobApply.job.jobImage + ")");
                        imageUrl = imageObj[0];
                    } else {
                        // 默认图片
                        imageUrl = "/static/images/jobJpg.jpg";
                    }
                    $applyItem.find(".content-img img").attr("src", imageUrl);

                    // 标题
                    var jobTitle = "";
                    if (jobApply.job.mediationId){
                        // 自营岗位
                        jobTitle = jobApply.job.jobContent;
                    } else {
                        // 平台岗位
                        jobTitle = jobApply.job.jobName;
                    }
                    $applyItem.find(".content-item-title").html(jobTitle);
                    
                    if (jobApply.job.mediationId) {
                        // 自营岗位
                        var $contentItemText = $contentItemTextTemplate.clone();
                        $contentItemText.find(".text-title").html("服务门店");
                        $contentItemText.find(".text-text").html(jobApply.mediationName);
                        // 添加至标题后面
                        $applyItem.find(".content-item-title").after($contentItemText);

                        var textTitle = "";
                        var textContent = "";
                        if (jobApply.laborStatus && jobApply.laborStatus == 1){
                            textTitle = "入职时间";
                            textContent = new Date(jobApply.laborConfirmTime).format("yyyy年MM月dd日");
                        } else if (jobApply.mediationStatus == 1){
                            textTitle = "发车时间";
                            textContent = new Date(jobApply.confirmTime).format("yyyy年MM月dd日");
                        } else {
                            textTitle = "报名时间";
                            textContent = new Date(jobApply.applyCreateTime).format("yyyy年MM月dd日");
                        }
                        $contentItemText = $contentItemTextTemplate.clone();
                        $contentItemText.find(".text-title").html(textTitle);
                        $contentItemText.find(".text-text").html(textContent);
                        // 添加至标题后面
                        $applyItem.find(".content-item-title").after($contentItemText);
                    } else {
                        // 平台岗位
                        // 报名时政策
                        var subsidy = "--";
                        // 返费类型
                        var returnFeeType = 1;
                        if (jobApply.applyPlatformReturnFee){
                            returnFeeType = jobApply.applyPlatformReturnFee.returnFeeType;
                            var subsidyMale = jobApply.applyPlatformReturnFee.maxPayMan + jobApply.applyPlatformReturnFee.hourlyPay;
                            var subsidyFemale = jobApply.applyPlatformReturnFee.maxPayWoman + jobApply.applyPlatformReturnFee.hourlyPay;
                            if (sex == 0){
                                subsidy = accDiv(subsidyFemale, 100);
                            } else if (sex == 1){
                                subsidy = accDiv(subsidyMale, 100);
                            } else {
                                if (subsidyMale > subsidyFemale){
                                    subsidy = accDiv(subsidyMale, 100);
                                } else {
                                    subsidy = accDiv(subsidyFemale, 100);
                                }
                            }
                        }
                        var $contentItemText = $contentItemTextTemplate.clone();
                        $contentItemText.find(".text-title").html("报名政策");
                        $contentItemText.find(".text-text .price").html(subsidy);
                        if (returnFeeType == 0){
                            $contentItemText.find(".text-text .price-unit").html("元/小时");
                        } else {
                            $contentItemText.find(".text-text .price-unit").html("元");
                        }
                        // 政策添加至标题后面
                        $applyItem.find(".content-item-title").after($contentItemText);
                        // 查看工作详情
                        $applyItem.find(".show-job-detail").removeClass("nz-hide").attr("data-apply-id", jobApply.applyId);
                    }

                    // 状态图标
                    if (jobApply.personStatus && jobApply.personStatus == 0) {
                        $applyItem.find(".item-title div").addClass("working").html("工作中");
                    } else if (jobApply.personStatus && jobApply.personStatus == 1){
                        $applyItem.find(".item-title div").addClass("leave").html("已离职");
                    } else if (jobApply.mediationStatus == 2
                        || jobApply.laborStatus && jobApply.laborStatus == 2){
                        $applyItem.find(".item-title div").addClass("delete").html("已剔除");
                    } else if (jobApply.personStatus && jobApply.personStatus == 2){
                        $applyItem.find(".item-title div").addClass("cancel").html("已取消");
                    } else if (jobApply.mediationStatus == 1){
                        $applyItem.find(".item-title div").addClass("start").html("已发车");
                    } else {
                        $applyItem.find(".item-title div").addClass("apply").html("已报名");
                    }

                    if (!jobApply.job.mediationId){
                        // 平台岗位
                        $applyItem.find(".item-detail").removeClass("nz-hide");
                        $applyItem.find(".factory-name div").eq(1).html(jobApply.job.companyName);
                        $applyItem.find(".service-mediation div").eq(1).html(jobApply.mediationName);
                        if (jobApply.laborStatus && jobApply.laborStatus == 1){
                            $applyItem.find(".apply-time div").eq(0).html("入职时间");
                            $applyItem.find(".apply-time div").eq(1).html(new Date(jobApply.laborConfirmTime).format("yyyy年MM月dd日"));
                        } else if (jobApply.mediationStatus == 1){
                            $applyItem.find(".apply-time div").eq(0).html("发车时间");
                            $applyItem.find(".apply-time div").eq(1).html(new Date(jobApply.confirmTime).format("yyyy年MM月dd日"));
                        } else {
                            $applyItem.find(".apply-time div").eq(0).html("报名时间");
                            $applyItem.find(".apply-time div").eq(1).html(new Date(jobApply.applyCreateTime).format("yyyy年MM月dd日"));
                        }
                    }

                    // 底部操作按钮
                    if (jobApply.job.mediationId){
                        // 自营岗位
                        // 完善信息
                        $applyItem.find(".perfect-information").removeClass("nz-hide");
                        if (jobApply.mediationStatus == 0 && !jobApply.personStatus) {
                            // 取消报名
                            $applyItem.find(".cancel-apply").removeClass("nz-hide").attr("data-job-id", jobApply.job.jobId);
                        }
                    } else {
                        // 平台岗位
                        if (jobApply.mediationStatus == 0 && !jobApply.personStatus
                            || jobApply.mediationStatus == 3
                            || jobApply.laborStatus && jobApply.laborStatus == 0
                            || jobApply.personStatus && jobApply.personStatus == 0) {
                            // VIP高价
                            $applyItem.find(".vip-share").removeClass("nz-hide").attr("data-invite", jobApply.invite);
                        }
                        if (jobApply.laborStatus && jobApply.laborStatus == 1) {
                            if (jobApply.returnFeeIdPlatform) {
                                // 入职薪资
                                if (jobApply.personConfirmTime && jobApply.customerConfirmReturnFee == 2 && jobApply.customerConfirmReturnFeeStatus != 2) {
                                    $applyItem
                                        .find(".my-subsidy")
                                        .removeClass("nz-hide")
                                        .attr("data-apply-id", jobApply.applyId)
                                        .attr("data-advance-amount", jobApply.job.advanceAmount)
                                        .attr("data-job-name", jobApply.job.jobName)
                                        .attr("data-show", true)
                                        .attr("data-button-label", "等待平台处理");
                                } else if (jobApply.personConfirmTime && jobApply.customerConfirmReturnFee == 2 && jobApply.customerConfirmReturnFeeStatus == 2) {
                                    $applyItem
                                        .find(".my-subsidy")
                                        .removeClass("nz-hide")
                                        .attr("data-apply-id", jobApply.applyId)
                                        .attr("data-advance-amount", jobApply.job.advanceAmount)
                                        .attr("data-job-name", jobApply.job.jobName)
                                        .attr("data-show", true)
                                        .attr("data-button-label", "平台已处理");
                                } else if (jobApply.personConfirmTime) {
                                    $applyItem
                                        .find(".my-subsidy")
                                        .removeClass("nz-hide")
                                        .attr("data-apply-id", jobApply.applyId)
                                        .attr("data-advance-amount", jobApply.job.advanceAmount)
                                        .attr("data-job-name", jobApply.job.jobName)
                                        .attr("data-show", true)
                                        .attr("data-button-label", "查看面试信息");
                                } else {
                                    $applyItem
                                        .find(".my-subsidy")
                                        .removeClass("nz-hide")
                                        .attr("data-apply-id", jobApply.applyId)
                                        .attr("data-advance-amount", jobApply.job.advanceAmount)
                                        .attr("data-job-name", jobApply.job.jobName)
                                        .attr("data-show", false)
                                        .attr("data-button-label", "确认面试信息 ");
                                }
                            } else {
                                $applyItem
                                    .find(".my-subsidy")
                                    .removeClass("nz-hide")
                                    .attr("data-apply-id", jobApply.applyId)
                                    .attr("data-advance-amount", jobApply.job.advanceAmount)
                                    .attr("data-job-name", jobApply.job.jobName)
                                    .attr("data-show", false)
                                    .attr("data-button-label", "等待平台处理");
                            }
                            // 传厂牌
                            $applyItem.find(".upload-work-card").removeClass("nz-hide").attr("data-apply-id", jobApply.applyId);
                            // 出名单
                            $applyItem.find(".my-list-name").removeClass("nz-hide").attr("data-apply-id", jobApply.applyId);
                        } else if (jobApply.mediationStatus == 1) {
                            // 传厂牌
                            $applyItem.find(".upload-work-card").removeClass("nz-hide").attr("data-apply-id", jobApply.applyId);
                            // 完善信息
                            $applyItem.find(".perfect-information").removeClass("nz-hide");
                        } else if (jobApply.mediationStatus == 0 && !jobApply.personStatus) {
                            // 完善信息
                            $applyItem.find(".perfect-information").removeClass("nz-hide");
                            // 取消报名
                            $applyItem.find(".cancel-apply").removeClass("nz-hide").attr("data-job-id", jobApply.job.jobId);
                        } else {
                            // 完善信息
                            $applyItem.find(".perfect-information").removeClass("nz-hide");
                        }
                    }

                    $(".negative-apply-item-list").append($applyItem);
                    // 绑定点击事件
                    initBindClickEvent();
                }

                var currentPageNumber = data.data.applyPage.number;
                // 判断是否显示加载更多按钮
                if (data.data.applyPage.totalPages > currentPageNumber) {
                    // 总页数大于当前页码，继续显示
                    $(".click-loading-more").removeClass("nz-hide").unbind().one("click", function () {
                        // 解绑所有事件，并绑定一次点击事件
                        unfoldNegativeApply(++currentPageNumber);
                    });
                } else {
                    $(".click-loading-more").addClass("nz-hide").unbind();
                }
            } else {
                $.alert(data.data.text || "查询失败");
            }
        }
    );
}