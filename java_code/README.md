# Java Autograder

This autograder provides JUnit testing of student-supplied code, assigning points for each test passed and optionally limiting the number of submissions.

### Instructions

Download the `java_code` directory.  There are three key entries:
- `config.json` - The configuration file for this autograder.
- `src` - Test code used to grade the student assignments.  This should be written as JUnit 4 test cases.  Provided files in `src` _may_ be overwritten by student submission.  Tests should be stored in the `src/main` directory.
- `provided` - Any Java code that you want to provide to the students.  Anything in this directory will overwrite a student-submitted file of the same name when grading occurs.

Edit the supplied `config.json` file (as shown below) to list the number of submission attempts allowed:
```json
{
    "submissions_allowed": 10
}
```
To allow any number of submissions, set the configuration parameter to 0.  On each submission, the student will get a notification about how many attempts they have used.  When the maximum allowed attempts have been reached, Gradescope _will_ still allow resubmission, but the autograder will not process it.  The results of the last submission within the allowed attempts will be displayed instead.

Save your JUnit test files in the `src/main` directory.  An example test file, `ExampleTests.java` is provided.  You will need to include the Gradescope test framework and provide the `GradedTest` annotations to each test included in the grade.  See the included file for example tests.  Update the `src/main/RunTests.java` file to list each class containing JUnit tests that you'd like to run.

Provide any starter code for students in the `src` directory.  That code will be overwritten by student uploads to Gradescope.  If there are any files that you would like to provide that should overwrite the student's submission, include those in the `provided` directory.

Lastly, zip up the contents of the `java_code` directory and upload as an autograder to Gradescope.

### Gradescope Results

When the students submit their code to Gradescope, the results will also include a statement about their submission count: 

```
You have used 4 submission(s) out of a maximum 10 allowed.
```

*Note:* If there are any compile-time errors, they will also be displayed to the student.
