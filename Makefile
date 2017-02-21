monit:
	docker run -p 9090:9090 -v $(shell pwd)/prometheus/prometheus.yml:/etc/prometheus/prometheus.yml  prom/prometheus

angel:
	php server.php angel --port-client=1338,1339,1340,1341 --worker-count=5

proto:
	protoc --php_out=src/SimpleRPCMessage/Generated rpc.proto
