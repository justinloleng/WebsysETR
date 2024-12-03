<?php
require("conn.php");

// Initialize the conversation array
$conversation = array();

// Check if the form is submitted
if (isset($_POST['question'])) {
    // OpenAI API request and database insertion

    $curl = curl_init();
    $str = $_POST['question'];
    $postdata = array(
        "model" => "text-davinci-003",
        "prompt" => $str,
        "temperature" => 0,
        "max_tokens" => 500
    );
    $postdata = json_encode($postdata);
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.openai.com/v1/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postdata,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer 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',
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response, true);
    $newdate = date('Y-m-d');
    if (array_key_exists("error", $result)) {
        echo "Key Exist";
        $message = "Oops! We ran into an issue in : " . $result['error'];
    } else {
        $message = $result['choices'][0]['text'];
    }
    $botreply = array("answer" => $message, "received_date" => $newdate);

    // Database insertion
    $stmt = $conn->prepare("INSERT INTO chat_data (message_send, send_date, message_received, received_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $message_send, $send_date, $message_received, $received_date);

    $message_send = $_POST['question'];
    $send_date = date('Y-m-d');
    $message_received = $botreply['answer'];
    $received_date = $botreply['received_date'];
    $stmt->execute();
    $stmt->close();

    // Fetch entire conversation from the database
    $fetch_stmt = $conn->prepare("SELECT message_send, send_date, message_received, received_date FROM chat_data ORDER BY id ASC");
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();

    while ($row = $fetch_result->fetch_assoc()) {
        $conversation[] = array(
            "user" => $row['message_send'],
            "user_date" => $row['send_date'],
            "bot" => $row['message_received'],
            "bot_date" => $row['received_date']
        );
    }

    $fetch_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Form</title>
</head>
<body>
    <form method="post" action="">
        <label for="question">Ask a question:</label>
        <input type="text" name="question" required>
        <button type="submit">Submit</button>
    </form>

    <?php
    // Display the entire conversation
    foreach ($conversation as $entry) {
        echo "<p>User: {$entry['user']} ({$entry['user_date']})</p>";
        echo "<p>Bot: {$entry['bot']} ({$entry['bot_date']})</p>";
    }
    ?>
</body>
</html>
