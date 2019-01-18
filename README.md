todo:  
1. Possibly update with additional hardware stuff  
   a. Dipole antenna  
   b. Other reciever modules  
   c. Case options  

--  

Use a Raspberry Pi to monitor Maverick ET-732 temperature readings and provide an interface to access that information.

Back end software (maverick.c) sniffs the 433mhz radio signal from the Maverick ET-732 (and likely other models), which transmits temprerature readings every 12 seconds, and stores those readings to a sqlite3 database. Front end software (php on nginx) provides an interface to start/stop cooks, manage cook related information, and view temp gauges and graphs

Software:  
web server - nginx  
DBMS - sqlite3  
interface - php7-fpm   
433mhz sniffer/parser - C  

Hardware:  
Raspberry PI  
433mhz receiver (http://a.co/fe3oOx3)  

GPIO Pinout (physical pin numbers):  
2 (5v) - to 5v pin on receiver  
34 (GND) - to GND on receiver  
31 (BCM6) - to DATA on receiver  

Install steps as of 5/2018 (Raspbian stretch lite version March 2018) (work in progress):  
1. Download Raspbian Stretch Lite - https://www.raspberrypi.org/downloads/raspbian/  
2. Unzip img, burn with Etcher - https://etcher.io/  
3. Insert SD card, connect HDMI and keyboard to pi, power on  
4. Log in: pi, raspberry  
5. sudo raspi-config, 
  a. 2 - Network Config, N1 (set hostname if desired), N2 (configure wifi)  
  b. 5 - Interfacing options, P2 - Enable SSH  
6. Reboot  
7. sudo apt-get update, then sudo apt-get dist-upgrade  
8. sudo raspi-config  
  a. 1 - change password (if desired)  
  b. 4 - Localisation options (if desired)  
  c. 5 - Interfacing options, P6 - Turn off shell interface over serial (no to shell interface, yes to enable serial HW)  
9. Reboot  
10. sudo apt-get install git (may have to sudo apt-get updated again first)  
11. git clone https://github.com/eightywon/maverick  
12. Install PIGPIO (see http://abyz.me.uk/rpi/pigpio/download.html)  
   a. rm pigpio.zip  
   b. sudo rm -rf PIGPIO  
   c. get abyz.me.uk/rpi/pigpio/pigpio.zip  
   d. unzip pigpio.zip  
   e. cd PIGPIO  
   f. make  
   g. sudo make install  
13. sudo apt-get install nginx   
14. sudo apt-get install libsqlite3-dev sqlite3  
15. Install php-fpm and sqlite3 php library  
   a. sudo apt-get install php-fpm  
   b. sudo apt-get install php-sqlite3  
   c. configure for nginx (ENABLE PHP IN NGINX section): https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md  
16. Copy maverick html files to nginx web root  
   a. cd ~/maverick/html  
   b. sudo cp -r * /var/www/html  
17. Create the database  
   a. cd ~/maverick  
   b. sudo sqlite3 -init ./db.script /var/www/html/the.db  
   c. .fullschema to verify the db  
   d. .quit to exit  
18. Build the maverick executable  
   a. cd ~/maverick  
   b. sudo gcc -o /var/www/html/maverick maverick.c -lpigpio -lsqlite3  
19. Enable nginx user www-data to execute and kill maverick executable  
   a. sudo visudo  
   b. add "www-data ALL=(ALL) NOPASSWD: /var/www/html/maverick.sh, /bin/kill" as last line
20. Set ownership/permissions on /var/www/html directory and contents  
   a. sudo chown www-data:www-data /var/www/html  
   b. sudo chown -R www-data:www-data /var/www/html/*  

