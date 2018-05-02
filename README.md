todo:  
1. Replace references to wiringPi with pigpio steps - http://abyz.me.uk/rpi/pigpio/download.html  
2. Add "PRAGMA journal_mode=WAL;" to db.script to activate write-ahead logging mode in the sqlite DB which helps reduces write conflicts 
3. Possibly update with additional hardware stuff  
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
6 (GND) - to GND on receiver  
10 (BCM15 RXD) - to DATA on receiver  

Install steps as of 4/2018 (Raspbian stretch lite version March 2018) (work in progress):  
1. Download Raspbian Stretch Lite - https://www.raspberrypi.org/downloads/raspbian/  
2. Unzip img, burn with Etcher - https://etcher.io/  
3. Insert SD card, connect HDMI and keyboard to pi, power on  
4. Log in: pi, raspberry  
5. sudo raspi-config, 2 - Network Config, N1 (set hostname if desired), N2 (configure wifi)  
6. Reboot  
7. sudo apt-get update, then sudo apt-get dist-upgrade  
8. sudo raspi-config  
  a. 1 - change password (if desired)  
  b. 4 - Localisation options (if desired)  
  c. 5 - Interfacing options  
     1. P2 - Enable SSH  
     2. P6 - Turn off shell interface over serial (no to shell interface, yes to enable serial HW)  
9. Reboot  
10. sudo apt-get install git (may have to sudo apt-get updated again first)  
11. git clone https://github.com/eightywon/maverick  
12. Install wiringPi  
   a. git clone git://git.drogon.net/wiringPi  
   b. cd ~/wiringPi  
   c. ./build  
   d. gpio -v to verify install  
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
   b. sudo gcc -o /var/www/html/maverick maverick.c -lwiringPi -lsqlite3  
19. Enable nginx user www-data to execute and kill maverick executable  
   a. sudo visudo  
   b. add "www-data ALL=(ALL) NOPASSWD: /var/www/html/maverick.sh, /bin/kill" as last line
20. Set ownership/permissions on /var/www/html directory and contents  
   a. sudo chown www-data:www-data /var/www/html  
   b. sudo chown -R www-data:www-data /var/www/html/*  

--

links/research/notes (not maintained):
http://forums.adafruit.com/viewtopic.php?f=8&t=25414  
http://wiki.openpicus.com/index.php?title=Wifi_bbq  
http://www.raspberrypi.org/phpBB3/viewtopic.php?f=37&t=29650  
http://www.raspberrypi.org/phpBB3/viewtopic.php?p=252739#p252739  
http://openmicros.org/index.php/articles/94-ciseco-product-documentation/raspberry-pi/283-setting-up-my-raspberry-pi  
sending and receiving via UART with minicom   
http://www.raspberry-projects.com/pi/programming-in-c/uart-serial-port/using-the-uart  
try this too http://www.raspberrypi.org/phpBB3/viewtopic.php?f=63&t=32953  
http://elinux.org/Serial_port_programming  
pic of pinout  
http://eclipsesource.com/blogs/2012/10/17/serial-communication-in-java-with-raspberry-pi-and-rxtx/  
right dev and pins to use  
http://raspberrypihobbyist.blogspot.com/2012/08/raspberry-pi-serial-port.html  
good stuff  
http://ninjablocks.com/blogs/how-to/7506204-adding-433-to-your-raspberry-pi  
http://www.raspberrypi.org/phpBB3/viewtopic.php?t=53425&p=408648  
For later  
http://www.cl.cam.ac.uk/projects/raspberrypi/tutorials/temperature/  
http://www.raspberrypi.org/phpBB3/viewtopic.php?p=237517#p237517  
http://www.raspberrypi.org/phpBB3/viewtopic.php?f=37&t=29650  
compiling with mysql headers  
http://www.raspberrypi.org/phpBB3/viewtopic.php?t=31394&p=393917  
session control - how to recognize when  the maverick is turned off between smokes and start a new session  
improve front-end - graphs, bbq-like temp gauges  
how often to log to db? every time sniffed? once per minute/half minute? average all sniffs or just log most recent?  
clean up code  
http://stevenhickson.blogspot.com/2013/05/using-google-voice-c-api.html  
http://forum.arduino.cc/index.php?topic=22052.0   
http://www.tutorialspoint.com/sqlite/sqlite_c_cpp.htm  
https://developers.google.com/chart/interactive/docs/php_example?csw=1  
https://github.com/BjoernSch/MaverickBBQ/blob/master/maverick.py  
https://forums.adafruit.com/viewtopic.php?f=8&t=25414&start=15  
https://hackaday.com/2015/03/25/logic-noise-filters-and-drums/#more-150438  
http://raspberrywebserver.com/sql-databases/set-up-an-sqlite-database-on-a-raspberry-pi.html  
https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md  

to allow pi to send alerts install postfix SMTP program and set up relay through comcast mail server like - http://forums.xfinity.com/t5/E-Mail-and-Xfinity-Connect-Help/Mac-OS-X-Proper-postfix-configuration-for-SMTP/m-p/1092577#M191652  

launch from terminal- $sudo /var/www/html/maverick > ~/maverick.log &


