# Wasa Kredit for OpenCart v3+
This is a payment extension for OpenCart 3.0.0.0 - 3.0.3.7.

## Installation Guide
Before installing the extension, make sure that there is a backup of web store!

1. Download the extension
2. Extract the content form the ZIP file
3. Upload the content of the upload folder to the root of your web store
4. Log in as an administrator and navigate to Extensions -> Extensions -> Payments
5. Click on the install button for the Wasa Kredit extension
6. Click on the edit button for the Wasa Kredit extension
7. Enter your Client ID and Secret key
8. Select default order status for new orders
9. Save!

## Monthly Price Widget
To display our monthly cost widget on your product page, you will need to manually edit the product page template:

### /catalog/view/theme/default/template/product/product.twigl
Paste the following code where you want the widget to appear:
```
{{ wasa_widget }}
```

### /catalog/controller/product/product.php
Find this code:
```
if ($product_info) {
```

Below that line, enter this code:
```
$this->load->model('payment/wasa');

$wasa_price = $this->currency->format($product_info['price'], $this->session->data['currency'], false, false);

$data['wasa_widget'] = $this->model_payment_wasa->getWidget($wasa_price);
```
