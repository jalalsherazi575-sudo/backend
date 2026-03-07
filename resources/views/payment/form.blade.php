<form method="post" action="{{ route('payment.process') }}">
    @csrf
    <label for="amount">Amount:</label>
    <input type="text" id="amount" name="amount"><br><br>
    <label for="description">Description:</label>
    <input type="text" id="description" name="description"><br><br>
    <button type="submit">Pay Now</button>
</form>
