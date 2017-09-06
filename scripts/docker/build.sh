#!/bin/bash
pushd dockerContext
cp ../composer.json ./
cp ../composer.lock ./
docker build . -t synaq/zimbra-connector-dev:latest
rm -f ./composer.*
popd
