search_dir="./tests"
for entry in "$search_dir"/*
do
  echo -e "\n[ $entry ]\n"
  php phpunit.phar $entry
done
