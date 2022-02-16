#!/bin/bash

zip -r release.zip . -x *.git* -x build.sh -x Docker* -x docker* -x DOCKER* -x output.log
