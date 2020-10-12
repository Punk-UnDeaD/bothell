### установка докера
docker-compose up -d

### применение миграций
console doctrine:migrations:migrate -n

### генерация тасок
docker-compose exec php console app:generate:tasks --total=10000 --users=1000

### обработка тасок одного пользователя
docker-compose exec php console app:process:task \<uid\>

Аргументы: uid - User id

### обработка тасок
docker-compose exec php console app:process:allTasks --pack=200 --threads=16

в данный момент выполняется автоматически в отдельном контейнере

### обработка тасок с максимальной конкуренцией
docker-compose exec php console app:process:allTasks --pack=1000 --threads=1000


### принудительно остановить контейнер с обработчиком тасок
docker-compose kill php-task-processor

### отправить сигнал на оснановку контейнеру с обработчиком тасок
docker-compose exec php-task-processor sh /stop.sh