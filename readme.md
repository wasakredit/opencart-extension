# Wasa Kredit for OpenCart v3.0+
This is a payment extension for OpenCart 3.0.0.0 - 3.0.3.8.

## Installation Guide
Before installing the extension, make sure to backup the store!

1. Download the extension
2. Extract the content form the ZIP file
3. Upload the content of the upload folder to the root of your web store
4. Log in as an administrator and navigate to Extensions -> Extensions -> Payments
5. Click on the install button for the Wasa Kredit extension
6. Click on the edit button for the Wasa Kredit extension
7. Enter your Client ID and Secret Key
8. Select default order status for new orders
9. Save

## Extension for OpenCart 2.0 and 2.3
Our extensions for OpenCart 2.0 and 2.3 has been moved to separate branches and has been marked as legacy.
Please note that we no longer actively update these versions, we recommend that you update to a newer version of OpenCart.
However, we are still fixing bugs caused by the extension.

Extension for OpenCart 2.0 can be found here:\
https://github.com/wasakredit/opencart-extension/tree/opencart-v2-0

Extension for OpenCart 2.3 can be found here:\
https://github.com/wasakredit/opencart-extension/tree/opencart-v2-3

## Monthly Price Widget
To display our monthly cost widget on your product page, you will need to manually install the modification.

1. Navigate to _Extensions -> Installer_
2. Click on _Upload_ and select _wasa-widget.ocmod.zip_ from the widget folder
3. Navigate to _Extensions -> Modifications_
4. Click on _Refresh_.

If you have a custom theme you'll' need to manually modify this template:

`catalog/view/theme/[YOUR THEME]/template/product/product.twig`

Insert this following line where you want to add the widget:

`{# wasa-monthly-cost-widget #}`
