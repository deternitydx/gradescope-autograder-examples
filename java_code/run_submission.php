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

$submissionsAllowed = $config["submissions_allowed"];


// Check on how many submissions the student has had already
$prior = json_decode(file_get_contents("/autograder/submission_metadata.json"), true);
$countPrevious = count($prior["previous_submissions"]);

$submissionCount = $countPrevious + 1;

if ($submissionCount > $submissionsAllowed) {

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

$countOut = "You have used $submissionCount submission(s) out of a maximum $submissionsAllowed allowed.<br>\n\n";



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
        "output" => $countOut . "There was a compile-time error:<br><br><pre>".$compileOut."</pre>",
        "stdout_visibility" => "visible"
    ];
    file_put_contents("/autograder/results/results.json", json_encode($results, JSON_PRETTY_PRINT));
    exit();
} 


// Run the autograder code
$run = runcommand("bash ./run.sh");

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

// Update the results to display the number of submissions
$final = json_decode(file_get_contents("/autograder/results/results.json"), true);
$final["output"] = $countOut;
file_put_contents("/autograder/results/results.json", json_encode($final, JSON_PRETTY_PRINT));

