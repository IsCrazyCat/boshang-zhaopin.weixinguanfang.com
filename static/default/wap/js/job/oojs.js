'use strict';

Date.prototype.autoFormat = function() {
    if(this == 'Invalid Date') {
        return '17/12/30前';
    }
    var currentDate = new Date(),
        year = this.getFullYear(),
        month = this.getMonth() + 1,
        day = this.getDate(),
        hour = this.getHours(),
        minute = this.getMinutes(),
        second = this.getSeconds(),
        curentYear = currentDate.getFullYear(),
        currentMonth = currentDate.getMonth() + 1,
        currentDay = currentDate.getDate(),
        currentHour = currentDate.getHours(),
        currentMinute = currentDate.getMinutes(),
        currentSecond = currentDate.getSeconds(),
        timeStr;
    minute = minute < 10 ? ('0' + minute) : minute;
    if(year < curentYear) {
        timeStr = (year % 100) + '/' + month + '/' + day + ' ' + hour + ':' + minute;
    } else {
        var pastTime = currentDate - this,
            pastHour = pastTime / 3600000;

        if(pastTime <= currentHour * 3600000 + currentMinute * 60000 + currentSecond * 1000) {
            timeStr = '今天 ';
        } else if(pastTime <= currentHour * 3600000 + currentMinute * 60000 + currentSecond * 1000 + 24*3600000) {
            timeStr = '昨天  ' + hour + ':' + minute;
        } else if(pastTime <= currentHour * 3600000 + currentMinute * 60000 + currentSecond * 1000 + 48*3600000) {
            timeStr = '前天  ' + hour + ':' + minute;
        } else {
            timeStr = '今年 ' + month + '月' + day + '日 ' + hour + ':' + minute;
        }
    }
    return timeStr;
}

Date.prototype.format = function(pattern) {
    if(this == 'Invalid Date') {
        return '时间错误';
    }
    var year = this.getFullYear(),
        month = this.getMonth() + 1,
        day = this.getDate(),
        hour = this.getHours(),
        minute = this.getMinutes(),
        second = this.getSeconds(),
        str = pattern;
    year = year < 10 ? ('0' + year) : year;
    month = month < 10 ? ('0' + month) : month;
    day = day < 10 ? ('0' + day) : day;
    hour = hour < 10 ? ('0' + hour) : hour;
    minute = minute < 10 ? ('0' + minute) : minute;
    second = second < 10 ? ('0' + second) : second;

    str = str.replace('yyyy', year)
        .replace('yy', year%100)
        .replace('MM', month)
        .replace('dd', day)
        .replace('HH', hour)
        .replace('mm', minute)
        .replace('ss', second);
    return str;
}

/**
 * utf8 utf16转码，解决中文问题
 */
String.prototype.utf16to8 = function () {
    var out, i, len, c;
    out = '';
    len = this.length;
    for (i = 0; i < len; i++) {
        c = this.charCodeAt(i);
        if ((c >= 0x0001) && (c <= 0x007F)) {
            out += this.charAt(i);
        } else if (c > 0x07FF) {
            out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
            out += String.fromCharCode(0x80 | ((c >> 6) & 0x3F));
            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        } else {
            out += String.fromCharCode(0xC0 | ((c >> 6) & 0x1F));
            out += String.fromCharCode(0x80 | ((c >> 0) & 0x3F));
        }
    }
    return out;
}

String.prototype.utf8to16 = function () {
    var out, i, len, c;
    var char2, char3;
    out = '';
    len = this.length;
    i = 0;
    while (i < len) {
        c = this.charCodeAt(i++);
        switch (c >> 4) {

            case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
            // 0xxxxxxx
            out += this.charAt(i - 1);
            break;
            case 12: case 13:
            // 110x xxxx   10xx xxxx
            char2 = this.charCodeAt(i++);
            out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
            break;
            case 14:
                // 1110 xxxx  10xx xxxx  10xx xxxx
                char2 = this.charCodeAt(i++);
                char3 = this.charCodeAt(i++);
                out += String.fromCharCode(((c & 0x0F) << 12) |
                    ((char2 & 0x3F) << 6) |
                    ((char3 & 0x3F) << 0));
                break;
        }
    }
    return out;
}


// 除法
function accDiv(arg1,arg2) {
    var t1=0,t2=0,r1,r2;
    try{t1=arg1.toString().split(".")[1].length}catch(e){}
    try{t2=arg2.toString().split(".")[1].length}catch(e){}
    r1=Number(arg1.toString().replace(".",""))
    r2=Number(arg2.toString().replace(".",""))
    return accMul((r1/r2),Math.pow(10,t2-t1));
}
//乘法
function accMul(arg1,arg2) {
    var m=0,s1=arg1.toString(),s2=arg2.toString();
    try{m+=s1.split(".")[1].length}catch(e){}
    try{m+=s2.split(".")[1].length}catch(e){}
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
}
//加法
function accAdd(arg1,arg2) {
    var r1,r2,m;
    try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
    try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
    m=Math.pow(10,Math.max(r1,r2));
    return accDiv((accMul(arg1, m) + accMul(arg2, m)), m)
}
//减法
function Subtr(arg1,arg2) {
    var r1,r2,m,n;
    try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
    try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
    m=Math.pow(10,Math.max(r1,r2));
    n=(r1 >= r2)?r1:r2;
    return (accDiv((accMul(arg1,m)-accMul(arg2,m)),m)).toFixed(n);
}

String.prototype.stringLimit = function(limit, display) {
    display = display || '…';
    if (this.length > limit) {
        return this.substr(0, limit) + display;
    }
    return this;
}