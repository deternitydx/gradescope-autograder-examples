#!/usr/bin/env bash

# Run the autograder
java -cp bin/:lib/* RunTests > /autograder/results/results.json
