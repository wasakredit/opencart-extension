If you want to show wasa payment widget in product page, You need to follow instructions below. 

You can also contact me via email jaroslav@nalezny.cz with FTP access and i will modify your theme files for you.


EDIT : /catalog/view/theme/default/template/product/product.tpl
place <?php echo $wasa_widget ?> above  <div id="product">

EDIT : /catalog/controller/product/product.php
code below place above if ($product_info) {

$this->load->model('extension/payment/wasa');
$wasa_price = $this->currency->format(
                    $product_info['price'],
                    $this->session->data['currency'],
                    false,
                    false
                );

$data['wasa_widget'] = $this->model_extension_payment_wasa->getWidget($wasa_price);