# Gradescope Autograder Examples

These are plug-and-play Gradescope autograders.  With a few settings changes, they should be ready to use out of the box.  

## Included Examples

This repository includes multiple example autograders.  View the readme file in each autograder for more information on set up.

- java\_code: This autograder runs JUnit 4 tests and optionally limits the number of submissions.
- java\_coverage: This autograder runs code coverage of student-supplied code with student-supplied JUnit 4 test cases.  It assigns a grade based on percent coverage and optionally allows limiting the number of submissions
- text\_matching: This autograder looks for submitted files by name and can check for content using regular expressions or string matching.  It requires more configuration than the other examples.
