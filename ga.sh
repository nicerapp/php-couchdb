#!/bin/sh

#-- THIS FILE UPDATES THE SOURCES BACK INTO THE GIT REPOSITORY THAT IS STORED ON https://github.com/nicerapp/nicerapp
#-- IT IS TO BE RUN BY THE DEVELOPER(S) OF nicerapp ONLY!
git add .
git commit -m "$1"
git push
