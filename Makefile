PHP=$(shell which php)
CURL=$(shell which curl)
HOST=localhost
PORT=9999

install:
	$(PHP) -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));"
update:
	$(PHP) composer.phar update
server:
	$(PHP) -S $(HOST):$(PORT) ./app.php

