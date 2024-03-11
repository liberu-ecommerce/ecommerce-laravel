<div>
    <h2>Shopping Cart</h2>
    @if(count($items) > 0)
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $id => $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td>${{ number_format($item['price'], 2) }}</td>
                        <td>
                            <input type="number" wire:model.lazy="items.{{ $id }}.quantity" wire:change="updateQuantity('{{ $id }}', $event.target.value)">
                        </td>
                        <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        <td>
                            <button wire:click="removeItem('{{ $id }}')">Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div>
            <strong>Total: ${{ number_format(array_reduce($items, function ($carry, $item) {
                return $carry + ($item['price'] * $item['quantity']);
            }, 0), 2) }}</strong>
        </div>
        <div>
            <button wire:click="clearCart">Clear Cart</button>
        </div>
    @else
        <p>Your cart is empty.</p>
    @endif
</div>
