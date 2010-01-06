#!/bin/bash
find ~/website/infusionBuilder-secure/tmp/build/ -mtime +1 -exec rm -Rf {} \;
find ~/website/infusionBuilder-secure/tmp/products/ -mtime +1 -exec rm -Rf {} \;
