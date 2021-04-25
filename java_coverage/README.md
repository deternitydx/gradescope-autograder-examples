# Java Code-Coverage Autograder

This autograder produces a grade basesd on code coverage of student-provided JUnit testing of their own code.  It makes use of jacoco to calculate code coverage, then assigns a percentage score based on the percent of code coverage.

### Instructions

Download the `java_coverage` directory.  There are three key entries:
- `config.json` - The configuration file for this autograder.
- `provided` - Any Java code that you want to provide to the students.  Anything in this directory will overwrite a student-submitted file of the same name when grading occurs.
- `build.xml` - The ant build file used to compile and run student JUnit tests and calculate code coverage

Edit the supplied `config.json` file (as shown below):
```json
{
    "submissions_allowed": 15,
    "max_score": 100,
    "min_coverage_required": 10,
    "max_coverage_required": 90,
    "required_files": [
        "ClassOne.java",
        "ClassTwo.java"
    ]
}
```
The configuration file provides multiple options to tune the grading:
- `submissions_allowed` - Limits the number of submissions to the autograder.  To allow any number of submissions, set the configuration parameter to 0.  On each submission, the student will get a notification about how many attempts they have used.  When the maximum allowed attempts have been reached, Gradescope _will_ still allow resubmission, but the autograder will not process it.  The results of the last submission within the allowed attempts will be displayed instead.
- `max_score` - The maximum score (in points) to award based on the student's code coverage percentage.
- `min_coverage_required` and `max_coverage_required` - These parameters define the range of code coverage percentage required of the student submissions.  Any code coverage below the minimum will receive a 0.  Any coverage above the maximum will receive a score of `max_score`.  In between these values, the percentage will be scaled linearly to award points.
- `required_files` - Files that the students must upload before the autograder will run.  This can ensure that students upload all the files to be analyzed.

If there are any files that you would like to provide that should overwrite the student's submission, such as instructor-provided classes for which students should write tests, include those in the `provided` directory.

*Note:* Student JUnit test classes should end with "Test".  The `build.xml` file excludes those classes from code coverage analysis.

Lastly, zip up the contents of the `java_coverage` directory and upload as an autograder to Gradescope.
