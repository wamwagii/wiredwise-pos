<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class PosTerminal extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    
    protected static ?string $navigationLabel = 'POS Terminal';
    
    protected static ?int $navigationSort = 1;
    
    // ✅ FIX: Remove 'static' - use 'protected string' only
    protected string $view = 'filament.pages.pos-terminal';
    
    /** @var array<int, array<string, mixed>> */
    public array $cart = [];
    
    public ?int $customer_id = null;
    public string $payment_method = 'cash';
    public float $discount_amount = 0;
    public float $tax_rate = 0;
    public string $search_term = '';
    
    /** @var Collection<int, Product> */
    public Collection $products;
    
    public float $subtotal = 0;
    public float $tax_amount = 0;
    public float $total_amount = 0;
    public string $barcode_input = '';
    
    public function mount(): void
    {
        $this->loadProducts();
        $this->calculateTotals();
    }
    
    public function loadProducts(): void
    {
        $query = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0);
            
        if (!empty($this->search_term)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search_term . '%')
                  ->orWhere('sku', 'like', '%' . $this->search_term . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search_term . '%');
            });
        }
        
        $this->products = $query->limit(20)->get();
    }
    
    #[On('search')]
    public function updateSearch(string $search): void
    {
        $this->search_term = $search;
        $this->loadProducts();
    }
    
    public function processBarcode(): void
    {
        if (empty($this->barcode_input)) {
            return;
        }
        
        $barcode = trim($this->barcode_input);
        
        $product = Product::where('barcode', $barcode)
            ->where('is_active', true)
            ->first();
        
        if (!$product) {
            $product = Product::where('sku', $barcode)
                ->where('is_active', true)
                ->first();
        }
        
        if ($product) {
            $this->addToCart($product->id);
            Notification::make()
                ->title('Product Found')
                ->body($product->name . ' added to cart')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Product Not Found')
                ->body('No product found with barcode/SKU: ' . $barcode)
                ->danger()
                ->send();
        }
        
        $this->barcode_input = '';
    }
    
    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock_quantity <= 0) {
            Notification::make()
                ->title('Product out of stock')
                ->danger()
                ->body($product?->name . ' is out of stock')
                ->send();
            return;
        }
        
        $existingIndex = collect($this->cart)->search(fn($item) => $item['product_id'] === $productId);
        
        if ($existingIndex !== false) {
            $this->cart[$existingIndex]['quantity']++;
            $this->cart[$existingIndex]['total_price'] = $this->cart[$existingIndex]['unit_price'] * $this->cart[$existingIndex]['quantity'];
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'quantity' => 1,
                'unit_price' => (float) $product->selling_price,
                'total_price' => (float) $product->selling_price,
            ];
        }
        
        $this->calculateTotals();
        $this->dispatch('cart-updated');
        
        Notification::make()
            ->title($product->name . ' added to cart')
            ->success()
            ->send();
    }
    
    public function updateCartQuantity(int $productId, int $quantity): void
    {
        $index = collect($this->cart)->search(fn($item) => $item['product_id'] === $productId);
        
        if ($index !== false) {
            if ($quantity <= 0) {
                unset($this->cart[$index]);
                $this->cart = array_values($this->cart);
            } else {
                $this->cart[$index]['quantity'] = $quantity;
                $this->cart[$index]['total_price'] = $this->cart[$index]['unit_price'] * $quantity;
            }
            
            $this->calculateTotals();
            $this->dispatch('cart-updated');
        }
    }
    
    public function removeFromCart(int $productId): void
    {
        $this->cart = array_values(
            array_filter($this->cart, fn($item) => $item['product_id'] !== $productId)
        );
        $this->calculateTotals();
        $this->dispatch('cart-updated');
        
        Notification::make()
            ->title('Item removed from cart')
            ->warning()
            ->send();
    }
    
    public function calculateTotals(): void
    {
        $this->subtotal = (float) collect($this->cart)->sum('total_price');
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
    }
    
    public function updatedDiscountAmount(): void
    {
        $this->calculateTotals();
    }
    
    public function updatedTaxRate(): void
    {
        $this->calculateTotals();
    }
    
    public function clearCart(): void
    {
        $this->cart = [];
        $this->customer_id = null;
        $this->discount_amount = 0;
        $this->tax_rate = 0;
        $this->calculateTotals();
        $this->dispatch('cart-updated');
        
        Notification::make()
            ->title('Cart cleared')
            ->info()
            ->send();
    }
    
    public function processPayment(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Cart is empty')
                ->warning()
                ->send();
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $tenant = filament()->getTenant();
            
            $invoice = Invoice::create([
                'tenant_id' => $tenant?->id,
                'customer_id' => $this->customer_id,
                'user_id' => auth()->id(),
                'invoice_number' => 'POS-' . strtoupper(uniqid()),
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->tax_amount,
                'discount_amount' => $this->discount_amount,
                'total_amount' => $this->total_amount,
                'payment_method' => $this->payment_method,
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
            
            foreach ($this->cart as $item) {
                InvoiceItem::create([
                    'tenant_id' => $tenant?->id,
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
                
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->stock_quantity -= $item['quantity'];
                    $product->save();
                }
            }
            
            DB::commit();
            
            $this->clearCart();
            
            Notification::make()
                ->title('Payment processed successfully!')
                ->success()
                ->body('Invoice #' . $invoice->invoice_number . ' - Amount: $' . number_format($this->total_amount, 2))
                ->send();
                
            $this->dispatch('payment-completed', invoiceId: $invoice->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Payment failed')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }
    
    protected function getViewData(): array
    {
        return [
            'customers' => Customer::where('is_active', true)->get(),
        ];
    }
}