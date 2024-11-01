<?php

 /**
* Plugin Name:USB Qr Code Scanner For Woocommerce
* Description: A plugin for quick ordering Payment on the spot using by barcode reader(USB Qr Code Scanner)
* Version: 1.0.0
* Author: Behzad Rohizadeh 
* Author URI:https://behzadrohizadeh.ir
* Text Domain:wcoqrscusb
* Domain Path: /languages
*/
if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

class WCOQRSCUSB 
{
    
    function __construct()
    {
           if ( is_admin() ) {
                add_action( 'admin_menu', array(&$this, 'wcoqrscusb_add_admin_menu' ) );
            }
       add_shortcode('usbqrcode', array(&$this,'WCOQRSCUSB_shortcode'));
       add_action('wp_enqueue_scripts',array(&$this, 'WCOQRSCUSB_scripts'));
       add_action('wp_ajax_wcoqrscusb_add_to_cart_by_sku',array(&$this,'wcoqrscusb_add_to_cart_by_sku'));
       add_action('wp_ajax_wcoqrscusb_save_order',array(&$this,'wcoqrscusb_wcoqrscusb_save_order'));
       add_action('woocommerce_product_options_pricing',array(&$this,'wcoqrscusb_show_barcode_in_product'));
       add_action( 'woocommerce_variation_options_pricing', array(&$this,'wcoqrscusb_show_barcode_in_variation') ,10,20,30);
       add_action('plugins_loaded',array(&$this, 'wcoqrscusb_localization_init_textdomain'));

    }

    function wcoqrscusb_localization_init_textdomain()
        {

    $path = dirname( plugin_basename(__FILE__)) . '/languages';

 $result = load_plugin_textdomain( 'wcoqrscusb', false, $path);
 
    
 
        }


