
<!DOCTYPE html>
<html>
<head>
    <title>Email Templates</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

</head>
<body>
<div class="wrapper" style="background-color: darkseagreen;text-align: center;width: 100%;padding: 20px;">
    <h2>{{ config('app.name', 'Sytadel App') }}</h2>
    <div class="mail"
         style="background-color: #ffffff;max-width: 600px;margin: auto;text-align: center;padding-top: 40px;color: #74787e;margin-top: 30px;font-family: 'Roboto', sans-serif;padding: 30px 50px 30px 50px;">
        
        <h1 style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#74787e;font-size:16px;font-weight:bold;margin-top:0;text-align:left">Hello {{ucfirst($objMail->name)}} !</h2>
        <p style="color: #807d7d;line-height: 1.5;">Welcome to {{ config('app.name', 'Sytadel App') }}.</p>
        <p style="color: #807d7d;line-height: 1.5;">Regards,</p>
        <p style="color: #807d7d;line-height: 0.5;">{{ config('app.name', 'Sytadel App') }}</p>
    </div>
</div>
</body>
</html>