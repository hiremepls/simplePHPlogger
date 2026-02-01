#!/bin/bash

# Configuration
ENDPOINT="http://yourserver.com/log.php"  # Change to your PHP endpoint
PERIOD_SECONDS=60                         # Change to your desired period in seconds
PARAM1="value1"                           # Change to your parameter values
PARAM2="value2"
PARAM3="value3"

# Function to send data
send_data() {
    curl -X POST "$ENDPOINT" \
         -d "param1=$PARAM1" \
         -d "param2=$PARAM2" \
         -d "param3=$PARAM3"
    echo "Data sent at $(date)"
}

# Main loop
while true; do
    send_data
    sleep $PERIOD_SECONDS
done
