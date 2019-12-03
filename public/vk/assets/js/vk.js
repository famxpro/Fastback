function VK(){
    var self= this;
    var _current_link = "";
    var _current_link_photos = [];
    var _started = false;
    this.init= function(){
        if($(".vk-app").length > 0){
            self.optionVk();
            self.loadPreview();
        }
    };

    this.optionVk = function(){
        /*
        * Select type post
        */
        if($("[name='default_type']").val() != undefined){
            _default_type = $("[name='default_type']").val();
            console.log(_default_type);
            setTimeout(function(){
                _that = $(".schedule-vk-type .item[data-type='"+_default_type+"']");
                self.load_form(_that, false);
                if(_default_type == "link"){
                    $(".vk-app input[name='link']").change();
                }
            }, 50);
        }

        $(document).on("click", ".schedule-vk-type .item", function(){
            self.load_form($(this), true);
        });

        //Enable Schedule
        $(document).on("change", ".enable_vk_schedule", function(){
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

        $(document).on("click", ".vk-app .file-manager-list-images .item .close", function(){
            if($(".file-manager-list-images .item").length <= 0){
               self.defaultPreview();
            }
        });

        $(document).on("click", ".vk-app .btnPostNow", function(){
            _that = $(this);
            self.postNow(_that);
        });
    };

    this.load_form = function(_that, _load_default_preview){
        _type = _that.data("type");
        _that.addClass("active");
        _that.siblings().removeClass("active");
        _that.siblings().find("input").removeAttr('checked');
        _that.find("input").attr('checked','checked');

        if(_type == "text" || _type == "link"){
            $(".image-manage").hide();
        }else{
            $(".image-manage").show();
        }

        if(_type != "link"){
            $(".vk-text").removeClass("max2");
            $("#link").removeClass("active");
            $("#link input").val("");
        }else{
            $("#link").addClass("active");
            $(".vk-text").addClass("max2");
        }

        if(_type == "text"){
            $(".vk-text").addClass("max");
        }else{
            $(".vk-text").removeClass("max");
        }

        $(".preview-vk").addClass("hide");
        $(".preview-vk-"+_type).removeClass("hide");

        if(_load_default_preview){
            self.defaultPreview();
        }
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
        //Load link
        $(document).on("change", ".vk-app input[name='link']", function(){
            _that   = $(this);
            _link   = _that.val();
            _action = PATH+"vk/post/ajax_get_link";
            _data   = $.param({token:token, link: _link});

            if(_link == ""){
                return false;
            }

            $(".vk-app .preview-vk-link .image").removeAttr("style");
            $(".vk-app .preview-vk-link .title").html('<div class="line-no-text"></div>');
            $(".vk-app .preview-vk-link .desc").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text"></div>');
            $(".vk-app .preview-vk-link .website").html('<div class="line-no-text w50"></div>');

            Main.ajax_post(_that, _action, _data, function(_result){
                if(_result.status == "success"){
                    if(_result.image != "")
                        $(".vk-app .preview-vk-link .image").css({'background-image': 'url(' + _result.image + ')'});
                    if(_result.title != "")
                        $(".vk-app .preview-vk-link .title").html(_result.title);

                    if(_result.description != "")
                        $(".vk-app .preview-vk-link .desc").html(_result.description);
                    if(_result.host != "")
                        $(".vk-app .preview-vk-link .website").html(_result.host);
                }
            });
        });

        $(document).on("click", ".all-post .list-action li a", function(){
            _that = $(this);
            _li = _that.parents("li");
            _id = _that.attr("href");
            _id = _id.replace("#", "");

            switch(_id){
                case "text":
                    $(".preview-vk").addClass("hide");
                    $(".preview-vk-text").removeClass("hide");
                    self.defaultPreview();
                    break;

                case "link":
                    $(".preview-vk").addClass("hide");
                    $(".preview-vk-link").removeClass("hide");
                    self.defaultPreview();
                    break;

                case "photo":
                    $(".preview-vk").addClass("hide");
                    $(".preview-vk-media").removeClass("hide");
                    self.defaultPreview();
                    break;

                case "video":
                    $(".preview-vk").addClass("hide");
                    $(".preview-vk-media").removeClass("hide");
                    self.defaultPreview();
                    break;
            }
        });

        //Review content
        if($(".post-message").length > 0){
            $(".post-message").data("emojioneArea").on("keyup", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".vk-app .caption-info").html(_data);
                }else{
                    $(".vk-app .caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".post-message").data("emojioneArea").on("change", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".vk-app .caption-info").html(_data);
                }else{
                    $(".vk-app .caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".post-message").data("emojioneArea").on("emojibtn.click", function(editor) {
                _data = $(".emojionearea-editor").html();
                if(_data != ""){
                    $(".vk-app .caption-info").html(_data);
                }else{
                    $(".vk-app .caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });
        }

        //Load Preview
        setInterval(function(){ 
            if($(".all-post .add_link").length > 0){
                $(".vk-app .preview-vk-link .title").html('<div class="line-no-text"></div>');
                $(".vk-app .preview-vk-link .desc").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text"></div>');
                $(".vk-app .preview-vk-link .website").html('<div class="line-no-text w50"></div>');

                _result = $(".all-post .add_link").attr("data-result");
                if(_result != undefined && _result != ""){
                    _result = JSON.parse(_result);

                    if(_result.title != "")
                        $(".vk-app .preview-vk-link .title").html(_result.title);
                    if(_result.description != "")
                        $(".vk-app .preview-vk-link .desc").html(_result.description);
                    if(_result.host != "")
                        $(".vk-app .preview-vk-link .website").html(_result.host);
                }
            }

            _type  = $(".schedule-vk-type .item.active").data("type");
            _type = ( _type == undefined )?$(".all-post .list-action .active input").val():_type;
            _media = $(".file-manager-list-images .item");
            if(_media.length > 0){
                if(_type == "media" || _type == "photo" || _type == "video" || _type == "link"){

                    list_images = [];
                    $check = true;

                    $("input[name='media[]']").each(function( index ) {
                        list_images.push($(this).val());
                        if(_current_link_photos.indexOf($(this).val()) == -1 || _current_link_photos.length != $("input[name='media[]']").length){
                            $check = false;
                        }
                    });

                    if($check == false){
                        _current_link_photos = list_images;
                        _count_image = list_images.length > 5?5:list_images.length;
                        _count_now = 1;
                        $(".vk-app .preview-vk-link .image").css({'background-image': 'url(' + list_images[0] + ')'});

                        $(".preview-vk-media .preview-image").attr("class", "preview-image").addClass("preview-media" + _count_image).html('');
                        for (i = 0; i < list_images.length; i++) {
                            _link_arr = list_images[i].split(".");
                            if(_link_arr[_link_arr.length - 1] != "mp4"){
                                $(".preview-vk-media .preview-image").append('<div class="item" style="background-image: url('+list_images[i]+')">'+((_count_now == 5 && list_images.length > 5)?'<div class="count">+'+(list_images.length-4)+'</div>':'')+'</div>');
                            }else{
                                _text = '';
                                if(_count_now == 5 && list_images.length > 5){
                                    _text = '<div class="count">+'+(list_images.length-4)+'</div>';
                                }
                                $(".preview-vk-media .preview-image").append('<div class="item"><video src="'+list_images[i]+'" playsinline="" autoplay="" muted="" loop=""></video>'+_text+'</div>');
                            }

                            _count_now++;
                        }

                    }

                }
            }else{
                if($(".all-post .add_link").length > 0){
                    $(".vk-app .preview-vk-link .image").removeAttr("style");
                }
            }
        }, 1500);
    };

    this.defaultPreview = function(){
        $(".preview-vk-media .preview-image").attr("class", "preview-image").html('');
    };
}

VK= new VK();
$(function(){
    VK.init();
});
