@extends('front.layouts.paymentsuccess')
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-sm-12">
         <div class="container text-sm-center payment_page_cnt">
            <div>
               <div>
                  <h3>Error occurred during payment</h3>
               </div>
               <div><a href="{{ url('/') }}" title="paymentredirect" alt="paymentredirect" class="btn btn-primary">Go To Home</a></div>
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
    success: "0",
    message: "Error occurred during payment.",
    results: ""
   };

   // Convert the JSON object to a string
   var jsonString = JSON.stringify(message);
     window.ReactNativeWebView.postMessage(jsonString);
});
</script>
@endsection