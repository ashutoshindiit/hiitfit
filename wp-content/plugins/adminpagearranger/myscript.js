jQuery(function() {
    var $this;
    jQuery('.update-status-live').click(function(e){ 
        var value = jQuery(this).data('id');
        var status = 0;
        var $this = this;
        if(jQuery(this).is(':checked'))
        {
            status = 1; 
        }
        jQuery.ajax({
                type : "POST",
                url : "/wp-admin/admin-ajax.php",
                data : {
                    action: "live_get_status_data",
                    live_id: value,
                    status: status
                },
                beforeSend: function() {
                     jQuery($this).parents('tr').css('background','#fff1f1');
                },
                success: function(response) {
                    jQuery($this).parents('tr').css('background','');
                }
        });   

     });
});

