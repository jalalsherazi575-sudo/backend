@extends('front.layouts.paymentsuccess')
@section('content')
<style>
   .main-content.thank-you {
    background-color: #2B2D6E;
    height: 100vh;
    display: flex;
    align-items: center;
}
.thankyou-content img{margin-bottom:40px;}
.thankyou-content h3{font-size:30px;line-height:36px;color:#fff;margin-bottom:15px;}
.thankyou-content p{color:#B3B3CB;font-size:15px;line-height:20px;margin-bottom:10px;}
.thankyou-content p.transaction-block{font-size:14px;font-weight:500;margin-bottom:20px;}
.back-to-home a{max-width:350px;width:100%;background-color:#EDEDF7;border: 1px solid #CDCDDF;font-size:18px;line-height:20px;padding: 14px;font-weight:500;color:#2B2D6E}
</style>
<div class="main-content thank-you">
   <div class="container text-center payment_page_cnt">
   <div class="row">
      <div class="col-sm-12">
         
            <div class="thankyou-block">
               <div class="thankyou-content">
                  <img src="{{asset('/images/sucess.png')}}">
                  <h3>Thank You </h3>
                  <p>Your purchase has been completed successfully.</p>
                  <p class="transaction-block"><!-- Transaction Id - TTCNI0220008 --></p>
               </div>
               <div class="back-to-home"><a href="{{ url('/') }}" id="goToHomeBtn" title="paymentredirect" alt="Home" class="btn btn-primary">Go To Home</a></div>

            </div>
            </center>   
         </div>
      </div>
   </div>
</div>
@endsection
@section('scripts')
<script>
// Add a click event listener to the "Go To Home" button
document.getElementById('goToHomeBtn').addEventListener('click', function(event) {
   // Check if the device is a mobile device
   var message = {
    success: "1",
    message: "payment successfully.",
    results: ""
   };

   // Convert the JSON object to a string
   var jsonString = JSON.stringify(message);
     window.ReactNativeWebView.postMessage(jsonString);
});
</script>
@endsection