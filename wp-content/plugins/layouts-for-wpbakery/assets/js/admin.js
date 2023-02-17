jQuery(document).ready(function () {

    var lef_cur_url = window.location.href;
    var lef_res = lef_cur_url.substring(0, lef_cur_url.lastIndexOf("/") + 1);

    //Install layouts popup
    jQuery('.installbtn').each(function (idx, item) {
        var winnerId = "install-" + idx;
        this.id = winnerId;
        jQuery(this).click(function () {
            jQuery(".lfw-msg").show();
            jQuery(".lfw-msg").text('Import this template via one click');
            jQuery(".lfw-page-create, .lfw-create-page-btn").show();
            jQuery('input[type=text]').show();
            jQuery('.lfw-import-btn').bind('click');
            jQuery('.lfw-create-page-btn').bind('click');
            var btn = jQuery("#install-" + idx);
            var span = jQuery(".lfw-close-icon");
            var popId = jQuery('#content-in-' + idx);
            jQuery(popId).addClass('on');
            jQuery('body').addClass('install-popup');
            span.click(function () {
                jQuery(popId).removeClass('on');
                jQuery('body').removeClass('install-popup');
            });
        });
    });

    //Preview layouts popup
    jQuery('.previewbtn').each(function (idx, item) {

        var winnerId = "preview-" + idx;
        this.id = winnerId;
        jQuery(this).click(function () {
            jQuery(".lfw-msg").show();
            jQuery(".lfw-msg").text('Import this template via one click');
            jQuery(".lfw-page-create").show();
            jQuery(".lfw-page-create, .lfw-create-page-btn").show();
            jQuery('input[type=text]').show();
            jQuery('.lfw-import-btn').bind('click');
            jQuery('.lfw-buy-btn').bind('click');
            jQuery('.lfw-create-page-btn').bind('click');
            jQuery('#preview-in-' + idx + " iframe").attr("src", jQuery(this).attr('data-url'));
            var btn = jQuery("#preview-" + idx);
            var span = jQuery(".lfw-close-icon");
            var popId = jQuery('#preview-in-' + idx);
            jQuery(popId).addClass('on');
            jQuery('body').addClass('preview-popup');
            span.click(function () {
                jQuery(popId).removeClass('on');
                jQuery('body').removeClass('preview-popup');
            });
        });
    });

    //Filter layouts category js
    jQuery.fn.categoryFilter = function (selector) {
        this.click(function () {
            var categoryValue = jQuery(this).attr('data-filter');
            jQuery(this).addClass('active');
            jQuery(this).parent().siblings().children().removeClass('active');

            if (categoryValue == "all") {
                jQuery('.lfw_filter').show(800);
            } else {
                jQuery(".lfw_filter").not('.' + categoryValue).hide('800');
                jQuery('.lfw_filter').filter('.' + categoryValue).show('800');
            }
        });
    }

    jQuery('.lfw-category-filter').categoryFilter();

    jQuery(".lfw-close-icon").click(function () {
        jQuery(".lfw-import-btn").show();
        jQuery(".lfw-edit-template").hide();
        jQuery(".lfw-msg").hide();
        jQuery(".lfw-page-edit").hide();
        jQuery('.lfw-create-page-btn').removeClass('lfw-disabled');
        jQuery('.lfw-import-btn').removeClass('lfw-disabled');
        jQuery('input[type=text]').val('');
    });

    //sync latest template
    jQuery(".lfw-sync-btn").on('click', function () {

        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'handle_sync',
            },
            beforeSend: function () {
                jQuery('.lfw-sync-btn').text(js_object.lfw_sync);
            },
            success: function (res) {
                var res = res.slice(0, -1);
                if (res == 'success') {
                    setTimeout(function () {
                        Toastify({
                            text: js_object.lfw_sync_suc,
                            gravity: "right",
                            duration: 4500,
                            close: true,
                            backgroundColor: "linear-gradient(135deg, rgb( 99, 89, 241 ) 0%, rgb( 49, 181, 251 ) 100%)",
                        }).showToast();
                    }, 2000);
                    setTimeout(function () {
                        window.location.href = lef_cur_url;
                    }, 5000);
                } else {
                    setTimeout(function () {
                        Toastify({
                            text: js_object.lfw_sync_fai,
                            gravity: "right",
                            duration: 4500,
                            close: true,
                            backgroundColor: "linear-gradient(135deg, rgb( 99, 89, 241 ) 0%, rgb( 49, 181, 251 ) 100%)",
                        }).showToast();
                    }, 2000);
                    setTimeout(function () {
                        window.location.href = lef_cur_url;
                    }, 5000);
                }
            },

        });
    });

    //Import Template js
    jQuery(".lfw-import-btn").on('click', function () {
        jQuery(".lfw-loader").show();
        var template_id = jQuery(this).attr("data-template-id");
        var with_page = jQuery(".lfw-page-name-" + template_id).val();
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'handle_import',
                template_id: template_id,
                with_page: with_page,
            },
            beforeSend: function () {
                jQuery('.lfw-create-page-btn').addClass('lfw-disabled');
                jQuery(".lfw-import-btn").hide();
                jQuery(".lfw-loader").html("<div class='lfw-gradient-loader'></div>");

            },
            success: function (result) {
                jQuery(".lfw-loader").hide();
                if (result == 0) {
                    jQuery(".lfw-msg").text(js_object.lfw_error);
                } else {
                    jQuery(".lfw-msg").text(js_object.lfw_tem_msg);
                }
            },
            setTimeout: 1000,
        });
    });

    //Import Template with page name js
    jQuery(".lfw-create-page-btn").on('click', function () {
        var template_id = jQuery(this).attr("data-template-id");
        var crtbtn = jQuery(this).attr("data-name");
        jQuery('.lfw-loader-page').show();

        if (crtbtn == 'crtbtn') {
            var with_page = jQuery(".lfw-page-" + template_id).val();
        } else {
            var with_page = jQuery(this).siblings(".lfw-page-name-" + template_id).val();
        }

        //check page name not empty
        if (with_page == "") {
            alert(js_object.lfw_crt_page);
            jQuery(".lfw-page-name-" + template_id).addClass("lef-required");
            jQuery(".lfw-page-" + template_id).addClass("lef-required");
            return false;
        }

        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'handle_import',
                template_id: template_id,
                with_page: with_page,
            },
            beforeSend: function () {
                jQuery('.lfw-import-btn').addClass('lfw-disabled');
                jQuery(".lfw-create-page-btn, .lfw-page-name-" + template_id).hide();
                jQuery(".lfw-page-" + template_id).hide();
                jQuery(".lfw-loader-page").html("<div class='lfw-gradient-loader'></div>");
            },
            success: function (result) {
                jQuery(".lfw-page-create, .lfw-loader-page").hide();
                if (typeof result == 'string') {
                    if (jQuery.isNumeric(result)) {
                        if (result == 0) {
                            jQuery(".lfw-page-error").show();
                            jQuery(".lfw-error").text(js_object.lfw_error);
                        } else {
                            jQuery(".lfw-page-edit").show();
                            jQuery(".lfw-edit-page").attr("href", lef_res + 'post.php?post=' + result + "&action=edit");
                        }
                    } else {
                        jQuery(".lfw-page-error").show();
                        jQuery(".lfw-error").text(result);
                    }
                }
            },
            setTimeout: 1000,
        });
    });

});

function closeProgressIndicator() {
    jQuery(".lfeProgressIndicator").hide();
}
