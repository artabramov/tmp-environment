if [ ! -f ./phpunit.phar ]; then
    wget https://phar.phpunit.de/phpunit.phar
fi

search_dir="./tests"
for entry in "$search_dir"/*Test.php
do
  echo -e "\n[ $entry ]\n"
  php phpunit.phar $entry
done
