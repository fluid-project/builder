#!/bin/bash
find ~/website/infusion-builder-secure/tmp/build/ -mtime +1 -exec rm -Rf {} \;
find ~/website/infusion-builder-secure/tmp/products/ -mtime +1 -exec rm -Rf {} \;
