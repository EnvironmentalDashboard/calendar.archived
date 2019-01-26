#!/bin/bash
docker build -t calendar .
docker run -dit -p 4000:80 -e COMMUNITY="" --name oberlin-calendar calendar
# docker run -dit -p 4001:80 -e COMMUNITY="oberlin.org" --name obp-calendar calendar
