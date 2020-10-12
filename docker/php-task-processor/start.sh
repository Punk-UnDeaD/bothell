if [ -f ~/.stopped ]; then (rm  ~/.stopped) fi

until [ -f ~/.stopped ];
  do console app:process:allTasks;
done