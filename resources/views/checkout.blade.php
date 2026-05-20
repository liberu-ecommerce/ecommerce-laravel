<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        /* Minimal styling for layout */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f9fafb;
            margin: 0;
        }
        form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: box-shadow 150ms ease;
        }
        .StripeElement--focus {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
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
            font-size: 0.9rem;
        }
        button {
            background: #32325d;
            color: #ffffff;
            border: none;
            padding: 10px;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        button:hover {
            background: #4a4e69;
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
        document.addEventListener('DOMContentLoaded', function() {
            var stripe = Stripe('{{ config('services.stripe.key') }}');
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
                        // For quick tests we post to /payment (StripePaymentController)
                        fetch('/payment', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: JSON.stringify({ payment_method: result.paymentMethod.id, amount: 1.00 })
                        }).then(function(response) {
                            return response.json();
                        }).then(function(json) {
                            if (json.success) {
                                alert('Payment succeeded: ' + (json.payment_id || json.payment_id));
                            } else {
                                alert('Payment failed: ' + (json.error || 'Unknown'));
                            }
                        }).catch(function(error) {
                            console.error('Error:', error);
                            alert('Payment error, check console');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
