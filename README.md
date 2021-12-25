<img src="https://www.globalprimepay.com/dist/images/logo.svg" width="130" />

# GBPrimePay Payment

###  Install GBPrimePay Checkout

Install GBPrimePay Checkout for WHMCS
1) Download the GBPrimePay Checkout for WHMCS .zip file of the latest release from here: https://github.com/GBPrimepay/whmcs-gbprimepay-checkout/releases
2) Extract the files within your installation location under to folder ```/modules/```
3) Copy modules folder to ```<whmcs_root_directory>```
4) Open WHMCS Admin area
5) Navigate to "Wrench" icon on upper right corner ```Configuration > System Settings```
7) Go to ```Payment Gateways```
8) Click the ```All Payment Gateways``` tab
     - To install GBPrimePay Checkout , click ```GBPrimePay Checkout```
9) Enter required details on the ```Manage Existing Gateways tab```
10) GBPrimePay Checkout Settings on the ```GBPrimePay Checkout```

## Recommended Module Content ##

The structure of this module is as follows.

```
 modules/gateways/
  |- callback/gbprimepay.php
  |- include-code
  |  gbprimepay.php
```
