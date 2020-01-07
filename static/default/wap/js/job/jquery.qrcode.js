var xQRCode = (function () {
    var _e = {};
    _e.generateQRCode = function (options) {
        if (typeof options === 'string') {
            options = { qrcode: { text: options } };
        } else if (typeof options !== 'object') {
            options = {};
        }

        options = $.extend({
            elid: '',
            qrcode: {
                width: 300,
                height: 300,
                padding: 40,
                typeNumber: -1,
                correctLevel: QRErrorCorrectLevel.H,
                background: "#ffffff",
                foreground: "#000000",
                position: {x: 150, y: 320}
            },
            width: 600,
            height: 800,
            mainImage: '/static/images/qrcode.jpg',
            texts: [
                {
                    text: '牛职工作网',
                    color: '#000',
                    fontSize: '30px',
                    fontWeight: 'bold',
                    textAlign: 'center',
                    textBaseline: 'Alphabetic',
                    x: 300,
                    y: 650
                }
            ],
            images: [],
            defaultFontSize: '30px',
            defaultFontWeight: 'bold',
            defaultTextAlign: 'center',
            defaultTextBaseline: 'Alphabetic',
        }, options);


        var that = document.getElementById(options.elid);
        var canvas = document.createElement('canvas');
        canvas.width = options.width;
        canvas.height = options.height;
        that.appendChild(canvas);
        // $(canvas).appendTo(that);
        // var cvs = document.getElementsByTagName('canvas')[0];
        var ctx = canvas.getContext('2d');

        // 生成二维码
        function MakeQRCode() {
            var qrcode = new QRCode(options.qrcode.typeNumber, options.qrcode.correctLevel); // 实例化 QRCode()
            qrcode.addData(options.qrcode.text); // 传递文字
            qrcode.make(); // 调用 make
            // 生成二维码
            var tileW = (options.qrcode.width - options.qrcode.padding * 2) / qrcode.getModuleCount();
            var tileH = (options.qrcode.height - options.qrcode.padding * 2) / qrcode.getModuleCount();
            ctx.fillStyle = options.qrcode.background;
            ctx.fillRect(options.qrcode.position.x, options.qrcode.position.y, options.qrcode.width, options.qrcode.height);
            ctx.fillStyle = options.qrcode.foreground;
            for (var row = 0; row < qrcode.getModuleCount(); row++) {
                for (var col = 0; col < qrcode.getModuleCount(); col++) {
                    ctx.fillStyle = qrcode.isDark(row, col) ? options.qrcode.foreground : options.qrcode.background;
                    ctx.fillRect(Math.round(col * tileW) + options.qrcode.padding + options.qrcode.position.x, Math.round(row * tileH) + options.qrcode.padding + options.qrcode.position.y, Math.ceil(tileW), Math.ceil(tileH));
                }
            }
        }
        function canvas2img() {
            if ($(that).find('canvas')[0]) {
                var imgD = $(that).find('canvas')[0].toDataURL('image/png');
                if (imgD && imgD.length > 9) {
                    if (options.flag) {
                        $(that).find('canvas').css({width: '100%', display: 'inline-block'})
                        $(that).find('img').prop('src', imgD).css({position: 'absolute', opacity: 0}).show();
                    } else {
                        $(that).find('canvas').hide();
                        $(that).find('img').prop('src', imgD).show();
                    }
                    options.callback && options.callback();
                }
            }
        }

        // 如果没有图片那么只生成二维码
        if(!options.mainImage){
            MakeQRCode();
            canvas2img();
            return;
        }
        // 绘制文字
        function drawText(obj) {
            if (obj) {
                ctx.fillStyle = obj.color;
                ctx.font = (obj.fontWeight || options.defaultFontWeight) + " " + (obj.fontSize || options.defaultFontSize) + " 微软雅黑";
                ctx.textAlign = obj.textAlign || options.defaultTextAlign;
                ctx.textBaseline = obj.textBaseline || options.defaultTextBaseline;
                if(obj.isWrap) {
                    // 如果文字要求换行
                    var txt = obj.text.split('');
                    var maxshow = 17; // 最大零界点
                    var row = Math.ceil(txt.length / maxshow); // 几行
                    var x = obj.x;
                    var y = obj.y;
                    var k = 0;
                    for(var i=0;i<row;i++) {
                        for(var j=0;j<17&&txt[k];j++) {
                            ctx.fillText(txt[k], x+19*j, y+(i*20));
                            k++;
                        }
                    }
                }else {
                    ctx.fillText(obj.text, obj.x, obj.y);
                }
            }
        }

        // 加载 主图片
        function drawMainImage() {
            var image = new Image();
            image.src = options.mainImage;
            image.onload = function () { // 主图片加载完毕 绘制次图片
                ctx.drawImage(image, 0, 0, options.width, options.height);
                drawText(); // 绘制 用户名字
                MakeQRCode(); // 生成二维码
                canvas2img(); // 绘制成 img
                if (options.images) {
                    for (var i = 0; i < options.images.length; i++) {
                        if (options.images[i].r) {
                            circleImg(options.images[i]);
                        } else {
                            load(options.images[i]);
                        }
                    }
                }
                // 这里因为文字先绘制 图片后加载 所以图片会覆盖文字
                if (options.texts.length) {
                    for (var j = 0; j < options.texts.length; j++) {
                        drawText(options.texts[j]);
                    }
                    canvas2img();
                }

                function load(obj) {
                    var x = obj.x, y = obj.y, w = obj.w, h = obj.h;
                    var img = new Image();
                    img.onload = function () {
                        ctx.drawImage(img, x, y, w, h);
                        canvas2img();
                    };
                    img.src = obj.image;
                }

                function circleImg(obj) {

                    var d =2 * obj.r;
                    var cx = obj.x + obj.r;
                    var cy = obj.y + obj.r;
                    var img = new Image();
                    img.onload = function() {
                        ctx.save();
                        ctx.strokeStyle = '#ff0000';
                        ctx.arc(cx, cy, obj.r, 0, 2 * Math.PI, false);
                        ctx.stroke();
                        ctx.clip();
                        ctx.drawImage(img, obj.x, obj.y, obj.w, obj.h);
                        ctx.restore();
                        ctx.strokeStyle = '#ffffff';
                        ctx.lineWidth = 4;
                        ctx.arc(cx, cy, obj.r, 0, 2 * Math.PI, false);
                        ctx.stroke();
                        canvas2img();
                    }
                    img.src = obj.image;
                }
            };
        }
        drawMainImage();
    };
    return _e;
})();

!window.xQRCode && (window.xQRCode = xQRCode);
