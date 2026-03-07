<!DOCTYPE html>
<html>
<head>
    <title>Email Templates</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

</head>
<body>
<div class="wrapper" style="background-color: #f8f8f8;text-align: center;width: 100%;padding: 20px;">
    <div style="padding:20px;background-color: #672f90;max-width: 700px;margin: auto;"><img src="{{ url('/') }}/assets/admin/img/long-logo.png"></div>
    <div class="mail"
         style="background-color: #ffffff;max-width: 640px;margin: auto;text-align: center;padding-top: 40px;color: #74787e;margin-top: 30px;font-family: 'Roboto', sans-serif;padding: 30px 50px 30px 50px;">
        
        <h1 style="font-family:Avenir,Helvetica,sans-serif;box-sizing:border-box;color:#74787e;font-size:16px;font-weight:bold;margin-top:0;text-align:left">Hello {{$demo->name}},</h2>
        <p style="color: #807d7d;line-height: 1.5;">You are receiving this email because we received a password reset request for your account.</p>

        <div class="action" style="padding-top: 30px;padding-bottom: 30px;">
            <a href="{{$demo->url}}" class="action"
               style="padding-top: 30px;padding-bottom: 30px;background-color: #f5d60f;text-decoration: none;display: inline-block;color:#222222;padding: 10px 40px;font-weight: bold;font-size: 20px;">Reset
                Password</a>
        </div>


        <p style="color: #807d7d;line-height: 1.5;font-size:16px;">If you didn't request this password reset,please ingnore this
            email</p>
       
    </div>

</div>
</body>
</html>