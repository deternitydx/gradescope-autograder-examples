<?php

function runcommand($command) {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
        1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
        2 => array("pipe", "w")
    );
    $pipes = array();
    $process = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($process)) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // It is important that you close any pipes before calling
        // proc_close in order to avoid a deadlock
        $return_value = proc_close($process);
        return [$return_value, $output, $error];
    }

    return null;
}

$config = json_decode(file_get_contents("config.json"), true);

$maxScore = $config["max_score"];
$submissionsAllowed = $config["submissions_allowed"];
$minCoverage = $config["min_coverage_required"];
$maxCoverage = $config["max_coverage_required"];
$requiredFiles = $config["required_files"];

// Check on how many submissions the student has had already
$prior = json_decode(file_get_contents("/autograder/submission_metadata.json"), true);
$countPrevious = count($prior["previous_submissions"]);

$submissionCount = $countPrevious + 1;

if ($submissionsAllowed != 0 && $submissionCount > $submissionsAllowed) {

    // find the latest submission (Gradescope documentation doesn't say if they're ordered!)
    $latest = null;
    $lkey = null;
    foreach ($prior["previous_submissions"] as $k => $sub) {
        if ($latest == null) {
            $latest = $sub["submission_time"];
            $lkey = $k;
        }

        if ($sub["submission_time"] > $latest) {
            $latest = $sub["submission_time"];
            $lkey = $k;
        }
    }

    // Write the latest results with our notice about exceeding submissions allowed
    $latestResults = $prior["previous_submissions"][$k]["results"];
    $formerOutput = $latestResults["output"];
    $parts = explode(" allowed.", $formerOutput, 2);
    $realText = "";
    if (count($parts) == 2) {
        $realText = $parts[1];
    } else {
        $parts = explode(" results follow:", $formerOutput, 2);
        if (count($parts) == 2) 
            $realText = $parts[1];
        $parts = explode(" results follow.", $formerOutput, 2);
        if (count($parts) == 2) 
            $realText = $parts[1];
    }

    $latestResults["output"] = "<b>You have exceeded the number of submissions allowed ($submissionsAllowed).</b>  The score and results are unchanged from the last allowed run.  The prior results follow.<br><br>\n\n\n" . $realText;
    
    file_put_contents("/autograder/results/results.json", json_encode($latestResults, JSON_PRETTY_PRINT));
    exit();

}

$countOut = "";
if ($submissionsAllowed > 0)
    $countOut = "You have used $submissionCount submission(s) out of a maximum $submissionsAllowed allowed.<br>\n\n";

// Check for the required files
$reqFiles = [];
foreach ($requiredFiles as $f) {
    $reqFiles[$f] = false;
}
if ($handle = opendir('src')) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != "..") {
            if (isset($reqFiles[$entry]))
                $reqFiles[$entry] = true;
        }
    }
    closedir($handle);
}
$reqFilesUploaded = true;
foreach ($reqFiles as $k=>$v) {
    if (!$v)
        $reqFilesUploaded = false;
}


if (!$reqFilesUploaded) {
    $missingFiles = "";
    foreach ($reqFiles as $k=>$v) {
        if (!$v)
            $missingFiles .= $k . "\n";
    }

    // generate an error message
    $results = [
        "score" => 0.0,
        "output" => $countOut . "You are missing the following required files:\n\n$missingFiles",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
}


// Compile the student's code with autograder code
$compile = runcommand("bash ./compile.sh");

if ($compile == null) {
    // generate an error message
    $results = [
        "score" => 0.0,
        "output" => $countOut . "There was an error compiling your submission.  Please contact the course staff.",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
}

// Check that the compile succeeded. If not, then show the student their compile errors
$compileOut = trim($compile[1]) . trim($compile[2]);
if (strlen($compileOut) != 0) {
    $results = [
        "score" => 0.0,
        "output" => $countOut . "<pre>".$compileOut."</pre>",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
} 


// Run the autograder code
$run = runcommand("ant");

if ($run == null) {
    // generate an error message
    $results = [
        "score" => 0.0,
        "output" => $countOut . "There was an error running your submission.  Please contact the course staff.",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
}

//return [$return_value, $output, $error];
if ($run[0] != 0) {
    // An error occurred
    $results = [
        "score" => 0.0,
        "output" => $countOut . "There was an error running your submission.  It is likely that there was a compile-time error.  We have included the full output below:<br><br>".
                    "<pre>".trim($run[1]).trim($run[2])."</pre>",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
}

$row = 1;
$coverage = [];
$totalCovered = 0;
$totalInstructions = 0;
if (($handle = fopen("target/site/jacoco/report.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row++ != 1) {
            $coverage[$data[2]] = [
               "missed" => $data[3],
               "covered" => $data[4],
               "total" => $data[3] + $data[4],
               "percent" => round(($data[4] / ($data[3] + $data[4])) * 100, 2)
            ];
            $totalCovered += $data[4];
            $totalInstructions += $data[3] + $data[4]; 
        }
    }
    fclose($handle);
}

$totalCoverage = round(($totalCovered / $totalInstructions) * 100, 2);

$comments = "Here are your code coverage results:\n\n";
foreach ($coverage as $t => $v) {
    $comments .= "$t: {$v["percent"]}%\n";
}
$comments .= "-------------------------------------\n";
$comments .= "Total Coverage: $totalCoverage%";


$score = 0;
if ($totalCoverage < $minCoverage)
    $score = 0;
else if ($totalCoverage > $maxCoverage)
    $score = $maxScore;
else
    $score = round((($totalCoverage - $minCoverage) / ($maxCoverage-$minCoverage)) * ($maxScore), 2);

$results = [
    "score" => $score,
    "output" => $countOut . $comments,
    "stdout_visibility" => "visible"
];
file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));

