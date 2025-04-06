<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="user_acct.css">
    <title>Document</title>
</head>

<body>
  
    <div id="main-content">

        <form action="signup.php" method="post">
            <div class="container">
                <h1>Welcome User</h1>
                <p>Please fill in this form to create an account.</p>
                <hr>

                <label for="name">Name:</label>
                <input type="text" name="name" id="name" placeholder="Full Name" required>

                <br><br>

                <label for="email">Email:</label>
                <input type="text" name="email" id="email" placeholder="Enter Email" required>

                <br><br>

                <label for="password">Password:</label>
                <input type="password" name="password" id="password" placeholder="Password" required>

                <br><br>

                <label for="conf-password">Confirm Password:</label>
                <input type="password" name="conf-password" id="conf-password" placeholder="Confirm Password" required>

                <div id="submit-btn-container">
                    <button id="submit-btn" type="submit">Submit</button>
                </div>

            </div>
        </form>

    </div>

</body>
</html>
