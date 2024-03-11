<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        /* Minimal styling for layout */
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }
        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }
        .StripeElement--invalid {
            border-color: #fa755a;
        }
        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
        }
        #card-errors {
            color: #fa755a;
            margin-top: 10px;
        }
        button {
            background: #32325d;
            color: #ffffff;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <form id="payment-form">
        <div id="card-element">
            <!-- Stripe Elements will be inserted here -->
        </div>
        <div id="card-errors" role="alert"></div>
        <button type="submit">Submit Payment</button>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>

    <script>
        var stripe = Stripe('{{ env("STRIPE_KEY") }}');
        var elements = stripe.elements();
        var style = {
            base: {
                color: "#32325d",
            }
        };

        var card = elements.create("card", { style: style });
        card.mount("#card-element");

        card.on('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            stripe.createPaymentMethod({
                type: 'card',
                card: card,
            }).then(function(result) {
                if (result.error) {
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    fetch('/api/payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({ payment_method_id: result.paymentMethod.id })
                    }).then(function(response) {
                        return response.json();
                    }).then(function(paymentIntent) {
                        console.log(paymentIntent);
                        // Handle success or display error message
                    });
                }
            });
        });
    </script>
</body>
</html>
