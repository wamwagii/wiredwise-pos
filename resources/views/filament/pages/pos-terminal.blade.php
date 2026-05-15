<x-filament-panels::page>
    <style>
        .pos-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .pos-products {
            flex: 2;
            min-width: 300px;
        }
        
        .pos-cart {
            flex: 1;
            min-width: 300px;
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .product-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1rem;
            cursor: pointer;
            transition: box-shadow 0.2s;
            border: 1px solid #e5e7eb;
        }
        
        .product-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        .dark .product-card {
            background: #1f2937;
            border-color: #374151;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark .cart-item {
            border-bottom-color: #374151;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            width: 1.5rem;
            height: 1.5rem;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        
        .quantity-btn.minus {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .quantity-btn.plus {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .dark .quantity-btn.minus {
            background: #7f1d1d;
            color: #fca5a5;
        }
        
        .dark .quantity-btn.plus {
            background: #14532d;
            color: #86efac;
        }
        
        .payment-btn {
            background: #dcfce7;
            color: #16a34a;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-align: center;
            cursor: pointer;
        }
        
        .payment-btn:hover {
            background: #bbf7d0;
        }
    </style>

    <div class="pos-container">
        {{-- Left Column - Products --}}
        <div class="pos-products">
            {{-- Barcode Scanner --}}
            <div style="background: white; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);" class="dark:bg-gray-800">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Barcode Scanner</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" 
                           wire:model="barcode_input" 
                           wire:keydown.enter="processBarcode"
                           placeholder="Scan or type barcode/SKU here..." 
                           style="flex: 1; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem;">
                    <button wire:click="processBarcode" 
                            style="background: #2563eb; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem;">
                        Add
                    </button>
                </div>
                <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Supports barcode scanners (auto-submit with Enter)</p>
            </div>
            
            {{-- Search --}}
            <div style="background: white; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);" class="dark:bg-gray-800">
                <input type="text" 
                       wire:model.live.debounce.300ms="search_term" 
                       placeholder="Search by name, SKU, or barcode..." 
                       style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem;">
            </div>
            
            {{-- Products Grid --}}
            <div class="product-grid">
                @foreach($products as $product)
                    <div wire:click="addToCart({{ $product->id }})" class="product-card">
                        <div style="font-size: 0.75rem; color: #6b7280;">{{ $product->sku }}</div>
                        <div style="font-weight: 600; margin-top: 0.25rem;">{{ $product->name }}</div>
                        <div style="font-size: 1.25rem; font-weight: bold; color: #2563eb; margin-top: 0.5rem;">
                            ${{ number_format($product->selling_price, 2) }}
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">Stock: {{ $product->stock_quantity }}</div>
                    </div>
                @endforeach
            </div>
        </div>
        
        {{-- Right Column - Cart --}}
        <div class="pos-cart">
            {{-- Customer Selection --}}
            <div style="background: white; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);" class="dark:bg-gray-800">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem;">Customer (Optional)</label>
                <select wire:model="customer_id" 
                        style="width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem;">
                    <option value="">Walk-in Customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                    @endforeach
                </select>
            </div>
            
            {{-- Cart Items --}}
            <div style="background: white; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);" class="dark:bg-gray-800">
                <div style="padding: 1rem; border-bottom: 1px solid #e5e7eb; font-weight: 600;">Current Order</div>
                
                <div style="max-height: 400px; overflow-y: auto;">
                    @if(empty($cart))
                        <div style="padding: 2rem; text-align: center; color: #6b7280;">No items in cart</div>
                    @else
                        @foreach($cart as $item)
                            <div class="cart-item">
                                <div>
                                    <div style="font-weight: 500;">{{ $item['name'] }}</div>
                                    <div style="font-size: 0.75rem; color: #6b7280;">${{ number_format($item['unit_price'], 2) }} each</div>
                                </div>
                                <div class="quantity-controls">
                                    <button wire:click="updateCartQuantity({{ $item['product_id'] }}, {{ $item['quantity'] - 1 }})"
                                            class="quantity-btn minus"
                                            style="width: 28px; height: 28px; border-radius: 4px; background: #fee2e2; color: #dc2626; cursor: pointer;">
                                        -
                                    </button>
                                    <span style="width: 30px; text-align: center;">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateCartQuantity({{ $item['product_id'] }}, {{ $item['quantity'] + 1 }})"
                                            class="quantity-btn plus"
                                            style="width: 28px; height: 28px; border-radius: 4px; background: #dcfce7; color: #16a34a; cursor: pointer;">
                                        +
                                    </button>
                                    <button wire:click="removeFromCart({{ $item['product_id'] }})"
                                            style="color: #dc2626; margin-left: 0.5rem; cursor: pointer;">
                                        ✕
                                    </button>
                                </div>
                            </div>
                            <div style="text-align: right; font-size: 0.875rem; font-weight: 600; padding: 0 0.75rem 0.75rem;">
                                Total: ${{ number_format($item['total_price'], 2) }}
                            </div>
                        @endforeach
                    @endif
                </div>
                
                {{-- Totals --}}
                <div style="padding: 1rem; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal:</span>
                        <span>${{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span>Tax Rate (%):</span>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" wire:model.live="tax_rate" step="0.01" 
                                   style="width: 80px; text-align: right; border-radius: 0.25rem; border: 1px solid #d1d5db; padding: 0.25rem;">
                            <span>${{ number_format($tax_amount, 2) }}</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span>Discount:</span>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" wire:model.live="discount_amount" step="0.01" 
                                   style="width: 80px; text-align: right; border-radius: 0.25rem; border: 1px solid #d1d5db; padding: 0.25rem;">
                            <span>${{ number_format($discount_amount, 2) }}</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.125rem; padding-top: 0.5rem; border-top: 1px solid #e5e7eb;">
                        <span>Total:</span>
                        <span style="color: #16a34a;">${{ number_format($total_amount, 2) }}</span>
                    </div>
                </div>
                
                {{-- Actions --}}
                <div style="padding: 1rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <button wire:click="clearCart" style="background: #9ca3af; color: white; padding: 0.5rem; border-radius: 0.5rem;">Clear Cart</button>
                        <button wire:click="processPayment" style="background: #16a34a; color: white; padding: 0.5rem; border-radius: 0.5rem;">Process Payment</button>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem;">
                        <button wire:click="$set('payment_method', 'cash')" 
                                style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d1d5db; {{ $payment_method === 'cash' ? 'background: #2563eb; color: white;' : 'background: white;' }}">
                            Cash
                        </button>
                        <button wire:click="$set('payment_method', 'card')" 
                                style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d1d5db; {{ $payment_method === 'card' ? 'background: #2563eb; color: white;' : 'background: white;' }}">
                            Card
                        </button>
                        <button wire:click="$set('payment_method', 'mobile_money')" 
                                style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid #d1d5db; {{ $payment_method === 'mobile_money' ? 'background: #2563eb; color: white;' : 'background: white;' }}">
                            Mobile Money
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Keep focus on barcode input
            setInterval(() => {
                const barcodeInput = document.querySelector('[wire\\:model="barcode_input"]');
                if (barcodeInput && document.activeElement !== barcodeInput) {
                    barcodeInput.focus();
                }
            }, 2000);
        });
    </script>
    @endpush
</x-filament-panels::page>