    function wcoqrscusb_show_barcode_in_product() {
    global  $post;

    $sku = get_post_meta($post->ID , "_sku" , true) ; 
    $html  = ''; 
    if (!empty($sku)) {
        if(extension_loaded('gd')){
        require 'vendor/autoload.php';
        $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
        $html.='<div style="text-align: center;" class="form-field">';
        $html.= '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($sku, $generator::TYPE_CODE_128)) . '">';
     $html.='</div>';

   }

    if(!extension_loaded('gd'))
    {
        $html.='<div style="text-align: center;" class="form-field">';
        $html.= '<span>'.__("To create a barcode, you need to activate the PHP GD library. To activate
Contact the hosting manager","wcoqrscusb").'</span>';
        $html.='</div>';

    }
}

     

_e($html) ; 
   
}

function wcoqrscusb_show_barcode_in_variation($loop, $variation_data, $variation) {

    $sku = get_post_meta($variation->ID , "_sku" , true) ; 
$html  = ''; 
    if (!empty($sku)) {
        if(extension_loaded('gd')){
        require 'vendor/autoload.php';
        $generator = new Picqer\Barcode\BarcodeGeneratorJPG();

        $html.='<div style="text-align: center;" class="form-field">';
        $html.= '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($sku, $generator::TYPE_CODE_128)) . '">';

        $html.='</div>';
 }

     if(!extension_loaded('gd'))
    {
        $html.='<div style="text-align: center;" class="form-field">';
        $html.= '<span>'.__("To create a barcode, you need to activate the PHP GD library. To activate
Contact the hosting manager","wcoqrscusb").'</span>';
        $html.='</div>';

    }


   }

_e($html) ; 
   
}
    function wcoqrscusb_wcoqrscusb_save_order()
    {

        $cart = WC()->cart;
        $checkout = WC()->checkout();
        $order_id = $checkout->create_order(array());
        $order = wc_get_order($order_id);
        $order->set_payment_method( "cod" );
        $order->update_status("Completed", __("The order was successfully saveed","wcoqrscusb"), TRUE);
        $order->set_created_via( 'programatically' );
        $order->set_customer_id( get_current_user_id() );
        //$order->set_customer_user_agent( wc_get_user_agent() ); 

        $order->calculate_totals();
        $order->payment_complete(); 
        $order_id = $order->save();
        $cart->empty_cart();


    $res["status"]=200 ; 
    echo json_encode($res);
    exit();
    }

    function WCOQRSCUSB_shortcode() 
    {

    

   
 

if (current_user_can('seller') || current_user_can('shop_manager') || current_user_can('administrator')) {
   

        $print_header = get_option("print_header") ; 
        $print_footer = get_option("print_footer") ; 
      
      return '
          <div id="section-to-print">
        
        <img id="loaderimage" src="'.plugin_dir_url(__FILE__).'img/loader.gif">
         <div class="print_header">'.html_entity_decode($print_header).'</div>
         <div id="wcoqrscubd_cart_html">
         
         </div>
          <div class="print_footer">'.html_entity_decode($print_footer).'</div>
       <div>
      ';
  }
  return '' ; 

  
    }

    function WCOQRSCUSB_scripts() 
    {


    wp_register_style('WCOQRSCUSB-css', plugins_url('css/WCOQRSCUSB.css', __FILE__), [], '1.0.0');
    wp_enqueue_style('WCOQRSCUSB-css');

    wp_register_script('WCOQRSCUSB-js', plugins_url( '/js/WCOQRSCUSB.js', __FILE__ ), array('jquery'),"1.0.0");
    wp_enqueue_script('WCOQRSCUSB-js');

     wp_localize_script( 'WCOQRSCUSB-js', 'the_in_url', array( 'in_url' => admin_url( 'admin-ajax.php' ) ) ); 

    }

    function get_product_id_by_sku_my($sku)
    {

        global $wpdb;
        $table=$wpdb->prefix."postmeta";  
        $entry=$wpdb->get_results("SELECT * FROM $table WHERE meta_key='_sku' AND meta_value='{$sku}'");

        if (!empty($entry)) {
            
           return intval($entry[0]->post_id) ; 
        }

     return 0 ; 
    }

  

    function coupon_code_to_cart( $copon)
{

      if(!empty($copon)) {
 
      WC()->cart->remove_coupons();
      $add=  WC()->cart->apply_coupon($copon);

      if (!$add) {
         
         return __("The entered discount code is not available","wcoqrscusb") ; 
      }

      return "" ; 
    


      }
      return "";
}

    function wcoqrscusb_add_to_cart_by_sku() 
    {
       $acdo=sanitize_text_field($_POST["acdo"]) ;
       $res["notices"] = [] ; 

      
if ($acdo=="add") {
       $barcode=sanitize_text_field($_POST["barcode"]) ;
       $product_id = $this->get_product_id_by_sku_my($barcode);
    WC()->cart->add_to_cart($product_id, 1, 0);
}
  if ($acdo=="remove") {
    $barcode=sanitize_text_field($_POST["barcode"]) ;
    $product_id = $this->get_product_id_by_sku_my($barcode);
   $this->remove_product_from_cart_by_id($product_id);
}  

if ($acdo=="apply_copun_m") {
      $coupon_code=sanitize_text_field($_POST["coupon_code"]) ;
     $res["notices"]=$this->coupon_code_to_cart($coupon_code);

  }  
     

    $res["product_id"]=$product_id ; 
    $res["status"]=200 ; 
    $res["htmlcart"] =$this->cart_content_html() ; 

    echo json_encode($res);
    exit();

    }


    function remove_product_from_cart_by_id($product_id)
    {

      foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
         if ( $cart_item['product_id'] == $product_id || $product_id==$cart_item['variation_id']) {
              WC()->cart->remove_cart_item( $cart_item_key );
         }
      }

      if ( count( WC()->cart->get_cart()) == 0 ) {
               WC()->cart->remove_coupons();

      }

      return "done" ; 
    }


    function cart_content_html()
    {
      
      $html = ''; 
        if (  WC()->cart->get_cart_contents_count() == 0 ) 
{

return $html ;
 }



     $html.= '<table>

  <thead>
   <tr>
   <th>'.__("Row","wcoqrscusb").' </th>
   <th>'.__("Product Name","wcoqrscusb").' </th>
   <th>'.__("Price","wcoqrscusb").' </th>
   <th>'.__("Qty","wcoqrscusb").' </th>
   <th>'.__("Subtotal","wcoqrscusb").' </th>
   </tr>
  </thead>';

 

    //$cc=get_woocommerce_currency_symbol() ; 
       
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
               $product     = $cart_item['data']; 
                $thumbnail   = $product->get_image(array( 70, 70)); 
        $subtotal = $cart_item[ 'data' ]->get_price() * $cart_item[ 'quantity' ];


                 $html.= '<tr>';
                     $html.= '<td > <button dataid="'.$product->get_sku().'" class="removeproduct" type="button">x</button>';
                      $html.=$thumbnail ; 
                     $html.= '</td>';
                      $html.= '<td>';
                      $html.=$product->get_name() ; 
                     $html.= '</td>';
                     $html.= '<td>';
                      $html.=wc_price($product->get_price());
                     $html.= '</td>';

                     $html.= '<td>';
                      $html.=$cart_item['quantity'];
                     $html.= '</td>';

                      $html.= '<td>';
                      $html.=wc_price($subtotal);
                     $html.= '</td>';
                 $html.= '</tr>';
               
            }
        
       $html.= '<tr class="no-print">
              <td colspan="6" class="actions">

                    <div class="coupon">
                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="'.__("Discount Code","wcoqrscusb").'"> 
                            <button id="apply_copun_m" type="button">'.__("Apply Discount Code","wcoqrscusb").'</button>
                   </div>
          </td>
            </tr>';



    $html.= '</table>';


    $html.= '<div class="cart_totals ">

    
    <table cellspacing="0" class="shop_table shop_table_responsive">

        <tbody>
        <tr class="cart-subtotal">
            <th>'.__("Subtotal","wcoqrscusb").'  :</th>
            <td>
            '.wc_price(WC()->cart->get_subtotal()).'
            
            </td>
        </tr>

          <tr class="cart-subtotal">
            <th>'.__("Discount","wcoqrscusb").'  : </th>
            <td>
            '.wc_price(WC()->cart->get_cart_discount_total()).'
            
            </td>
        </tr>

        
                          
        <tr class="order-total">
            <th>'.__("Total","wcoqrscusb").'  :</th>
            <td ><strong>
            <span class="woocommerce-Price-amount amount">
            '.WC()->cart->get_total().'
            </span></strong> 
            </td>
        </tr>

        
    </tbody></table>

   
<div class="wc-proceed-to-checkout">
        
<button type="button" class="checkout-button button scanbarcodesave">
      '.__("Save Order","wcoqrscusb").'
    </button>
    </div>
    
</div>';



    return $html ; 
    }



    

       
       function wcoqrscusb_add_admin_menu() {

          $text = __("Usb Barcode Scanner settings","wcoqrscusb"); 
            add_menu_page(
                $text,
                 $text,
                'manage_options',
                'wcoqrscusb-theme-settings',
                array(&$this, 'create_admin_page' )
            );
        }

      
        
