
/**
 * Button Trackorder in admin section  
 **/

jQuery(document).on('submit','.track_order_form',function(event){
    event.preventDefault();
    //disable button
    jQuery(this).find('button').addClass('loading');
    jQuery('.loading').attr('disabled',true);
    
    // form track-order  input value
    var track_info = new Array();
    var track_order_info = jQuery(this).serializeArray();

    if(track_order_info){
      //foreach all track info
      jQuery.each(track_order_info,function(key,track_data){
           track_info[track_data.name] = strip_tags(track_data.value); 
      });
      
      // call process for trackorder 
      track_order( (track_info.wassalNow_trackNo ? track_info.wassalNow_trackNo :'error') ,track_info.wassalNow_error);
          
    }
});


/**
 * Button Trackorder in admin section  
 **/
jQuery(document).on('click','.show_track_order',function(){
     //disable button
    jQuery(this).addClass('loading');
    jQuery(this).parents('.dropdown').addClass('loading');
    jQuery('.loading').attr('disabled',true);
    var track_info  = jQuery(this).attr('data-value');
    var track_error = jQuery(this).attr('data-error');
    // call process for trackorder 
    track_order(track_info,track_error);
});

// process for trackorder 
function track_order(track_order_number,track_order_error=''){
   if(track_order_number){
        jQuery.ajax({
            type:'POST',
            url:wosw_admin_ajax_trackorder.ajaxurl,
            dataType: 'JSON',
            data:{
                  action:'admin_track_wosw_order',
                  order_items_track_info:track_order_number,
                },
            success:function(result){
              jQuery('.loading').attr('disabled',false);
              jQuery('.loading').removeClass('loading');
              if(result.response){
                  // sweetalert
                  Swal.fire({
                    imageUrl: 'https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png',
                    imageWidth: 100,
                    imageHeight: 100,
                    title : result.titleText + track_order_number, 
                    text  : result.response,
                    confirmButtonText: result.buttonText,
                    icon: 'info',
                  });
              }
            }
        });
    }else{
        jQuery('.loading').attr('disabled',false);
        jQuery('.loading').removeClass('loading');
        //sweetalert
        Swal.fire({
          imageUrl: 'https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png',
          imageWidth: 100,
          imageHeight: 100,
          title : track_order_error, 
          text  : '',
          icon: 'info',
        });
    }
}



/**
 * Button Trackorder in admin section  
 **/
jQuery(document).on('click','.cancel_order_wosw',function(){
    //diaable button
    jQuery(this).addClass('loading');
    jQuery(this).parents('.dropdown').addClass('loading');
    jQuery('.loading').attr('disabled',true);
    var shipmentId      = jQuery(this).attr('data-value');
    var dataQuestionair = jQuery(this).attr('data-questionair');
    var dataConfirm     = jQuery(this).attr('data-confirm');
    var dataCancel      = jQuery(this).attr('data-cancel');
    if(shipmentId){
        //sweetalert
        Swal.fire({
          icon:'question',
          imageUrl: 'https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png',
          imageWidth: 100,
          imageHeight: 100,
          title: dataQuestionair,
          showDenyButton: true,
          showCancelButton: true,
          confirmButtonText: dataConfirm,
          CancelButtonText: dataCancel,
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                jQuery.ajax({
                  type:'POST',
                  url:wosw_admin_ajax_trackorder.ajaxurl,
                  dataType: 'JSON',
                  data:{
                        action:'admin_cancel_wosw_order',
                        order_items_shipmentId:shipmentId,
                      },
                  success:function(result){
                    jQuery('.loading').attr('disabled',false);
                    jQuery('.loading').removeClass('loading');
                    if(result.response){
                        //sweetalert
                        Swal.fire({
                          imageUrl: 'https://wassalnow.com/wp-content/uploads/2020/04/WNlogo-1.png',
                          imageWidth: 100,
                          imageHeight: 100,
                          title : result.titleText, 
                          text  : result.response,
                          confirmButtonText: result.buttonText,
                          icon  : ( result.response=='This shipment can not be deleted!'?'error':'success'),
                        });
                    }
                  }
                });
            }
        });
    }
});



/* When the user clicks on the button, 
toggle between hiding and showing the dropdown content */
jQuery(document).on('click','.wassal_dropbtn',function(){
     var order_id = jQuery(this).attr('data-value');
     document.getElementById("myDropdown"+order_id).classList.toggle("show");
});


// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {
    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}



function strip_tags(str) {
    str = str.toString();
    return str.replace(/<\/?[^>]+>/gi, '');
}