<?php
session_start();

require("0conn.php");
$conversation = array();

if (isset($_POST['go_to_dashboard'])) {
    $stmt_clear = $conn->prepare("DELETE FROM chat_data");
    $stmt_clear->execute();
    $stmt_clear->close();
    header("Location: 9customer.php#bottom");
}

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

if (isset($_POST['question'])) {
    $curl = curl_init();
    $str = $_POST['question'];
    $postdata = array(
        "model" => "text-davinci-003",
        "prompt" => $str,
        "temperature" => 0,
        "max_tokens" => 500
    );
    $postdata = json_encode($postdata);

    $retry = 0;
    do {
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
        $result = json_decode($response, true);

        if ($retry > 0 && isset($result['error']) && $result['error']['code'] == 'ratelimit-exceeded') {
            sleep(20); // Sleep for 20 seconds before retrying
        }

        $retry++;
    } while ($retry <= 10 && (isset($result['error']) && $result['error']['code'] == 'ratelimit-exceeded'));

    curl_close($curl);

    $newdate = date('Y-m-d');
    if (is_array($result) && array_key_exists("error", $result)) {
        $error_message = "Oops! We ran into an issue: " . $result['error']['message'];
        echo $error_message;
        $message = $error_message;
    } else {
        $message = $result['choices'][0]['text'];
    }
    $botreply = array("answer" => $message, "received_date" => $newdate);

    if (isset($_SESSION['username'])) {
        $stmt = $conn->prepare("INSERT INTO chat_data (message_send, send_date, message_received, received_date, username) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $message_send, $send_date, $message_received, $received_date, $_SESSION['username']);

        $message_send = $_POST['question'];
        $send_date = date('Y-m-d');
        $message_received = $botreply['answer'];
        $received_date = $botreply['received_date'];
        $stmt->execute();
        $stmt->close();

        // Update conversation array with the new entry
        $conversation[] = array(
            "user" => $message_send,
            "user_date" => $send_date,
            "bot" => $message_received,
            "bot_date" => $received_date
        );
    } else {
        // Handle the case when $_SESSION['username'] is not set
        echo "User not logged in.";
    }
}

if (!isset($_SESSION['user_avatar'])) {
    $randomAvatarNumber = rand(1, 7);
    $_SESSION['user_avatar'] = "images/avatar{$randomAvatarNumber}.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5; 
            background: #ffffff url('images/background.jpg') no-repeat fixed center;
            background-size: cover;
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 550px;
            max-width: 800px;
            margin: 20px auto;
            overflow: auto;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            background: #ffffff url('images/container.jpg') no-repeat fixed center;
            background-size: cover;
        }

        .messages {
            flex-grow: 1;
            max-height: 400px;
            overflow-y: auto; 
        }

        .user-message {
            display: flex;
            align-items: center;
            margin: 15px 0;
            text-align: justify;
        }

        .bot-message {
            display: flex;
            align-items: center;
            margin: 15px 0;
            text-align: justify;
        }

        .user-message-text,
        .bot-message-text {
            text-align: left;
            border-radius: 10px;
            padding: 12px;
            margin-left: 10px;
            overflow-wrap: break-word;
            max-width: 60%;
        }

        .user-message-text {
            background-color: #7FFF7F; /* Light green for user messages */
            color: #333;
        }

        .bot-message-text {
            background-color: #FFC0CB; /* Light pink for bot messages */
            color: #333;
        }

        .user-message {
            justify-content: flex-end;
        }

        .bot-message {
            justify-content: flex-start;
        }

        .input-forms {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 10px;
        }

        input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            margin-bottom: 10px;
            width: 100%; /* Make the input field 100% width */
        }

        button {
            background-color: #008CBA;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;/
        }

        .user-avatar,
        .bot-avatar {
            width: 40px;
            height: 40px;
            overflow: hidden;
            border-radius: 50%;
            margin-left: 10px;
            margin-right: 10px;
            text-align: center;
            line-height: 40px;
            font-size: 18px;
            color: white;
        }

        .user-avatar img,
        .bot-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        .time{
            size: 10px;
        }
    </style>
    <title>AI Chatbot</title>
</head>
<body>
    <div class="container">
        <div class="messages">
            <?php
            foreach ($conversation as $index => $entry) {
                $userInitial = strtoupper(substr($entry['user'], 0, 1));
            
                if ($entry['user'] != "") {
                    echo "<div class='user-message'>
                              <div class='time'>({$entry['user_date']})</div>
                              <br>
                              <div class='user-message-text'>{$entry['user']}</div>
                              <div class='user-avatar'><img src='{$_SESSION['user_avatar']}' alt='User Avatar'></div>
                          </div>";
                }
            
                if ($entry['bot'] != "") {
                    $botInitial = strtoupper(substr("T", 0, 1));
                    echo "<div class='bot-message'>
                              <div class='bot-avatar'><img src='images/aiavatar.jpg' alt='Bot Avatar'></div>
                              <div class='bot-message-text'>{$entry['bot']}</div>
                          </div>";
                }
            }
            ?>
        </div>

        <div class="input-forms">
            <form method='post' action='#bottom'>
                <label for='question'>Ask a question:</label>
                <input type='text' name='question' required>
                <button type='submit'>Submit</button>
            </form>
            <br>
            
            <form method='post' action='#bottom'>
                <button type='submit' name='go_to_dashboard'>Go To Dashboard</button>
            </form>
        </div>
    </div>

    <div id="bottom"></div>

    <script>
    window.onload = function () {
        var container = document.querySelector(".messages");
        container.scrollTop = container.scrollHeight;
        document.getElementById("bottom").scrollIntoView();
    };
    </script>
</body>
</html>
