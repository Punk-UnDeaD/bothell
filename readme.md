#установка докера
docker-compose up -d

#генерация тасок
docker-compose exec bin/console app:generate:tasks

#обработка тасок
docker-compose exec bin/console app:process:allTasks

