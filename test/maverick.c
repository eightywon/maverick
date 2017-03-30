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
unsigned int probe1=0, probe2=0;
unsigned int probe1_array[6], probe2_array[6];
sqlite3 *db;
char *zErrMsg=0;
int rc;
unsigned int  last_db_write;
unsigned int lastPinState;

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

static int callback(void *NotUsed, int argc, char **argv, char **azColName){
   int i;
   for(i=0; i<argc; i++){
      printf("%s = %s\n", azColName[i], argv[i] ? argv[i] : "NULL");
   }
   printf("\n");
   return 0;
}

void outputData(void)
{
        unsigned int i=0;
        probe1 = probe2 = 0;
        if (    (save_array[0] == 0xAA) &&
                (save_array[1] == 0x99) &&
                (save_array[2] == 0x95) &&
                (save_array[3] == 0x59) )
        {
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

		if (probe1==858992533) {
			probe1=0;
		}
		if (probe2==858992533) {
			probe2=0;
		}

                printf("Probe 1:%d\tProbe 2:%d\t@%d\n",probe1,probe2,micros());

		/*
                FILE *of;
                char outputFilename[] = "/var/www/html/index.htm";
                of = fopen(outputFilename, "a");
                if (!of)
                {
                        printf("File open failed!\n");
                }
		*/

                time_t now = time(NULL);
                char buff[20];
                strftime(buff, 20, "%Y-%m-%d %H:%M:%S", localtime(&now));
                //fprintf(of, "Probe 1: %d* | Probe 2: %d* | %s<br />\n", probe1, probe2,buff);
                //fclose(of);

		if (probe1<500 && probe2<500)
		{
		if (last_db_write==0 || (millis()-last_db_write>=30000))
		{
			char sql[100];
			snprintf(sql,100,"INSERT INTO maverick VALUES ('%s', %d, %d);",buff,probe1,probe2);
			printf("%s\n",sql);
			rc=sqlite3_exec(db,sql,callback,0,&zErrMsg);
			if (rc!=SQLITE_OK)
			{
				printf("SQL error: %s\n",zErrMsg);
			}
			last_db_write=millis();
		}
		}
/*
                MYSQL *connection;
                MYSQL_RES *result;
                MYSQL_ROW row;
                char query[100];
                connection = mysql_init(NULL);
                if (!mysql_real_connect(connection, "localhost", "root", "", "maverick", 0, NULL, 0))
                {
                        fprintf(stderr, "%s\n", mysql_error(connection));
                }
                snprintf(query, 100, "INSERT INTO data VALUES (%d, %d, '%s', 1), probe1, probe2, buff );
                //send query
                mysql_query(connection, query);
                mysql_close(connection);
*/
        }
}

void myInterrupt (void)
{
        unsigned int time_since_last = 0, currentPinState=digitalRead(PIN);
        unsigned int tsl_micros = 0;
        unsigned int bit_ok = 0, i;
        //get the time since last interrupt in milli and micro seconds
        time_since_last = (millis() - last_interrupt_millis);
        tsl_micros = (micros() - last_interrupt_micros);
        //here we're attempting to detect the Maverick's preamble - 8x pulses of ~5ms each, spaced at ~250us
        if (detection_state == STATE_START_PULSES)
        {

                //if last interrupt was seen between 3ms and 7ms ago
                //if (((time_since_last > 3) && (time_since_last < 7)) && digitalRead(PIN))
				//if (time_since_last==5 && digitalRead(PIN))
		if ((tsl_micros>4800 && tsl_micros<5300) && currentPinState==1)
                {
                        start_pulse_counter++;
                        if (start_pulse_counter == 7)
                        {
                                printf("Preamble detected @%d\n", millis());
                                start_pulse_counter = 0;
                                detection_state = STATE_FIRST_BIT;
                        }
			else {
	                        printf("*TRIGGER* Since last pulse:%dms (%dms), Time from start:%dms, Pulse count:%d \n",time_since_last, tsl_micros,millis(), start_pulse_counter);
	        	}
		}
                /*
                else if (tsl_micros > 400)
                {
                        start_pulse_counter = 0;
                }
                */
        }
        /*
        else if (detection_state == STATE_FIRST_BIT && digitalRead(PIN))
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
                                current_bit=digitalRead(PIN);
                        }
                }
                if ((tsl_micros > 375) && (tsl_micros < 600))
                {
                        if (short_bit == 1)
                        {
                                //expected a short bit and something went wrong
                                //start over at getting preamble
                                detection_state = STATE_START_PULSES;
                                printf("\n!!!PATTERN FAILURE!!! @%d\n",millis());
			}
                        bit_count++;
                        //printf("%d",digitalRead(PIN));
                        current_bit=digitalRead(PIN);
                        bit_ok = 1;
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
        */
		//store current interrupt time to calculate time since last (above)
		last_interrupt_micros = micros();
		last_interrupt_millis = millis();
}

int main(int argc, char **argv)
{
        //wiringPiSetupSys();
	wiringPiSetup();
        pinMode(PIN, INPUT);
        printf("Starting on wiringPi PIN %d, gpio PIN %d\n",PIN,wpiPinToGpio(PIN));
        wiringPiISR(PIN, INT_EDGE_BOTH, &myInterrupt);
        piHiPri(50);
	rc=sqlite3_open("/var/www/html/maverick.db",&db);
	if (rc)
	{
		printf("Can't open db: %s\n",sqlite3_errmsg(db));
	}
	else
	{
		printf("db opened\n");
	}
        for (;;)
        {
                //printf("Waiting ... \n"); fflush (stdout);
		delay(60000);
        }
	sqlite3_close(db);
        return 0;
}
