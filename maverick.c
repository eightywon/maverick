#define STATE_START_PULSES      0
#define STATE_FIRST_BIT         1
#define STATE_DATA              2
#define PIN                     16

#include <wiringPi.h>
#include <stdio.h>
#include <stdlib.h>
#include <wiringSerial.h>
#include <sqlite3.h>
#include <time.h>

unsigned int volatile start_pulse_counter = 0, detection_state = 0;
unsigned int volatile last_interrupt_micros = 0, last_interrupt_millis;
unsigned int fd, data_array_index = 0, data_array[13], shift_value = 0, short_bit = 0, add_1st_bit = 1, current_byte = 0, current_bit = 1, bit_count = 0, ISR_status, save_array[13];
int probe1=0, probe2=0;
int prevProbe1=0, prevProbe2=0;
unsigned int probe1_array[6], probe2_array[6];
sqlite3 *db;
char *zErrMsg=0;
const char *email;
int rc,pitLow,pitHi,foodLow,foodHi;
unsigned int  last_db_write, cookID;

unsigned int time_since_last = 0;
unsigned int tsl_micros = 0;
unsigned int bit_ok = 0, i;
int current_micros, current_millis;
unsigned int pin_state;
unsigned int firstRead=1, goodData, badReadCount;

// make the quarternary convertion
unsigned int quart(unsigned int param)
{
	 param &= 0x0F;
	if (param==0x05)
		return(0);
        if (param==0x06)
                return(1);
        if (param==0x09)
                return(2);
        if (param==0x0A)
                return(3);
}

static int callback(void *data, int argc, char **argv, char **colName)
{
	int i;
	for(i=0; i<argc; i++)
	{
	}
	return 0;
}

void sendAlert(char *p,char *s,const char *e,int t) {
	FILE *of;
        char ofName[]="/tmp/email";
        of=fopen(ofName,"w");
        fprintf(of,"From: <www-data> www-data@dev\n");
        fprintf(of,"To: %s\n",e);
        fprintf(of,"Subject: %s %s Alert\n",p,s);
        fprintf(of,"%s Temp: %d\n",p,t);
        fprintf(of,"http://192.168.1.111/alerts.php");
        fclose(of);
        system("sendmail -t -F 'www-data' -f 'www-data@dev' < /tmp/email");
}

