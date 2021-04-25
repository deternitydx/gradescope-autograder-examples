<?php

/****************
 * Grading Script 
 *
 * Grades 3 at a time and returns the final grade
 * in a format used by gradescope.  The directory
 * for student submissions is given on the command
 * line as the first argument
 ****************/


// TODO: Configuration parameters
// Maximum number of points to award in this autograder
$maxPoints = 10;
// Number of files to examine.  Each will be listed as a separate "test" (or part) in the Gradescope display.
$numParts = 3;



/**
 * Build the output for later use
 */
$output = [
    "tests" => [
    ]
];

function addToOutput($number, $comments, $score) {
    global $output;

    $output["tests"][$number]["score"] = $score;
    $output["tests"][$number]["output"] = $comments;
}

for ($i = 1; $i <= $numParts; $i++) {
    $output["tests"][$i] = [
        "score" => 0,
        "max_score" => 1,
        "name" => "Lab Part " . chr(64+$i),
        "number" => "$i",
        "output" => "Not Found (Please check filenames)"
    ];
}

/**
 * Right now, this script will handle all the grading; it's given
 * the file contents and the file name of every file that the student
 * uploaded.  That allows us to be flexible if we can look through the
 * contents of the file for which lab part the submission is for rather
 * than just relying on the name of the file.
 * NOTE: This does not
 */
function gradeUploadedFile($file, $name) {
    $score = 0;
    $comment = "Did not pass check";
    if (strtolower($name) == "first-required-filename.txt") {
        // TODO: This example just checks that the file contents are long enough
        if (strlen($file) > 200) {
            $score = 1;
            $comment = "Passed check";
        }
        addToOutput(1, $comment, $score);
        return $score;
    }
    if (strtolower($name) == "second-required-filename.txt") {
        // TODO: This example checks that the string "integer" is found in the submitted file at least 3 times
        if (strlen($file) > 30 && substr_count($file, "integer") > 2) {
            $score = 1;
            $comment = "Passed check";
        }
        addToOutput(2, $comment, $score);
        return $score;
    }
    if (strtolower($name) == "third-required-filename.txt") {
        // TODO: This example checks that the string "int" is found in the submitted file at least once 
        if (strlen($file) > 20 && substr_count(strtolower($file), "int") > 1) {
            $score = 1;
            $comment = "Passed check";
        }
        addToOutput(3, $comment, $score);
        return $score;
    }
    // Any of the examples above could be modified to use regular expression tests

    // file didn't match, so we don't grade
    return 0;
}

// Check the uploaded directory for files
$directory = $argv[1];
$score = 0;
$comments = "";
if ($handle = opendir($directory)) {
    while (false !== ($fn = readdir($handle))) {
        if ($fn != "." && $fn != "..") {
            $comments .= "    Submitted file: $fn\n";
            $submission = file_get_contents("$directory$fn");
            if (($score += gradeUploadedFile($submission, $fn)) > 0) {
                $comments .= "      non-empty\n";
            } else {
                $comments .= "      could not read file\n";
            }
        }
    }
}

// count up the score
$perPart = $maxPoints / $numParts;
$finalScore = ceil($score * $perPart);
if ($finalScore > $maxPoints)
    $finalScore = $maxPoints;

$realOutput = [];
$realTests = [];
foreach ($output["tests"] as $test)
    array_push($realTests, $test);
$realOutput["score"] = $finalScore;
$realOutput["output"] = $comments;
$realOutput["tests"] = $realTests;

// write out the JSON file
echo json_encode($realOutput, JSON_PRETTY_PRINT);

