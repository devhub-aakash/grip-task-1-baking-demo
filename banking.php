<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bank</title>
    <style>
        button {
            padding-block: 4px;
            padding-inline: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        #window {
            display: flex;
            justify-content: start;
            align-items: center;
            padding-left: 100px;
            font-size: 20px;
        }
    </style>
</head>

<body style="margin: 0;">
    <!-- connection funtion -->
    <?php
    function getconn()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "bank_db";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            return null;
        }
        return $conn;
    }

    ?>
    <header style="width: 100%;height:80px;text-align: center;font-size: 60px;background-color: #2F80ED;color:white">My Bank</header>
    <div id="window" style="width:70%;max-width:900px; min-width:360px;height:600px;border:1px solid #000;margin:auto;margin-top: 60px;">
    </div>

    <div style="text-align: center;">
        <button onclick=viewall() style="padding-block: 8px;padding-inline: 16px;font-size: 18px;background-color: #2F80ED;color: white;border: none;border-radius: 20px;margin-top: 60px;cursor: pointer;">View All Customers</button>
    </div>

    <script>
        function viewall() {
            document.getElementById('window').innerHTML =
                `
                <div style='display:flex-column;'>
            <?php fetch_bank_data() ?>
            </div>
            `
        }

        function view(name, email, balance) {
            document.getElementById('window').innerHTML = `
        <div>
            <form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" >
                <h2>User's Details</h2>
                <div style='font-size:24px'>Name: ${name}<br>Email: ${email}<br>Balance: ${balance} </div><br/>
                <input type="hidden" name='sender' value='${email}'/>
                <h3>Enter amount to transfer: <input type='text' id='amount' name='amount' /></h3>
                <div>
                <h3>Transfer Money To:
                <select name='receiver'>` +
                "<?php user_list() ?>" +
                `</select>
                </h3>
                </div>
                <button type='submit' style='margin-top:20px'>Send Money</button>
            </form>
        </div>`
        }
    </script>

    <?php
    function fetch_bank_data()
    {
        $conn = getconn();
        $sql = "SELECT user_name, user_email, balance FROM bank_data";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                $name = explode(' ', $row["user_name"])[0];
                echo "Name: " . $name . " - <button onclick=view('" . $name . "','" . $row["user_email"] . "'," . $row['balance'] . ")>View Customer</button>" . "<br><br>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }
    ?>

    <?php
    function user_list()
    {
        $conn = getconn();
        $sql = "SELECT user_name, user_email, balance FROM bank_data";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // output data of each row
            while ($row = $result->fetch_assoc()) {
                $name = strval($row["user_name"]);
                // echo "Name: " . $name . " - <button onclick=transfer()>transfer</button>" . "<br><br>";
                echo "<option value=" . $row['user_email'] . ">" . $name . "</option>";
            }
        } else {
            echo "0 results";
        }
        $conn->close();
    }
    ?>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $sender = strval($_POST['sender']);
        $receiver = strval($_POST['receiver']);
        $amount = $_POST['amount'];
        $conn = getconn();
        $sql1 = "SELECT balance FROM bank_data WHERE user_email='" . $sender . "'";
        $sql2 = "SELECT balance FROM bank_data WHERE user_email='" . $receiver . "'";
        $sender_balance = $conn->query($sql1)->fetch_array()['balance'];
        $receiver_balance = $conn->query($sql2)->fetch_array()['balance'];
        $temp =  "Before:<br><br>";
        $temp .= "Sender Balance: " . $sender_balance;
        $temp .= "<br><br>";
        $temp .= "Receiver Balance: " . $receiver_balance;
        $sql3 = "UPDATE bank_data SET balance=" . $sender_balance - $amount . " where user_email='" . $sender . "'";
        $sql4 = "UPDATE bank_data SET balance=" . $receiver_balance + $amount . " where user_email='" . $receiver . "'";
        if ($conn->query($sql3) === TRUE && $conn->query($sql4) === TRUE) {
            $sql1 = "SELECT balance FROM bank_data WHERE user_email='" . $sender . "'";
            $sql2 = "SELECT balance FROM bank_data WHERE user_email='" . $receiver . "'";
            $sender_balance = $conn->query($sql1)->fetch_array()['balance'];
            $receiver_balance = $conn->query($sql2)->fetch_array()['balance'];
            $temp .= "<br><br>";
            $temp .= "After:<br><br>";
            $temp .= "Sender Balance: " . $sender_balance;
            $temp .= "<br><br>";
            $temp .= "Receiver Balance: " . $receiver_balance;
            $temp .= "<br><br>";
            $temp .= "Money Transfered Successfully";
            echo "<script>document.getElementById('window').innerHTML='" . $temp . "'</script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
    ?>
</body>

</html>