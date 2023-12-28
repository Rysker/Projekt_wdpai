<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/login.css">
    <title>Login</title>
</head>
<body>
    <div id="leftbar">
            <img src="public/assets/mainmenu.jpg" alt="Analysis graph">
    </div>
    <div id="rightside">
        <div class="loginsquare">
            <h1 id="topmessage">Sign into your account</h1>
            <form class="login" action="login" method="POST">
                <input name ="email" type="email" placeholder="template@email.com" id="usernameinput"/>
                <input name="password" type="password" placeholder="Password" id ="passwordinput"/>
                <button type="submit">LOGIN</button>
            </form>
            <h1><a href="https://www.google.pl/" id="passwordforget">Forget your password?</a></h1>
            <div id="noaccsignup">
                <h1 id="noaccount">Don't have an account?</h1>
                <h1><a href="register" id="signup">Sign up!</a></h1>
            </div>
        </div>    
    </div>
</body>
</html>