# Wasa Kredit for OpenCart v3.0+
This is a payment extension for OpenCart 3.0.0.0 - 3.0.3.9.

This extension allows you to receive payments from your customers through:

:white_check_mark: Invoice Checkout\
:white_check_mark: Leasing Checkout

## Installation Guide
Before installing the extension, make sure to ***backup*** your web store!

1. From the [releases page](https://github.com/wasakredit/opencart-extension/releases), download ***wasa-extension.ocmod.zip***.
2. Go to *Admin → Extensions → Installer* and upload the downloaded ***wasa-extension.ocmod.zip*** file.
3. When uploaded successfully, go to *Admin → Extensions → Payments*.
4. Find the *Wasa Kredit - Leasing* and/or *Wasa Kredit - Invoice* in the module list and click the *Install* button.
5. Find the *Wasa Kredit - Leasing* and/or *Wasa Kredit - Invoice* in the module list and click the *Edit* button.
6. Enter your *Client ID* and *Client secret key*.
7. Save!

## Upgrading from v1.3.1 (or earlier)
If you are upgrading from v1.3.1 (or earlier) to v1.4.0 (or later), please follow these instructions
before atemptning the upgrade to avoid any issues caused by the breaking changes in v1.4.0.

- Uninstall your current version of the extension
	- Found at `Extensions -> Extensions -> Payments`
- Uninstall your current version of the modification (if installed)
	- Found at `Extensions -> Modifications`
- Delete the following files accosisated with your current version:
	- `/admin/controller/extension/payment/wasa.php`
	- `/admin/language/en-gb/extension/payment/wasa.php`
	- `/admin/language/sv-se/extension/payment/wasa.php`
	- `/admin/model/extension/payment/wasa.php`
	- `/admin/view/template/extension/payment/wasa.twig`
	- `/catalog/controller/extension/payment/wasa.php`
	- `/catalog/language/en-gb/extension/payment/wasa.php`
	- `/catalog/language/sv-se/extension/payment/wasa.php`
	- `/catalog/model/extension/module/wasa.php`
	- `/catalog/model/extension/payment/wasa.php`
	- `/catalog/view/theme/default/template/extension/module/wasa.twig`
	- `/catalog/view/theme/default/template/extension/payment/wasa.twig`

## Requirements

This is the requirements to run this module:
- OpenCart 3.0.3.0 or later
- PHP 7.3 or later
- A valid, approved Wasa Kredit merchant account

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

1. From the [releases page](https://github.com/wasakredit/opencart-extension/releases), download ***wasa-widget.ocmod.zip***.
2. Go to *Admin → Extensions → Installer* and upload the downloaded ***wasa-widget.ocmod.zip*** file.
3. Go to *Admin → Extensions → Modifications* and click on *Refresh*.

If you have a custom theme you'll' need to manually modify this template:

`catalog/view/theme/[YOUR THEME]/template/product/product.twig`

Insert this following line where you want to add the widget:

`{# wasa-monthly-cost-widget #}`

## Support

If you have spotted a bug or a technical problem, create a [GitHub issue](https://github.com/wasakredit/prestashop-addon-1.7/issues).
For other questions, contact our [support team](https://developer.wasakredit.se/contact).

*We only support the plugin with no customizations.
Please make sure before you raise an issue that you revisit it on a newly installed "vanilla" Prestashop environment.
With this practise you can make sure that the issue is not created by a customization or a third party plugin.*

