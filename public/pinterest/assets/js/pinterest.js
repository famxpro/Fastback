function Pinterest(){
    var self= this;
    var _current_link = "";
    var _started = false;
    this.init= function(){
        if($(".pinterest-app").length > 0){
            self.optionPinterest();
            self.loadPreview();
        }
    };

    this.optionPinterest = function(){
        //Enable Schedule
        $(document).on("change", ".enable_pinterest_schedule", function(){
            _that = $(this);
            if(!_that.hasClass("checked")){
                $('.time_post').removeAttr('disabled');
                $('.btnPostNow').addClass("hide");
                $('.btnSchedulePost').removeClass("hide");
                _that.addClass('checked');
            }else{
                $('.time_post').attr('disabled', true);
                $('.btnPostNow').removeClass("hide");
                $('.btnSchedulePost').addClass("hide");
                _that.removeClass('checked');        
            }
            return false;
        });

        //Get Pinterest Media
        $(document).on("click", ".btnGetPinterestMedia", function(){
            _that    = $(this);
            _type    = _that.data("type");
            _media   = _that.data("media");
            _caption = _that.data("caption");

            //Select tab
            $(".schedule-pinterest-type .item[data-type="+_type+"]").trigger("click");

            //Set caption
            $(".post-message").data("emojioneArea").setText(_caption);

            //Add image
            if(_type == "carousel"){
                FileManager.type_select = 'multi';
            }else{
                FileManager.type_select = 'single';
            }

            for (var i = 0; i < _media.length; i++) {
                FileManager.saveFile(_media[i]);
            }

            //Hide modal
            $('#mainModal').modal('hide');
        });

        $(document).on("click", ".file-manager-list-images .item .close", function(){
            if($(".file-manager-list-images .item").length <= 0){
               self.defaultPreview();
            }
        });

        $(document).on("click", ".pinterest-app .btnPostNow", function(){
            _that = $(this);
            self.postNow(_that);
        });
    };

    this.postNow = function(_that){
        _form    = _that.closest("form");
        _action  = _form.attr("action");
        _data    = $("[name!='account[]']").serialize();
        _data    = _data + '&' + $.param({token:token});
        _item    = $(".list-account .item.active");
        _stop    = false;
        if(_item.length > 0){
            _id     = _item.first().find("input").val();
            _data   = _form.serialize();
            _data   = Main.removeParam("account%5B%5D", _data);
            _data   = _data + '&' + $.param({token:token , 'account[]' :_id});
        }else{
            if(_started == true){
                _started = false;
                Main.statusCardOverplay("hide");
                return false;
            }
        }

        Main.statusOverplay("hide");
        Main.statusCardOverplay("show");

        Main.ajax_post(_that, _action, _data, function(_result){
            Main.statusOverplay("show");
            _started = true;

            //Remove mark item
            if(_result.stop == undefined){
                _item.first().trigger("click");
                setTimeout(function(){
                    $(".btnPostNow").trigger("click");
                }, 500);
            }else{
                Main.statusCardOverplay("hide");
            }
        });
    };

    this.loadPreview = function(){
        //Review content
        if($(".post-message").length > 0){
            $(".post-message").data("emojioneArea").on("keyup", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".post-message").data("emojioneArea").on("change", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".post-message").data("emojioneArea").on("emojibtn.click", function(editor) {
                _data = $(".emojionearea-editor").html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });
        }

        //Load Preview
        setInterval(function(){ 
            _media = $(".file-manager-list-images .item");
            if(_media.length > 0){
                _link     = _media.find("input").val();
                _link_arr = _link.split(".");
                if(_current_link != _link){
                    if(_link_arr[_link_arr.length - 1] == "mp4"){
                        $(".preview-pinterest-photo .preview-image").html('<video src="'+_link+'" playsinline="" autoplay="" muted="" loop=""></video>');
                        $(".preview-pinterest-photo .preview-image").css({"background-image": "none"});
                    }else{
                        $(".preview-pinterest-photo .preview-image").css({"background-image": "url("+_link+")"});
                        $(".preview-pinterest-photo .preview-image").html('');
                    }
                    _current_link = _link;
                }
            }
        }, 1500);
    };

    this.defaultPreview = function(){
        $(".preview-pinterest-photo .preview-image").css({"background-image": "none"}).html('');
    };
}

Pinterest= new Pinterest();
$(function(){
    Pinterest.init();
});
