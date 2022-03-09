##Sparsh Social Share Extension
Social Share extension is an effective tool to allow your customers to instantly share their favorite products, categories, homepage or any other cms pages on the most popular social networks like Facebook, Twitter, Google, Whatsapp, Pinterest and many more.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the files
3. Create a folder [Magento_Root]/app/code/Sparsh/SocialShare
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/SocialShare'

#Enable Extension:
- php bin/magento module:enable Sparsh_SocialShare
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_SocialShare
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush