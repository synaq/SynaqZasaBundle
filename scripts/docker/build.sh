#!/bin/bash
pushd dockerContext
cp ../composer.json ./
cp ../composer.lock ./
docker build . -t synaq/zimbra-connector-dev:latest --platform linux/amd64
rm -f ./composer.*
popd
