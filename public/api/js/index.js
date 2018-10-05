function submit(){
    var params = {};

    var url = $('.form #url').val();
    var token = $('.form #token').val();
    var methods = $('.form #methods').val();

    $('.form .params').each(function(){
        if($(this).find('.field').val()){
            params[$(this).find('.field').val()] = $(this).find('.value').val();
        }
    });

    var get_arr = url.split('?');
    if(get_arr.length>1 && get_arr[1]){
        url += `&auth_token=${token}`;
        url = url.replace(/\?+/,'?').replace(/&+/,'&');
    }else{
        url += `?auth_token=${token}`;
        url = url.replace(/\?+/,'?');
    }
    // console.log(url);
    localStorage.setItem('token',$('#token').val());
    localStorage.setItem('url',$('#url').val());
    $.ajax({
        type:methods,
        url:url,
        dataType:'json',
        data:params,
        headers:{
            authorization:token || null
        },
        success:function(data){
            console.log(data);
            $('#json').jsonView(data);
        },
        error:function(error){
            alert('出错了');
            console.error(error);
            throw new Error(error);
        }
    });
}

$(function(){

    var params_list = [1];


    $('.form #url').val(localStorage.getItem('url') || location.origin+'/');

    $('#methods option[value="POST"]').attr("selected",true);

    $('#token').val(localStorage.getItem('token'));

    $('.form .add').bind('click',function(){
        var index = params_list[params_list.length-1]+1;
        params_list.push(index);
        var template = `
            <div class="params input params${index}">
                <input type="text" class="field field${index}" value="">
                <span>:</span>
                <input type="text" class="value" id="value${index}">
                <a class="remove iconfont" title="移除此参数">&#xe798;</a>
            </div>`;
        $('.form .submit').before(template);
    });

    $(document).on('click','.form .params .remove',function(){
        $(this).parents('.params').remove();
    });

    $('#token').change(function(){
        localStorage.setItem('token',$(this).val());
    });

    $('#url').change(function(){
        localStorage.setItem('url',$(this).val());
    });

    $('.submit').bind('click',submit);
    $('.form').bind('keydown',function(e){
        if(e.keyCode===13){
            submit();
        }
    });
});