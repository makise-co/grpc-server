PROTO_API_DIR 	:= api
PROTO_OUT_DIR 	:= gen

.PHONY: gen-proto
gen-proto:
	mkdir -p ${PROTO_OUT_DIR}
	protoc \
		-I ${PROTO_API_DIR} \
		--go_out=plugins=grpc,paths=source_relative:./${PROTO_OUT_DIR} \
		--php_out=plugins=grpc,paths=source_relative:./${PROTO_OUT_DIR} \
		--include_imports --include_source_info \
		--descriptor_set_out=${PROTO_OUT_DIR}/api.pb \
		./${PROTO_API_DIR}/example/*proto
