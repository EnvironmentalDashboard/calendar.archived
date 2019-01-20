#!/bin/bash
rm /var/www/html/index.html
mkdir /var/secret && cat /var/www/html/includes/conn.php > /var/secret/local.php
/usr/sbin/apache2ctl -D FOREGROUND
