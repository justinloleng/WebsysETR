<?php
session_start();

require("0conn.php");
$conversation = array();

if (!isset($_SESSION['username'])) {
    header("Location: 3login.php");
    exit();
}

if (isset($_POST['go_to_dashboard'])) {
    $stmt_clear = $conn->prepare("DELETE FROM chat_data");
    $stmt_clear->execute();
    $stmt_clear->close();
    header("Location: 12user_profile.php#bottom");
    exit; 
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
            sleep(20); 
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

        // Redirect to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']."#bottom");
        exit; // Add exit to stop further execution
    } else {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png">
    <style>
        body {
            font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f3f3;
            display: flex;
            flex-wrap: wrap;
        }

        .topnav {
            background-color: #16b978;
            overflow: hidden;
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            padding-top: 90px;
            transition: top 0.3s;
        }

        .topnav a {
            float: center;
            color: #f2f2f2;
            text-align: center;
            padding: 15px 25px;
            text-decoration: none;
            font-size: 17px;
            display: flex;
            align-items: center;
        }

        .topnav a:hover {
            background-color: #ddd;
            color: black;
        }

        .topnav a.active {
            background-color: #04AA6D;
            color: white;
        }

        .topnav a i {
            margin-right: 30px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        .logo-container {
            position: fixed;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            padding: 20px;
            width: auto;
            margin-right: 10px;
        }

        .logo h1 {
            font-family: cursive;
            font-size: 24px;
            margin: 0;
            color: #16b978;
        }
        .container {
            background-color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 120px;
            max-height: calc(100vh - 120px); /* Adjusted to make the container height responsive */
            overflow-y: auto; /* Added to make the container scrollable if content is long */
        }
        .messages {
            flex-grow: 1;
            max-height: 400px;
            width: 100%;
        }

        .user-message,
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
            background-color: #7FFF7F;
            color: #333;
        }

        .bot-message-text {
            background-color: #FFC0CB;
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
            align-items: center;
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
            margin-top: 130px;
            margin-bottom: 15px;
            height: 48px;
            width: 600px; /* Set a maximum width */
        }

        button {
            background-color: #008CBA;
            color: #f2f2f2;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            max-width: 600px; /* Set a maximum width */
        }

        .user-avatar,
        .bot-avatar {
            width: 40px;
            height: 40px;
            overflow: hidden;
            border-radius: 50%;
            margin-left: 50px;
            margin-right: 50px;
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
        .time {
            font-size: 15px;
            text-align: center;
            color: gray;
        }
        .topnav a.active {
            background-color: lightgray;
            color: black;
        }
    </style>
    <title>AI Chatbot</title>
</head>

<body>
<div class="logo-container">
    <div class="logo">
        <img src="logo.png" alt="Tastebud Logo">
        <h1>Tastebud</h1>
    </div>
</div>

<div class="topnav">
    <a href="12user_profile.php"<?php echo (basename($_SERVER['PHP_SELF']) == '15userposts.php') ? 'class="active"' : ''; ?>><i class="fa fa-fw fa-user"></i>Profile</a>
    <a href="view_categories.php"><i class="fa-solid fa-list"></i>Categories</a>
    <a href="9customer.php"><i class="fa-solid fa-utensils"></i>User Recipes</a>
    <a href="14chat.php"><i class="fa-solid fa-comment"></i>Chat</a>
    <a href="4logout.php"><i class="fas fa-fw fa-sign-out"></i> Logout</a>
</div>
    <div class="container">
        <div class="messages">
            <?php
            $previousUserDate = null;
            foreach ($conversation as $index => $entry) {
                $userInitial = strtoupper(substr($entry['user'], 0, 1));
            
                if ($entry['user'] != "") {
                    if ($entry['user_date'] != $previousUserDate) {
                        echo "<div class='time'>{$entry['user_date']}</div>";
                    }

                    $previousUserDate = $entry['user_date'];
                    echo "<div class='user-message'>
                              <br>
                              <div class='user-message-text'>{$entry['user']}</div>
                              <div class='user-avatar'><img src='{$_SESSION['user_avatar']}' alt='User Avatar'></div>
                          </div>";
                }
            
                if ($entry['bot'] != "") {
                    $botInitial = strtoupper(substr("T", 0, 1));
                    echo "<div class='bot-message'>
                              <div class='bot-avatar'><img src='images\aiavatar.png' alt='Bot Avatar'></div>
                              <div class='bot-message-text'>{$entry['bot']}</div>
                          </div>";
                }
            }
            ?>
        </div>

        <div class="input-forms">
            <form method='post' action='#bottom'>
                <input type='text' placeholder="Ask a question" name='question' required>
                <button type='submit'>Submit</button>
            </form>
            <br>
            
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
