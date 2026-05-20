<div class="shopping-cart">
    <h2 class="mb-4">Shopping Cart</h2>
    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(count($items) > 0)
        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
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
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if(isset($item['image']))
                                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="img-thumbnail mr-3" style="width: 50px; height: 50px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $item['name'] }}</h6>
                                                @if($item['is_downloadable'])
                                                    <span class="badge bg-info text-white">Digital</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>${{ number_format($item['price'], 2) }}</td>
                                    <td>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm" wire:click="updateQuantity('{{ $id }}', {{ max(1, $item['quantity'] - 1) }})">-</button>
                                            <input type="number" class="form-control form-control-sm text-center" wire:model.lazy="items.{{ $id }}.quantity" wire:change="updateQuantity('{{ $id }}', $event.target.value)" min="1">
                                            <button class="btn btn-outline-secondary btn-sm" wire:click="updateQuantity('{{ $id }}', {{ $item['quantity'] + 1 }})">+</button>
                                        </div>
                                    </td>
                                    <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" wire:click="removeItem('{{ $id }}')">
                                            <i class="fa fa-trash"></i> Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <button class="btn btn-outline-secondary" wire:click="clearCart">
                    <i class="fa fa-trash"></i> Clear Cart
                </button>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>${{ number_format($this->calculateTotal(), 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping:</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total:</span>
                            <strong>${{ number_format($this->calculateTotal(), 2) }}</strong>
                        </div>
                        <div class="d-grid">
                            <a href="{{ route('checkout.initiate') }}" class="btn btn-primary">
                                Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <h4>Your cart is empty</h4>
                <p class="mb-4">Looks like you haven't added any products to your cart yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">
                    Continue Shopping
                </a>
            </div>
        </div>
    @endif
</div>