#!/usr/bin/env bash

mkdir -p bin
# Find all java files in src directory
java_files=$(find src -name "*.java")
javac -cp lib/*:. -d bin $java_files
