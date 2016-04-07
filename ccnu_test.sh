#!/bin/sh
!gcc -o ccnu ccnu.c -ldl -lfcgi
killall ccnu
killall nginx

spawn-fcgi -a 127.0.0.1 -p 9001 -f /www/ccnu
nginx
