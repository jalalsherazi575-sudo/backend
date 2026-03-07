<!DOCTYPE html>
<html>
<head>
    <title>Email Templates</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

</head>
<body>
<div class="wrapper" style="background-color: #f8f8f8;text-align: center;width: 100%;padding: 20px;">
    <img src="{{ url('/') }}/assets/admin/img/logo.svg" style="width: 200px;">
    <div class="mail"
         style="background-color: #ffffff;max-width: 600px;margin: auto;text-align: center;padding-top: 40px;color: #74787e;margin-top: 30px;font-family: 'Roboto', sans-serif;padding: 30px 50px 30px 50px;">
        <p style="color: #807d7d;line-height: 1.5;font-size:16px;">Thank you for contacting us</p>
         
        <div class="action" style="padding-top: 30px;">
            <span>
                <strong>Contact Name:</strong>
            </span>
                <span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">{{$demo->contactname}}</span>
        </div>

        <div class="action" style="padding-top: 30px;">
            <span>
                <strong>Contact Email:</strong>
            </span>
                <span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">{{$demo->contactemail}}</span>
        </div>

        <div class="action" style="padding-top: 30px;">
            <span>
                <strong>Contact Phone Number:</strong>
            </span>
                <span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">{{$demo->contactphone}}</span>
        </div>

        <div class="action" style="padding-top: 30px;">
            <span>
                <strong>Contact Message:</strong>
            </span>
                <span style="color: #807d7d;line-height: 1.5;font-size:16px;margin-left:20px;">{{$demo->message}}</span>
        </div>
      

        
       
    </div>

</div>
</body>
</html>