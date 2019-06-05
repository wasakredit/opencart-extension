# Wasa Kredit Payment Opencart Installation Guide

1. Download corresponding version from <https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=36742>
2. Extract content of the zip file
3. Prepare FTP access to your eshop
4. Upload the content of /upload directory to the root of your shop via FTP
5. Now you need to login to your shop administration and enable Wasa payment via Extensions -> payments
![Extensions picture](extensions.png)
6. When module is installed and you are in its administration, you can see following input fields
![Input fields picture](inputfields.png)
   1. Client ID - Please contact Wasa Kredit company for this information
   2. Client secret key - Please contact Wasa Kredit company for this information
   3. Environment - In case you want to create test order with Wasa payment first, in other cases select **Live**
   4. Status - Enable Wasa payment in opencart
   5. Order status - Select which status should be set for new order in Wasa payment. I suggest **Processing**
