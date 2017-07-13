clean:
	docker ps -aq | xargs docker rm -vf; docker images -aq | xargs docker rmi -f

build:
	docker build -t datafeed-wordpress .

run-mysql:
	docker run --name wpmysql -e MYSQL_ROOT_PASSWORD=secret -d mysql:latest

run-wp:
	$ docker run --name datafeed-wordpress --link wpmysql:mysql -p 8080:80 -d datafeed-wordpress

rsync:
	rsync -e "docker exec -i" --blocking-io -avz --delete --exclude=".git" . datafeed-wordpress:/var/www/html/wp-content/plugins/awin-data-feed

start: run-mysql run-wp

stop:
	docker stop wpmysql datafeed-wordpress

.PHONY: clean run-mysql run-wp start stop rsync build
