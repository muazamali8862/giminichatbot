<?php
// chatbot_process.php

// Load necessary libraries
require "vendor/autoload.php"; // Ensure you have the Gemini API client installed via Composer

use GeminiAPI\Client;
use GeminiAPI\Resources\Parts\TextPart;

// Database connection
include 'config.php'; // Ensure you have a config file for database connection

// Get the incoming JSON data
$data = json_decode(file_get_contents('php://input'));

// Check if the message is set
if (!isset($data->message) || !isset($data->user_id)) {
    echo json_encode(['error' => 'No message provided']);
    exit;
}

$message = trim($data->message); // Trim whitespace
$user_id = (int)$data->user_id; // Get user ID

// Debugging: log the incoming message
file_put_contents('log.txt', "Received message: $message\n", FILE_APPEND);

// Check for "Who are you?" question
if (strtolower($message) === 'who are you?' || strtolower($message) === 'what is your name?' || strtolower($message) === 'what is your purpose?' || strtolower($message) === 'what is your function?' || strtolower($message) === 'who are you' || strtolower($message) === 'what is your name' || strtolower($message) === 'what is your purpose' || strtolower($message) === 'what is your function' )  {
    $botResponse = 'My name is Muazam Ali. I am an AI assistant. Tell me how can I help you.';
} else {
    // Initialize Gemini API client
    $geminiApiKey = 'AIzaSyBzLwUeTVRe7DHAp8pbGvCs64xg6MIQK1w'; // Replace with your actual Gemini API key
    $client = new Client($geminiApiKey);

    try {
        // Send the message to Gemini API
        $response = $client->GeminiPro()->generateContent(new TextPart($message));

        // Extract the response text
        $botResponse = $response->text(); // Assuming this method returns the response text
    } catch (Exception $e) {
        // Handle any errors
        $botResponse = 'Error communicating with Gemini: ' . $e->getMessage();
    }
}

// Save the chat to the database
$stmt = $pdo->prepare("INSERT INTO chat_history (user_id, message, response) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $message, $botResponse]);

// Send the response back to the frontend
echo json_encode(['response' => $botResponse]);
?>