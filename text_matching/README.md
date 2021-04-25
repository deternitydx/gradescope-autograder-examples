# Text Upload Autograder 

This autograder statically examines uploaded files and provides a score based on instructor-provided critera.  It can include file length, string matching, and regular expressions. 

### Instructions

Download the `text_matching` directory.  There is one key entry:
- `grade.php` - The grading script for this autograder. 

Edit the supplied `grade.php` file's TODO items as follows:

Update the number of points that the autograder may award and list the number of parts (or tests) that the autograder will perform.  Each "part" consists of analyzing an uploaded file, will be reported as an autograder "test" to Gradescope, and will be awarded an equal fraction of the maximum points if passed.
```php
// Maximum number of points to award in this autograder
$maxPoints = 10;
// Number of files to examine.  Each will be listed as a separate "test" (or part) in the Gradescope display.
$numParts = 3;
```

Update the checks for each file in the `gradeUploadedFile` function.  The function receives as parameters:
- `$file` - the full contents of the uploaded file, and
- `$name` - the name of the uploaded file.
For each file found, any logic may be used to grade the file, such as string length of the file contents, matching on substrings, or regular expressions.  The files may also be run as desired from the script.  Once the score has been determined (here "did not pass" = 0 and "passed" = 1), use the `addToOutput` method to add the results to the final Gradescope return data.  I've provided some substring matching and string length examples in the sample file, as seen below:
```php
    if (strtolower($name) == "second-required-filename.txt") {
        // TODO: This example checks that the string "integer" is found in the submitted file at least 3 times
        if (strlen($file) > 30 && substr_count($file, "integer") > 2) {
            $score = 1;
            $comment = "Passed check";
        }
        addToOutput(2, $comment, $score);
        return $score;
    }
```
This snippet looks for a file named `second-required-filename.txt`, checks that the file has at least 30 characters and uses the word "integer" at least three times in the body of the file.  After the check, it adds the results to the output for part 2.  They will be shown on Gradescope under the second "test."

Lastly, zip up the contents of the `text_matching` directory and upload as an autograder to Gradescope.
