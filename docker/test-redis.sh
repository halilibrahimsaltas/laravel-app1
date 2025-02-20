#!/bin/bash

echo "Redis bağlantısı test ediliyor..."
if redis-cli -h redis ping > /dev/null 2>&1; then
    echo "Redis bağlantısı başarılı!"
    exit 0
else
    echo "Redis bağlantısı başarısız!"
    exit 1
fi 