### установка докера
docker-compose up -d

### применение миграций
console doctrine:migrations:migrate -n

### генерация тасок
docker-compose exec bin/console app:generate:tasks

### обработка тасок
docker-compose exec bin/console app:process:allTasks

в данный момент выполняется автоматически в отдельном контейнере
