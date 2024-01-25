<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href= "public/css/register.css">
    <title>Login</title>
</head>
<body>
    <div id="leftbar">
            <img src="public/assets/mainmenu.jpg" alt="Analysis graph">
    </div>
    <div id="rightside">
        <div class="loginsquare">
            <h1 id="topmessage">Create new account</h1>
            <form class="register" action="register" method="POST">
                <input name="email" type="email" placeholder="template@email.com" id="usernameinput"/>
                <input name="password" type="password" placeholder="Password" id ="passwordinput"/>
                <input name="confirmpassword" type="password" placeholder="Confirm Password" id ="confirminput"/>
                <button type="submit">REGISTER</button>
            </form>
            <div id="accsignup">
                <h1 id="account">Have an account?</h1>
                <h1><a href="login" id="signup">Sign in!</a></h1>
            </div>
        </div>    
    </div>
</body>
</html>