         function create_admin_page() { 


    if (isset($_POST['add_new'])) {

      $allowed_html = wp_kses_allowed_html( 'post' );
     
      update_option("print_header" , htmlentities(stripslashes(wp_kses($_POST["print_header"], $allowed_html))),true);
      update_option("print_footer" , htmlentities(stripslashes(wp_kses($_POST["print_footer"], $allowed_html))),true);
  
}



          $options = $this->get_options_data() ; 
            ?>


<style>

.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: right;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}

.fltr
{

 text-align: left;

}
</style>

<script>
function openCity(evt, cityName) {
  var i, tabcontent, tablinks;
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(cityName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
 <div class="wrap">
  <h1><?php _e("General settings","wcoqrscusb");?></h1>
   <?php 
if(!extension_loaded('gd'))
    {
   ?> 
        <div style="text-align: center;" class="update-message notice inline notice-warning notice-alt">
        <span><?php _e("To create a barcode, you need to activate the PHP GD library. To activate
Contact the hosting manager","wcoqrscusb") ?> </span>
        </div>

    <?php
    } 
    ?> 

  <div class="tab">
    <?php 

    foreach( $options as $k => $tab){ ?> 
     <button class="tablinks active" onclick='openCity(event, "<?php echo esc_html($k) ?>")'><?php echo esc_html($tab["lable"]) ?></button>
 <?php } ?> 
</div>
 <?php  foreach( $options as $k => $tab) {  ?>



<div id="<?php echo esc_html($k) ?>" class="tabcontent" style="display:block;" >
  <h3><?php echo esc_html($tab["lable"]); ?></h3>
<form method="post" action="">
<table class="form-table wpex-custom-admin-login-table">
        <?php foreach ($tab["form"] as $kk => $f) {  ?>

            <tr class="top">
                    <td>
                       <?php echo esc_html($f["lable"]);?>
                    </td>
                    <td>
                        <?php 

                        $v = get_option($kk);

                        ?>

                         

                             <textarea class="fltr" cols="70" rows="7" name="<?php echo esc_html($kk) ?>"><?php echo esc_html($v)?></textarea>
                       
                    </td>
                </tr>
                 <?php  } ?> 

   <tr class="top">
                    <td>
                        
                    </td>
                    <td>
                        <input name="add_new"   class="button button-primary" value="<?php _e('Update','wcoqrscusb');?>" type="submit">
                    </td>
                </tr>

            
        </table>

    </form>
</div>
<?php } ?> 


                

                       
            </div><!-- .wrap -->
        <?php }

    


    function get_options_data()

    {
      
      $options= array(
                "index_page"=>array(
                "lable"=>__("General settings","wcoqrscusb") ,
                "form"=>array
                  (
                 "print_header"=>["type"=>"textarea","lable"=>__("Header (text or html code) For Printer","wcoqrscusb")  ] ,
                 "print_footer"=>["type"=>"textarea","lable"=>__("Footer (text or html code) For Printer","wcoqrscusb")  ] ,

                 )
            ),// end index_page,

               

      );



    return $options; 
    }


    
}



new WCOQRSCUSB();
