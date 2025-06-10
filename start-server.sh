#!/bin/bash
# Script to start PHP built-in server for nursing home website

PORT=8000
DIR=$(dirname "$0")

echo "Starting PHP built-in server at http://localhost:$PORT"
echo "Serving directory: $DIR"

php -S localhost:$PORT -t "$DIR"
