function Youtube(){
    var self= this;
    var _current_link = "";
    var _current_link_photos = [];
    var _started = false;
    this.init= function(){
        if($(".youtube-app").length > 0){
            self.optionYoutube();
            self.loadPreview();
        }
    };

    this.optionYoutube = function(){
        $("#profiles .actionItem").click();
        $(document).on("click", ".actionLoadFB", function(){
            $("#profiles .actionItem").click();
        });

        /*Select all*/
        $(document).on("change", ".checkAll", function(){
            _that = $(this);
            if($('input:checkbox').hasClass("checkItem")){
                _ids = "";
                _el  = $("tbody input[name='id[]']");
                if(_that.hasClass("checked")){
                    _el.each(function(index){
                        _ids += $(this).val() + ((_el.length != index + 1)?",":"");
                    });    
                }

                $("input[name='list_ids']").val(_ids);
            }
        });

        $(document).on("change", "tbody input[name='id[]']", function(){
            _that = $(this);
            _ids  = "";
            _el   = $("tbody input[name='id[]']:checked");
            _el.each(function(index){
                _ids += $(this).val() + ((_el.length != index + 1)?",":"");
            });

            $("input[name='list_ids']").val(_ids);
        });

        //Select type post
        $(document).on("click", ".schedule-facebook-type .item", function(){
            _that = $(this);
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
                $("#link").removeClass("active");
            }

            $(".preview-fb").addClass("hide");
            $(".preview-fb-"+_type).removeClass("hide");
            self.defaultPreview();
        });

        //Enable Schedule
        $(document).on("change", ".enable_youtube_schedule", function(){

            _that = $(this);
            if(!_that.hasClass("checked")){
                $('.postnow-option').addClass("hide");
                $('.schedule-option').removeClass("hide");
                $('.btnPostNow').addClass("hide");
                $('.btnSchedulePost').removeClass("hide");
                _that.addClass('checked');
            }else{
                $('.postnow-option').removeClass("hide");
                $('.schedule-option').addClass("hide");
                $('.btnPostNow').removeClass("hide");
                $('.btnSchedulePost').addClass("hide");
                _that.removeClass('checked');        
            }
            return false;
        });

        $(document).on("click", ".file-manager-list-images .item .close", function(){
            if($(".file-manager-list-images .item").length <= 0){
               self.defaultPreview();
            }
        });

        $(document).on("click", ".youtube-app .btnPostNow", function(){
            _that = $(this);
            self.postNow(_that);
        });

        
    };

    this.postNow = function(_that){
        _form    = _that.closest("form");
        _action  = _form.attr("action");
        _data    = $("[name!='account[]']").serialize();
        _data    = _data + '&' + $.param({token:token});
        _item    = $(".box-list .item.active");
        _stop    = false;
        if(_item.length > 0){
            _id     = _item.first().find("input").val();
            _data   = _form.serialize();
            _data   = Main.removeParam("account%5B%5D", _data);
            _data   = _data + '&' + $.param({token:token , 'account[]' :_id});
        }else{
            if(_started == true){
                _started = false;
                Main.statusOverplay("hide");
                return false;
            }
        }

        Main.statusOverplay("show");

        Main.ajax_post(_that, _action, _data, function(_result){
            Main.statusOverplay("show");
            _started = true;

            //Remove mark item
            if(_result.stop == undefined){
                _item.first().find("a").trigger("click");
                if(_item.length > 1){
                    setTimeout(function(){
                        $(".btnPostNow").trigger("click");
                    }, 500);
                }
            }else{
                Main.statusOverplay("hide");
            }
        });
    };

    this.loadPreview = function(){
        //Review content
        if($(".youtube-app .post-message").length > 0){
            $(".youtube-app .post-message").data("emojioneArea").on("keyup", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".youtube-app .post-message").data("emojioneArea").on("change", function(editor) {
                _data = editor.html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });

            $(".youtube-app .post-message").data("emojioneArea").on("emojibtn.click", function(editor) {
                _data = $(".emojionearea-editor").html();
                if(_data != ""){
                    $(".caption-info").html(_data);
                }else{
                    $(".caption-info").html('<div class="line-no-text"></div><div class="line-no-text"></div><div class="line-no-text w50"></div>');
                }
            });
        }

        $(".youtube-app input[name='title']").keypress(function(){
            _data = $(this).val();

            if(_data != ""){
                $(".preview-yt .preview-title").html(_data);
            }else{
                $(".preview-yt .preview-title").html('<div class="line-no-text"></div><div class="line-no-text"></div>');
            }
        });

        $(".youtube-app input[name='title']").change(function(){
            _data = $(this).val();

            if(_data != ""){
                $(".preview-yt .preview-title").html(_data);
            }else{
                $(".preview-yt .preview-title").html('<div class="line-no-text"></div><div class="line-no-text"></div>');
            }
        });

        _preview_title = $(".preview-title").html();
        _input_title = $(".youtube-app input[name='title']").val();
        if(_preview_title != _input_title && _input_title != ""){
            $(".preview-title").html(_input_title);
        }

        //Load Preview
        _type = "video";
        setInterval(function(){ 
            _media = $(".file-manager-list-images .item");
            if(_media.length > 0){
                switch(_type){

                    case "video":
                        _link     = _media.find("input").val();
                        _link_arr = _link.split(".");
                        if(_current_link != _link){
                            if(_link_arr[_link_arr.length - 1] == "mp4"){
                                $(".preview-yt .preview-video").html('<video src="'+_link+'" playsinline="" autoplay="" muted="" loop=""></video>');
                                $(".preview-yt .preview-video").css({"background-image": "none"});
                            }
                            _current_link = _link;
                        }
                        break;

                    case "text":
                        break
                }
            }
        }, 1500);
    };

    this.defaultPreview = function(){
        $(".preview-yt .preview-video").attr("class", "preview-video").html('');
    };
}

Youtube= new Youtube();
$(function(){
    Youtube.init();
});