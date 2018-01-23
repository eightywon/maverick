#maverick  

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
test the range and work out any issues  
how often to log to db? every time sniffed? once per minute/half minute? average all sniffs or just log most recent?  
clean up code  
present project online  
back up flash card  
  
http://stevenhickson.blogspot.com/2013/05/using-google-voice-c-api.html  
http://forum.arduino.cc/index.php?topic=22052.0  
  
COMPILE!  
   
sudo gcc -o /var/www/html/maverick maverick.c -lwiringPi -lsqlite3  
  
  
http://www.tutorialspoint.com/sqlite/sqlite_c_cpp.htm  
https://developers.google.com/chart/interactive/docs/php_example?csw=1  
https://github.com/BjoernSch/MaverickBBQ/blob/master/maverick.py  
https://forums.adafruit.com/viewtopic.php?f=8&t=25414&start=15  
https://hackaday.com/2015/03/25/logic-noise-filters-and-drums/#more-150438  
http://raspberrywebserver.com/sql-databases/set-up-an-sqlite-database-on-a-raspberry-pi.html  
https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md  


webserver - nginx  
DBMS - sqlite3  
interface language - php5-fpm   

to get maverick executable to launch from webportal hosted by nginx when start cook button is clicked you have to add www-data user to /etc/sudoers so the program can be launched via php exec command with sudo flag  

to allow pi to send alerts install postfix SMTP program and set up relay through comcast mail server like - http://forums.xfinity.com/t5/E-Mail-and-Xfinity-Connect-Help/Mac-OS-X-Proper-postfix-configuration-for-SMTP/m-p/1092577#M191652  

sqlite3 db (the.db) schema: https://i.imgur.com/qw5fw2T.jpg, /var/www/html/the.db

launch from terminal- $sudo /var/www/html/maverick > ~/maverick.log &
