#!/bin/bash

curl --fail http://localhost/ || exit 1
curl --fail http://localhost/slideshow || exit 1
