#!/bin/bash

# Configuration
ENDPOINT="http://yourserver.com/log.php"  # Change to your PHP endpoint
PERIOD_SECONDS=60                         # Change to your desired period in seconds
MACHINE_NAME="my-server-01"               # Change to your machine's name

# Function to get CPU temperature (works on most Linux systems with lm-sensors)
get_cpu_temp() {
    if command -v sensors &> /dev/null; then
        sensors | awk '/^Core 0:/ {print $3}' | sed 's/[^0-9.]//g'
    else
        echo "NA"
    fi
}

# Function to get RAM usage in percentage
get_ram_usage() {
    free | awk '/Mem:/ {printf "%.1f", $3/$2*100}'
}

# Function to get CPU usage in percentage
get_cpu_usage() {
    top -bn1 | awk '/Cpu\(s\):/ {print $2 + $4}'
}

# Function to send data
send_data() {
    local temp=$(get_cpu_temp)
    local ram=$(get_ram_usage)
    local cpu=$(get_cpu_usage)

    curl -X POST "$ENDPOINT" \
         -d "machine=$MACHINE_NAME" \
         -d "temp=$temp" \
         -d "ram=$ram" \
         -d "cpu=$cpu"

    echo "Sent at $(date): Machine=$MACHINE_NAME, CPU Temp=$temp°C, RAM Usage=$ram%, CPU Usage=$cpu%"
}

# Main loop
while true; do
    send_data
    sleep $PERIOD_SECONDS
done
