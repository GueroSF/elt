### запуск из докера
переходим в директорию docker
запускаем ```docker-compose up --build```

"заходим" в контейнер ```docker exec -it tr-php-fpm fish```


устанавливаем зависимости ```composer install```


в браузере открываем ```localhost:8080```

### файл xml
путь к файлу задается через ```GUESTBOOK_FILE```

по умолчанию ```/app/var/guestbook.xml```
