<?php

function validateArguments($argc, $argv)
{
    if ($argc !== 4) {
        echo "Usage: php script.php <participants> <type> <sender>\n";
        exit(1);
    }
}

function validateParticipants($participants)
{
    if (!is_numeric($participants) || $participants > 8 || $participants < 1) {
        echo "Please select participants between 1-8.\n";
        exit(1);
    }
}

function validateType($type)
{
    $validTypes = ["education", "recreational", "social", "diy", "charity", "cooking", "relaxation", "music", "busywork"];
    if (!in_array($type, $validTypes)) {
        echo "Invalid type. Please select from: 'education', 'recreational', 'social', 'diy', 'charity', 'cooking', 'relaxation', 'music', 'busywork'.\n";
        exit(1);
    }
}

function validateSender($sender)
{
    if (!in_array($sender, ['file', 'console'])) {
        echo "Invalid sender type. Please specify 'file' or 'console'.\n";
        exit(1);
    }
}

function fetchActivity($participants, $type)
{
    $url = "https://www.boredapi.com/api/activity?participants=$participants&type=$type";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        echo "Error: " . curl_error($ch) . "\n";
        exit(1);
    }
    curl_close($ch);
    return $response;
}

function decodeResponse($response)
{
    $data = json_decode($response, true);
    if ($data === null) {
        echo "Error: Unable to decode response JSON.\n";
        exit(1);
    }
    return $data;
}

function validateApiResponse($data)
{
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . "\n";
        exit(1);
    }
    if (!isset($data['activity'])) {
        echo "Error: Unexpected response format. 'activity' key not found.\n";
        exit(1);
    }
}

function saveToFile($message)
{
    if (file_put_contents('recommendation.txt', $message) === false) {
        echo "Error: Unable to save recommendation to file.\n";
        exit(1);
    }
    echo "Recommendation saved to recommendation.txt\n";
}

function displayOnConsole($message)
{
    echo "Recommendation: $message\n";
}

validateArguments($argc, $argv);

$participants = $argv[1];
$type = $argv[2];
$sender = $argv[3];

validateParticipants($participants);
validateType($type);
validateSender($sender);

$response = fetchActivity($participants, $type);
$data = decodeResponse($response);
validateApiResponse($data);

$message = $data['activity'];

if ($sender === 'file') {
    saveToFile($message);
} elseif ($sender === 'console') {
    displayOnConsole($message);
}
