$(document).ready(function(event) {

    $('input[name^="title_"]').each(function(index, val) {

        var selector = 'input[name="title_'+(index+1)+'"]';

        $(selector).on('keyup',function (e) {

            if (!getUrlParameter('addoannablock')){
                return false;
            }

            delay(function(){
                var title = $(e.target).val();

                $.ajax({
                    url : "index.php?fc=module&module=oannablocks&controller=AdminOannaBlocks&token="+getUrlParameter('token'),
                    type : 'POST',
                    cache : false,
                    data : {
                        ajax : true,
                        title : title,
                        action : 'generateAlias',
                    },
                    success : function (result) {
                        if(result.success) {
                            $('input[name="alias_'+(index+1)+'"]').val(result.data);
                        }

                        // your action code on result
                    }
                });
            }, 400 );


        });
    })
})

var delay = (function(){
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
    return false;
};