void outputData(void)
{
        unsigned int i=0;

	if (firstRead==0) {
		if (goodData==1) {
		badReadCount=0;
		prevProbe1=probe1;
		prevProbe2=probe2;
		}
	}
        //probe1 = probe2 = 0;

        if (    (save_array[0] == 0xAA) &&
                (save_array[1] == 0x99) &&
                (save_array[2] == 0x95) &&
                (save_array[3] == 0x59) )
        {
        probe1 = probe2 = 0;

                probe2_array[0]= quart(save_array[8] & 0x0F);
                probe2_array[1]= quart(save_array[8] >> 4);
                probe2_array[2]= quart(save_array[7] & 0x0F);
                probe2_array[3]= quart(save_array[7] >> 4);
                probe2_array[4]= quart(save_array[6] & 0x0F);
                probe1_array[0]= quart(save_array[6] >> 4);
                probe1_array[1]= quart(save_array[5] & 0x0F);
                probe1_array[2]= quart(save_array[5] >> 4);
                probe1_array[3]= quart(save_array[4] & 0x0F);
                probe1_array[4]= quart(save_array[4] >> 4);
                for (i=0;i<=4;i++)
                {
                        probe1 += probe1_array[i] * (1<<(2*i));
                        probe2 += probe2_array[i] * (1<<(2*i));
                }

                probe1 -= 532;
                probe1 = (((probe1 * 9)/5) + 32);

                probe2 -= 532;
                probe2 = (((probe2 * 9)/5) + 32);

		if (probe1>=858992500 || probe1<0) {
			probe1=0;
		}
		if (probe2>=858992500 || probe2<0) {
			probe2=0;
		}

		goodData=1;
		if (firstRead==0) {
			if ((probe1<(prevProbe1-20)) || (probe1>(prevProbe1+20))) {
				badReadCount++;
				if (badReadCount<3) {
					goodData=0;
					//probe1=prevProbe1;
					printf("Bad data #%d - Probe 1:%d\tPrevProbe 1:%d\t@%d\n",badReadCount,probe1,prevProbe1,millis()/1000);
				} else {
					goodData=1;
				}
			} else if ((probe2<(prevProbe2-20)) || (probe2>(prevProbe2+20))) {
				badReadCount++;
				if (badReadCount<3) {
					goodData=0;
					//probe2=prevProbe2;
					printf("Bad data #%d - Probe 2:%d\tPrevProbe 2:%d\t@%d\n",badReadCount,probe2,prevProbe2,millis()/1000);
				} else {
					goodData=1;
				}
			}
		}

		firstRead=0;

                printf("Probe 1:%d\tProbe 2:%d\t@%d\n",probe1,probe2,millis()/1000);

		/*
                FILE *of;
                char outputFilename[] = "/var/www/html/index.htm";
                of = fopen(outputFilename, "a");
                if (!of)
                {
                        printf("File open failed!\n");
                }
		*/

		//get alerts info
		/*
		if (millis()-last_db_write>=30000)
		{
			sqlite3_stmt *res;
			char *getalerts="SELECT pitLow,pitHi,foodLow,foodHi,email FROM cooks WHERE id=?";
			rc=sqlite3_prepare_v2(db,getalerts,-1,&res,0);
			if (rc==SQLITE_OK)
			{
				sqlite3_bind_int(res,1,cookID);
			}
			int step=sqlite3_step(res);
			if (step==SQLITE_ROW)
			{
				pitLow=sqlite3_column_int(res,0);
				pitHi=sqlite3_column_int(res,1);
				foodLow=sqlite3_column_int(res,2);
				foodHi=sqlite3_column_int(res,3);
				email=sqlite3_column_text(res,4);
				printf("pL:%d, pH:%d, fL:%d, fH:%d, E:%s\n",pitLow,pitHi,foodLow,foodHi,email);
			}
			sqlite3_finalize(res);

			char *p, *s;
			if (probe1<pitLow) {
				p="Pit", s="Low";
				sendAlert(p,s,email,probe1);
			}
			if (probe1>pitHi) {
				p="Pit", s="High";
				sendAlert(p,s,email,probe1);
			}
			if (probe2<foodLow) {
				p="Food", s="Low";
				sendAlert(p,s,email,probe2);
			}
			if (probe2>foodHi) {
				p="Food", s="High";
				sendAlert(p,s,email,probe2);
			}

		}
		*/

		char sql[100];
                time_t now = time(NULL);
		char buff[20];
                strftime(buff, 20, "%Y-%m-%d %H:%M:%S", localtime(&now));
                //fprintf(of, "Probe 1: %d* | Probe 2: %d* | %s<br />\n", probe1, probe2,buff);
                //fclose(of);
		//if (probe1<500 && probe2<500)
		if (goodData==1)
		{
			if (last_db_write==0 || (millis()-last_db_write>=10000))
			{
				snprintf(sql,100,"INSERT INTO readings (cookid,time,probe1,probe2) VALUES (%d,'%s',%d,%d);",cookID,buff,probe1,probe2);
				printf("%s\n",sql);
				rc=sqlite3_exec(db,sql,callback,0,&zErrMsg);
				if (rc!=SQLITE_OK)
				{
					printf("SQL error: %s\n",zErrMsg);
				}
				else {
					last_db_write=millis();
				}
			}
		}
        }
}

