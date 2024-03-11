@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Manage Subscription</h2>
    <div class="subscription-details">
        <h3>Current Subscription</h3>
        <p><strong>Plan:</strong> {{ $subscription->stripe_plan }}</p>
        <p><strong>Status:</strong> {{ $subscription->stripe_status }}</p>
        <p><strong>Renewal Date:</strong> {{ $subscription->ends_at ? $subscription->ends_at->toFormattedDateString() : 'N/A' }}</p>
    </div>

    <div class="change-plan">
        <h3>Change Plan</h3>
        <form action="{{ route('subscription.change-plan') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="plan">Select Plan:</label>
                <select name="plan" id="plan" class="form-control">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Change Plan</button>
        </form>
    </div>

    <div class="cancel-subscription">
        <h3>Cancel Subscription</h3>
        <form action="{{ route('subscription.cancel') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger">Cancel Subscription</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            fetch(this.action, {
                method: this.method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify(Object.fromEntries(new FormData(this)))
            }).then(response => response.json())
              .then(data => {
                  alert(data.message);
                  if (data.success) {
                      window.location.reload();
                  }
              });
        });
    });
});
</script>
@endsection
