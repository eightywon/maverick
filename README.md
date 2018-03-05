#maverick  

webserver - nginx  
DBMS - sqlite3  
interface language - php7-fpm   

1. update, dist-upgrade
2. sudo rasp-config, usual setup, then 5 - Interfacing Options, P6 - Serial, No, Yes
3. sudo apt-get install git
4. git clone https://github.com/eightywon/maverick
5. git clone git://git.drogon.net/wiringPi, cd ~/wiringPi ./build
6. sudo apt-get install libsqlite3-dev, sqlite3
7. sudo apt-get install nginx
8. sudo apt-get install php-fpm, configure for nginx: https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md
9. sudo apt-get install php-sqlite3
10. sudo visudo, add "www-data ALL=(ALL) NOPASSWD: /var/www/html/maverick.sh, /bin/kill" as last line
11. copy html files to /var/www/html/
12. sudo sqlite3 -init maverick/db.script /var/www/html/the.db
13. gcc -o /var/www/html/maverick maverick.c -lwiringPi -lsqlite3


links/research/notes (not maintained)  

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