void myInterrupt (void)
{
	current_micros=micros();
	current_millis=millis();
	pin_state=digitalRead(PIN);

	time_since_last = 0;
	tsl_micros = 0;
	bit_ok = 0;

        //get the time since last interrupt in milli and micro seconds
        //time_since_last = (millis() - last_interrupt_millis);
        time_since_last = (current_millis - last_interrupt_millis);

        //tsl_micros = (micros() - last_interrupt_micros);
        tsl_micros = (current_micros - last_interrupt_micros);

        //store current interrupt time to calculate time since last (above)
        //last_interrupt_micros = micros();
        last_interrupt_micros = current_micros;
        //last_interrupt_millis = millis();
        last_interrupt_millis = current_millis;

        //here we're attempting to detect the Maverick's preamble - 8x pulses of ~5ms each, spaced at ~250us
        if (detection_state == STATE_START_PULSES)
        {
                //if last interrupt was seen between 3ms and 7ms ago
                //if (((time_since_last > 3) && (time_since_last < 7)) && digitalRead(PIN))
		//if (time_since_last==5 && digitalRead(PIN))
		if (time_since_last==5 && pin_state==1)
                {
                        start_pulse_counter++;
                        if (start_pulse_counter == 8)
                        {
                                printf("Preamble detected @%d\n", current_millis);
                                start_pulse_counter = 0;
                                detection_state = STATE_FIRST_BIT;
                        }
			else if (start_pulse_counter>4) {
				printf("*TRIGGER* Since last pulse:%dms (%dms), Time from start:%ds, Pulse count:%d \n",time_since_last, tsl_micros,(current_millis/1000), start_pulse_counter);
			}
                }
                else if (tsl_micros > 400)
                {
                        start_pulse_counter = 0;
                }
        }
        //else if (detection_state == STATE_FIRST_BIT && digitalRead(PIN))
        else if (detection_state == STATE_FIRST_BIT && pin_state==1)
        {
                detection_state = STATE_DATA;
                current_bit=1;
                current_byte=0;
                shift_value=0;
                data_array_index=0;
                bit_ok=0;
                short_bit=0;
                add_1st_bit = 1;
                bit_count = 1;
                //printf("1");
        }
        if (detection_state == STATE_DATA)
        {
                if ((tsl_micros > 150) && (tsl_micros < 350))
                {
                        if (short_bit == 0)
                        {
                                short_bit = 1;
                        }
                        else
                        {
                                bit_count++;
                                short_bit = 0;
                                bit_ok = 1;
                                //printf("%d",digitalRead(PIN));
                                //current_bit=digitalRead(PIN);
                                current_bit=pin_state;
                        }
                }
                if ((tsl_micros > 375) && (tsl_micros < 600))
                {
                        if (short_bit == 1)
                        {
                                //expected a short bit and something went wrong
                                //start over at getting preamble
                                detection_state = STATE_START_PULSES;
                                //printf("\n!!!PATTERN FAILURE!!! @%d\n",current_millis);
			} else {
	                        bit_count++;
        	                //printf("%d",digitalRead(PIN));
                	        //current_bit=digitalRead(PIN);
                	        current_bit=pin_state;
                        	bit_ok = 1;
			}
                }
                if (bit_ok)
                {
                        if (add_1st_bit)
                        {
                                current_byte = 0x01;
                                shift_value = 1;
                                add_1st_bit = 0;
                        }
                        current_byte = (current_byte << 1) + current_bit;
                        shift_value++;
                        if (shift_value == 8)
                        {
                                data_array[data_array_index++] = current_byte;
                                //printf("Byte %0d:0x%X @%d (%d)\n", data_array_index, current_byte, micros(), millis());
                                bit_count=0;
                                shift_value = 0;
                                current_byte = 0;
                        }
                        if (data_array_index == 9)
                        {
                                start_pulse_counter = 0;
				// printf("Flushing @%d (%d)\n",micros(), millis())
				detection_state = STATE_START_PULSES;
                                for (i=0;i<=9;i++)
                                {
                                        save_array[i] = data_array[i];
                                }
                                //printf("Outputting @%d (%d)\n",micros(), millis());
                                outputData();
                                //printf("Done @%d (%d)\n", micros(), millis());
                        }
                        bit_ok = 0;
                }
        }

	/*
	if (detection_state==STATE_START_PULSES) {
		printf("Procedure took %dus, pin state %d\n",micros()-current_micros,pin_state);
	}
	*/
}

int main(int argc, char **argv)
{
        //wiringPiSetupSys();
	wiringPiSetup();
        pinMode(PIN, INPUT);
        printf("Starting on wiringPi PIN %d, gpio PIN %d\n",PIN,wpiPinToGpio(PIN));
        wiringPiISR(PIN, INT_EDGE_BOTH, &myInterrupt);
        piHiPri(50);
	rc=sqlite3_open("/var/www/html/the.db",&db);
	if (rc)
	{
		printf("Can't open db: %s\n",sqlite3_errmsg(db));
	}
	else
	{
		printf("db opened\n");
                time_t now = time(NULL);
		char buff[20];
                strftime(buff, 20, "%Y-%m-%d %H:%M:%S", localtime(&now));
		char sql[100];
		snprintf(sql,100,"INSERT INTO cooks (start) VALUES ('%s');",buff);
		rc=sqlite3_exec(db,sql,callback,0,&zErrMsg);
		if (rc!=SQLITE_OK)
		{
			printf("SQL error: %s\n",zErrMsg);
		}
		else
		{
			cookID=sqlite3_last_insert_rowid(db);
			printf("Cook ID is %d\n",cookID);
		}

		//set active cook ID in DB for use in HTML interface
		snprintf(sql,100,"UPDATE activecook SET cookid=%d;",cookID);
		printf("SQL: %s\n",sql);
		rc=sqlite3_exec(db,sql,callback,0,&zErrMsg);
		if (rc!=SQLITE_OK) {
			printf("SQL error: %s\n",zErrMsg);
		}
	}
        for (;;)
        {
		delay(60000);
        }
	sqlite3_close(db);
        return 0;
}
