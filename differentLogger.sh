#!/usr/bin/env bash

URL="http://www..php"

CHANNEL="${1:-}"
USER="${2:-}"
MESSAGE="${3:-}"

if [[ -z "$CHANNEL" || -z "$USER" || -z "$MESSAGE" ]]; then
    echo "Usage: $0 <channel> <user> <message>"
    exit 1
fi

response=$(curl -sS -X POST "$URL" \
    --data-urlencode "channel=$CHANNEL" \
    --data-urlencode "user=$USER" \
    --data-urlencode "message=$MESSAGE" \
    --fail)

echo "Server response: $response"
