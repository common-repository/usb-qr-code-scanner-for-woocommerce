jQuery(document).ready(function($){



   var barcode = '';
            var interval;
            document.addEventListener('keydown', function(evt) {
                if (interval)
                    clearInterval(interval);
                if (evt.code == 'Enter') {
                    if (barcode)

                        sendbarcodetoserver(barcode);
                    barcode = '';
                    return;
                }
                if (evt.key != 'Shift')
                    barcode += evt.key;

                interval = setInterval(() => barcode = '', 20);
            });

           /* function handleBarcode(scanned_barcode) {
               
            
            }*/
            
            

$("#scanbarcode").click(function () {


var barcodetext= $("#barcode").val() ; 
  $("#loaderimage").show() ; 


  var data = {
            action: "wcoqrscusb_add_to_cart_by_sku",
            barcode: barcodetext , 
            acdo:"add"
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
            
              $("#wcoqrscubd_cart_html").html(res.htmlcart) ; 
              $("#loaderimage").hide() ; 

             
            }) ;

            


}) ; 

if ($("#wcoqrscubd_cart_html").length > 0 ) 
{
gethtmlbarcode()
}
function gethtmlbarcode()
{


var barcodetext= "aa"; 
  $("#loaderimage").show() ; 


  var data = {
            action: "wcoqrscusb_add_to_cart_by_sku",
            barcode: barcodetext , 
            acdo:"add"
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
            
              $("#wcoqrscubd_cart_html").html(res.htmlcart) ; 
              $("#loaderimage").hide() ; 

             
            }) ;

}


function sendbarcodetoserver(barcodetext)
{

console.log(barcodetext)

  $("#loaderimage").show() ; 


  var data = {
            action: "wcoqrscusb_add_to_cart_by_sku",
            barcode: barcodetext , 
            acdo:"add"
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
            
              $("#wcoqrscubd_cart_html").html(res.htmlcart) ; 
              $("#loaderimage").hide() ; 

             
            }) ;
    
}


$(document).on("click", '#apply_copun_m', function(event) { 


var coupon_code = $("#coupon_code").val() ; 
              $("#loaderimage").show() ; 


  var data = {
            action: "wcoqrscusb_add_to_cart_by_sku",
            coupon_code: coupon_code , 
            acdo:"apply_copun_m"
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
            
              $("#wcoqrscubd_cart_html").html(res.htmlcart) ; 
              $("#loaderimage").hide() ; 

              if (res.notices.length > 0 ) 
              {

                alert(res.notices);
              }

             
            }) ;



}) ;


$(document).on("click", '.removeproduct', function(event) { 


  $("#loaderimage").show() ; 

    var barcode = $(this).attr("dataid") ; 
  
  var data = {
            action: "wcoqrscusb_add_to_cart_by_sku",
            barcode: barcode , 
            acdo:"remove"
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
            
              $("#wcoqrscubd_cart_html").html(res.htmlcart) ; 
              $("#loaderimage").hide() ; 

             
            }) ;

            


})


$(document).on("click", '.scanbarcodesave', function(event) { 



  $("#loaderimage").show() ; 

  
  var data = {
            action: "wcoqrscusb_save_order",
        };


          $.post(the_in_url.in_url, data, function (response) {
            var res = $.parseJSON(response);
              
              $("#loaderimage").hide() ; 
              window.print();

             
            }) ;




}) ; 








});