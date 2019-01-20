#!/bin/bash
docker build -t calendar .
docker run -dit -p 8080:80 -v $(dirname $(pwd))/includes:/var/www/includes -h environmentaldashboard.org --name oberlin-calendar calendar
