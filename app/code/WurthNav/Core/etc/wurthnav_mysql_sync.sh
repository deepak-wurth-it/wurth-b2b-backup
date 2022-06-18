#!/bin/sh


 ####mysqldump '-h172.30.54.147' '-P3306' '-udeepak' '-p7SkdP!sq'  WurthNavDemo IOS IOSDetails Offer OfferItems SalesPrice SalesLineDiscount > WurthNavDemo.sql | mysql '-uroot' '-pu3rwUGgf8qxbXXEX' wc_b2b_m2 < WurthNavDemo.sql
# mysqldump '-h172.30.54.147' '-P3306' '-udeepak' '-p7SkdP!sq'  WurthNavDemo IOS IOSDetails Offer OfferItems SalesPrice SalesLineDiscount | mysql '-uroot' '-pu3rwUGgf8qxbXXEX' wc_b2b_m2 

LOCAL_HOST='127.0.0.1'
readonly LOCAL_HOST

LOCAL_DB='wc_b2b_m2'
readonly LOCAL_DB

REMOTE_DB='WurthNavDemo'
readonly REMOTE_DB

LOCAL_USER='root'
readonly LOCAL_USER

LOCAL_PASS='u3rwUGgf8qxbXXEX'
readonly LOCAL_PASS

#user@host or host

REMOTE_HOST='172.30.54.147'
readonly REMOTE_HOST

REMOTE_USER='deepak'
readonly REMOTE_USER

REMOTE_PASS='7SkdP!sq'
readonly REMOTE_PASS

REMOTE_PORT='22'
readonly REMOTE_PORT

#Tables
TABLES='IOS IOSDetails Offer OfferItems SalesPrice SalesLineDiscount'
readonly TABLES

# # Set default file permissions
umask 177
 #echo $TABLES

echo "Start clear db tables in  $LOCAL_DB"

echo "delete table in  database IF EXISTS $LOCAL_DB;" | mysql -u$LOCAL_USER -p$LOCAL_PASS
echo "CREATE table DATABASE $LOCAL_DB DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;" | mysql -u$LOCAL_USER -p$LOCAL_PASS

echo "show databases;" | mysql -u$LOCAL_USER -p$LOCAL_PASS

echo "Start copy from $REMOTE_DB ($REMOTE_HOST) to $LOCAL_DB"



 mysqldump "-h$REMOTE_HOST" "-P3306" "-u$REMOTE_USER" "-p$REMOTE_PASS" $REMOTE_DB $TABLES  | mysql "-u$LOCAL_USER" "-p$LOCAL_PASS" "$LOCAL_DB" 

echo "Done... (in $SECONDS sec.)"
