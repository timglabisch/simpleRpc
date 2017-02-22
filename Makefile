monit:
	docker run -p 9090:9090 -v $(shell pwd)/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml  prom/prometheus

angel:
	php server.php angel --port-client=1338,1339,1340,1341 --worker-count=5

server:
	php server.php server --port-client=1338  --port-worker=1390  --port-admin=1190 -vvv

debug_server:
	xdebug server.php server --port-client=1338  --port-worker=1390  --port-admin=1190 -vvv

debug_worker:
	RPC_SERVER=127.0.0.1:1390 xdebug worker.php -vvv

worker:
	RPC_SERVER=127.0.0.1:1390 php worker.php -vvv

proto:
	protoc --php_out=src/SimpleRPCMessage/Generated rpc.proto
