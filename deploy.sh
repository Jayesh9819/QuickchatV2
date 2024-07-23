#!/bin/bash

# Push to the main branch on origin
git push origin main

# Check if the first command was successful
if [ $? -eq 0 ]; then
    echo "Successfully pushed to origin main"
    
    # Push to the main branch on production
    git push production main
    
    # Check if the second command was successful
    if [ $? -eq 0 ]; then
        echo "Successfully pushed to production main"
    else
        echo "Failed to push to production main"
        exit 1
    fi
else
    echo "Failed to push to origin main"
    exit 1
fi

