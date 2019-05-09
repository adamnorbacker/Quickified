(function ($) {
    $('.navbutton').on('click', function (e) {
        e.preventDefault();
        var $this = $(this),
            theId = $this.find("a").attr('href');
        if ($this.hasClass('active')) {
            return false;
        } else {
            $('.navbutton').removeClass('active');
            $('.q_pages').removeClass('activepage');
            $this.addClass('active');
            $(theId).addClass("activepage");
        }
    });

    $(".dropdowncontainer").find(".text").on("click", function(){
        $(this).parent().find(".dropdownlist").toggleClass("active");
    });

    $('#optimize_db_button').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'optimizeTables'
            },
            success: function (reponse) {
                alert("DB is optimized");
            },
            error: function (xhr) {
                //error handling
                alert("error " + xhr);
            }
        });

    });

    $('#optimize_images_button').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'optimizeImages'
            },
            success: function (reponse) {
                alert(reponse);
            },
            error: function (xhr) {
                //error handling
                alert("Error " + xhr);
            }
        });

    });

    $('#optimize_fonts_button').click(function (e) {
        e.preventDefault();
        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                action: 'optimizeFonts'
            },
            success: function (reponse) {
                alert(reponse);
            },
            error: function (xhr) {
                //error handling
                alert("Error " + xhr);
            }
        });

    });

    //Generate zip
    $(".dropdowncontainer").find(".download_files").on("click", function(){
        var font_id = $(this).data("id");
        $.ajax({
            type: "post",
            url: ajaxurl,
            data:{
                'action': 'Get_Zipped_fonts',
                'font_id': font_id
            },
            success: function(msg){
                document.location.assign(msg);
            },
            error: function (request, status, error) {
                alert(status);
            }
        });
    });

        //Generate font css
        $(".dropdowncontainer").find(".generate_css").on("click", function(){
            var font_id = $(this).data("id");
            $.ajax({
                type: "post",
                url: ajaxurl,
                data:{
                    'action': 'Get_Generated_Font_css',
                    'font_id': font_id
                },
                success: function(msg){
                    $('#popup_generated_css').fadeIn(200);
                    $('#generated_css_content').text(msg);
                },
                error: function (request, status, error) {
                    alert(status);
                }
            });
        });

        $('#close_gen_css').on("click", function(){
            $('#popup_generated_css').fadeOut(200);
        });

})(jQuery);