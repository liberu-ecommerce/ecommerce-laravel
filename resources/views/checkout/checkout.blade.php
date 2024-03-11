&lt;div>
    @livewire('checkout.address_autofill')

    &lt;form wire:submit.prevent="submitCheckout">
        &lt;div class="form-group">
            &lt;label for="email">Email Address&lt;/label>
            &lt;input type="email" class="form-control" id="email" wire:model.defer="email" required>
        &lt;/div>

        &lt;div class="form-group">
            &lt;div class="custom-control custom-checkbox">
                &lt;input type="checkbox" class="custom-control-input" id="guestCheckout" wire:model="guestCheckout">
                &lt;label class="custom-control-label" for="guestCheckout">Checkout as Guest&lt;/label>
            &lt;/div>
        &lt;/div>

        &lt;div class="form-group" wire:ignore>
            &lt;label for="shipping_address">Shipping Address&lt;/label>
            @livewire('address_autofill', key('address_autofill'))
        &lt;/div>

        &lt;div class="form-group">
            &lt;label for="paymentMethod">Payment Method&lt;/label>
            &lt;select class="form-control" id="paymentMethod" wire:model.defer="paymentMethod" required>
                &lt;option value="">Select a payment method&lt;/option>
                &lt;option value="credit_card">Credit Card&lt;/option>
                &lt;option value="paypal">PayPal&lt;/option>
            &lt;/select>
        &lt;/div>

        &lt;button type="submit" class="btn btn-primary">Complete Checkout&lt;/button>
    &lt;/form>
&lt;/div>

@livewireStyles
@livewireScripts
