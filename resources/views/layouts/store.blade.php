<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MiniStore — Store workspace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--ink:#202821;--muted:#899087;--line:#e8ebe5;--paper:#fff;--canvas:#f6f7f3;--green:#315b43;--green-dark:#264a36;--green-soft:#edf4ee;--orange:#d18857;--red:#bf6657;--shadow:0 16px 45px rgba(30,48,33,.08)}
        *{box-sizing:border-box}body{margin:0;background:var(--canvas);color:var(--ink);font:14px 'DM Sans',sans-serif}button,input,select{font:inherit}button{cursor:pointer}h1,h2,h3,p{margin:0}.app{min-height:100vh;display:grid;grid-template-columns:246px 1fr}.sidebar{position:sticky;top:0;height:100vh;background:#fff;border-right:1px solid var(--line);padding:28px 18px;display:flex;flex-direction:column;z-index:2}.brand{display:flex;align-items:center;gap:11px;padding:0 10px;margin-bottom:45px}.brand-mark{width:34px;height:34px;background:var(--green);border-radius:11px;display:grid;place-items:center;color:#fff;font-family:Manrope;font-size:19px;font-weight:800}.brand-name{font:800 18px Manrope;letter-spacing:-.7px}.brand-sub{font-size:10px;color:#9da39a;letter-spacing:1.1px;text-transform:uppercase;margin-top:2px}.nav-label{font-size:10px;color:#a5aaa1;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;padding:0 13px;margin:0 0 10px}.nav{display:grid;gap:5px}.nav a{height:43px;border-radius:10px;display:flex;align-items:center;gap:13px;padding:0 13px;color:#7b827a;text-decoration:none;font-weight:500;transition:.2s}.nav a:hover{background:#f6f8f4;color:var(--ink)}.nav a.active{background:var(--green-soft);color:var(--green);font-weight:700}.nav-icon{width:18px;height:18px;display:grid;place-items:center;font-size:16px}.side-bottom{margin-top:auto}.help-card{background:#f7f8f5;padding:16px;border-radius:12px;margin:14px 3px 20px}.help-card b{font-size:12px}.help-card p{font-size:11px;color:#898f87;margin:7px 0 12px;line-height:1.5}.help-card button{border:0;background:none;color:var(--green);padding:0;font-size:11px;font-weight:700}.profile{display:flex;gap:10px;align-items:center;padding:13px 9px 0;border-top:1px solid var(--line)}.avatar{width:34px;height:34px;display:grid;place-items:center;border-radius:50%;background:#e9d8c8;color:#6e4c34;font-size:11px;font-weight:700}.profile-name{font-size:12px;font-weight:700}.profile-role{color:#a0a59e;font-size:10px;margin-top:3px}.main{min-width:0}.topbar{height:75px;background:rgba(255,255,255,.85);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 42px;position:sticky;top:0;z-index:1;backdrop-filter:blur(10px)}.crumb{font-size:12px;color:#9ca198}.crumb strong{color:#515a50;font-weight:600}.top-actions{display:flex;align-items:center;gap:18px}.icon-btn{border:0;background:none;color:#717970;font-size:17px;position:relative}.notification{width:6px;height:6px;background:var(--orange);position:absolute;border-radius:50%;top:1px;right:0}.top-divider{height:25px;width:1px;background:var(--line)}.top-date{font-size:11px;color:#92988e}.content{max-width:1440px;margin:0 auto;padding:36px 42px 56px}.page-heading{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:28px}.eyebrow{font-size:10px;letter-spacing:1.4px;color:var(--green);text-transform:uppercase;font-weight:700;margin-bottom:8px}.page-heading h1{font:700 28px Manrope;letter-spacing:-1px}.page-heading p{color:#91978e;font-size:12px;margin-top:6px}.button{border:0;border-radius:9px;padding:11px 16px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:.2s}.button-primary{background:var(--green);color:white;box-shadow:0 5px 14px rgba(49,91,67,.16)}.button-primary:hover{background:var(--green-dark);transform:translateY(-1px)}.button-quiet{background:#fff;border:1px solid var(--line);color:#586057}.button-quiet:hover{background:#f8faf7}.button-small{padding:8px 11px;font-size:11px}.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}.metric{background:#fff;border:1px solid var(--line);border-radius:13px;padding:17px 19px;min-height:111px;position:relative;overflow:hidden}.metric-top{display:flex;align-items:center;justify-content:space-between;color:#969d93;font-size:11px}.metric-icon{width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#f3f6f1;color:var(--green);font-size:14px}.metric-value{font:700 23px Manrope;letter-spacing:-.6px;margin-top:14px}.metric-foot{font-size:10px;color:#a1a69f;margin-top:4px}.metric-foot .up{color:#4c8963;font-weight:700}.grid-main{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(250px,.85fr);gap:17px;margin-bottom:18px}.panel{background:#fff;border:1px solid var(--line);border-radius:13px;overflow:hidden}.panel-head{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #eff1ed}.panel-title{font:700 14px Manrope}.panel-subtitle{font-size:10px;color:#a0a59d;margin-top:4px}.text-link{border:0;background:none;color:var(--green);font-size:11px;font-weight:700;text-decoration:none}.chart-wrap{padding:18px 19px 12px;height:245px}.chart-legend{display:flex;align-items:center;gap:7px;font-size:10px;color:#979d94}.dot{width:7px;height:7px;background:#7b9e80;border-radius:50%}.chart{width:100%;height:170px;overflow:visible}.chart text{font:9px 'DM Sans';fill:#a2a79f}.chart .gridline{stroke:#edf0eb;stroke-width:1}.chart .area{fill:url(#fade)}.chart .line{fill:none;stroke:#557c5c;stroke-width:2.4;stroke-linecap:round;stroke-linejoin:round}.chart .point{fill:white;stroke:#557c5c;stroke-width:2}.inventory-list{padding:5px 19px 12px}.inventory-row{display:flex;align-items:center;gap:11px;padding:12px 0;border-bottom:1px solid #f0f1ed}.inventory-row:last-child{border:0}.product-thumb{width:38px;height:38px;border-radius:9px;background:#f2eee8;display:grid;place-items:center;font-size:18px}.product-thumb.green{background:#edf3eb}.product-thumb.pink{background:#f6ece9}.product-copy{min-width:0;flex:1}.product-copy b{font-size:11px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.product-copy span{font-size:10px;color:#9ca298;display:block;margin-top:3px}.stock-number{text-align:right;font:700 12px Manrope}.stock-label{font-size:9px;color:#a0a59c;text-align:right;margin-top:3px}.status{padding:5px 8px;border-radius:30px;font-size:9px;font-weight:700;white-space:nowrap}.status.low{color:#ac6c38;background:#fbf1e7}.status.out{color:#ae6253;background:#f9eae6}.status.good{color:#4d805b;background:#edf5ed}.table-panel{margin-bottom:18px}.table-tools{display:flex;align-items:center;gap:10px}.searchbox{height:33px;border:1px solid var(--line);border-radius:8px;display:flex;align-items:center;padding:0 10px;gap:7px;color:#a4aaa1}.searchbox input{border:0;outline:0;width:130px;font-size:10px}.table-scroll{overflow-x:auto}table{width:100%;border-collapse:collapse;text-align:left}th{font-size:9px;letter-spacing:.5px;text-transform:uppercase;color:#a1a69e;font-weight:700;background:#fcfcfa;padding:12px 19px;white-space:nowrap}td{padding:13px 19px;border-top:1px solid #f0f1ed;font-size:11px;white-space:nowrap}td.muted{color:#969c92}.customer-cell{display:flex;align-items:center;gap:9px;font-weight:600}.mini-avatar{height:27px;width:27px;border-radius:50%;background:#eee9dc;display:grid;place-items:center;color:#75654e;font-size:9px;font-weight:700}.mini-avatar.blue{background:#e5edf1;color:#557080}.mini-avatar.rose{background:#f1e6e5;color:#91645e}.mini-avatar.violet{background:#ebe6f1;color:#716186}.money{font-weight:700;color:#343c35}.section-view{display:none}.section-view.active{display:block}.notice{border-radius:9px;padding:12px 15px;margin-bottom:17px;background:#edf5ed;color:#37674a;font-size:12px}.notice.errors{background:#fbefec;color:#a14b40}.inventory-toolbar{display:flex;gap:10px;align-items:center}.catalog-card{display:flex;gap:13px;align-items:center}.empty-state{text-align:center;padding:42px;color:#92998f;font-size:12px}.modal-backdrop{position:fixed;inset:0;background:rgba(25,35,27,.38);display:none;align-items:center;justify-content:center;padding:18px;z-index:5;backdrop-filter:blur(3px)}.modal-backdrop.open{display:flex}.modal{width:min(560px,100%);max-height:90vh;overflow:auto;background:#fff;border-radius:16px;box-shadow:0 25px 80px rgba(15,35,18,.22);padding:24px}.modal-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px}.modal-head h2{font:700 19px Manrope;letter-spacing:-.5px}.modal-head p{font-size:11px;color:#959b92;margin-top:5px}.close-btn{border:0;background:#f5f6f3;border-radius:8px;width:30px;height:30px;color:#7a8178}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px}.field{display:grid;gap:6px}.field.full{grid-column:1/-1}.field label{font-size:10px;font-weight:700;color:#697168}.field input,.field select{height:39px;width:100%;border:1px solid #e4e8e1;border-radius:8px;padding:0 11px;outline:0;color:var(--ink);background:#fff;font-size:11px}.field input:focus,.field select:focus{border-color:#8aa58d;box-shadow:0 0 0 3px #eff5ef}.form-hint{font-size:9px;color:#9ca298}.modal-actions{display:flex;justify-content:flex-end;gap:9px;padding-top:19px;margin-top:19px;border-top:1px solid var(--line)}.order-lines{display:grid;gap:9px}.order-line{display:grid;grid-template-columns:1fr 94px 32px;gap:8px;align-items:center}.order-line select,.order-line input{height:37px;border:1px solid #e4e8e1;border-radius:8px;padding:0 9px;background:#fff;font-size:10px;width:100%}.remove-line{width:30px;height:30px;border:0;border-radius:8px;background:#fbefec;color:#b25c4d}.order-summary{background:#f7f8f5;border-radius:10px;padding:13px 15px;margin-top:15px;display:grid;gap:7px}.summary-row{display:flex;justify-content:space-between;font-size:10px;color:#828a80}.summary-row.total{border-top:1px solid #e5e9e1;padding-top:9px;margin-top:2px;font-weight:700;color:var(--ink);font-size:12px}.mobile-menu{display:none}
        @media(max-width:1050px){.app{grid-template-columns:210px 1fr}.content{padding:29px 25px}.topbar{padding:0 25px}.metrics{grid-template-columns:repeat(2,1fr)}.grid-main{grid-template-columns:1fr}.inventory-list{display:grid;grid-template-columns:1fr 1fr;column-gap:22px}}
        @media(max-width:700px){.app{display:block}.sidebar{position:fixed;left:-260px;width:246px;transition:.25s;box-shadow:var(--shadow)}.sidebar.open{left:0}.topbar{height:62px;padding:0 17px}.mobile-menu{display:inline-grid}.top-actions{gap:12px}.top-date,.top-divider{display:none}.content{padding:25px 16px}.page-heading{align-items:flex-start;gap:14px;flex-direction:column}.page-heading h1{font-size:24px}.metrics{gap:9px}.metric{padding:13px;min-height:98px}.metric-value{font-size:20px}.grid-main{gap:12px}.inventory-list{display:block}.panel-head{padding:15px}.chart-wrap{padding:12px;height:225px}.chart{height:158px}.searchbox input{width:100px}.table-panel .panel-head{align-items:flex-start;gap:12px;flex-direction:column}.table-tools{width:100%;justify-content:space-between}.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.modal{padding:19px}.order-line{grid-template-columns:1fr 72px 30px}.hide-mobile{display:none}}
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand"><div class="brand-mark">M</div><div><div class="brand-name">MiniStore</div><div class="brand-sub">retail workspace</div></div></div>
        <div class="nav-label">Workspace</div>
        <nav class="nav">
            <a href="{{ route('overview.index') }}" class="{{ $activeTab==='overview'?'active':'' }}"><span class="nav-icon">▦</span> Overview</a>
            <a href="{{ route('orders.index') }}" class="{{ $activeTab==='orders'?'active':'' }}"><span class="nav-icon">▤</span> Orders</a>
            <a href="{{ route('products.index') }}" class="{{ $activeTab==='products'?'active':'' }}"><span class="nav-icon">◇</span> Products</a>
            <a href="{{ route('customers.index') }}" class="{{ $activeTab==='customers'?'active':'' }}"><span class="nav-icon">♙</span> Customers</a>
        </nav>
        <div class="side-bottom"><div class="profile"><div class="avatar">A</div><div><div class="profile-name">Ajin</div><div class="profile-role">Store administrator</div></div><span style="margin-left:auto;color:#999">⌄</span></div></div>
    </aside>
    <main class="main">
        <header class="topbar"><div style="display:flex;align-items:center;gap:13px"><button class="icon-btn mobile-menu" id="menuToggle" aria-label="Open menu">☰</button><div class="crumb">Workspace <span style="margin:0 8px;color:#c0c4bd">/</span> <strong>{{ ucfirst($activeTab) }}</strong></div></div><div class="top-actions"><span class="top-date">Monday, October 26, 2026</span><span class="top-divider"></span><div class="avatar" style="width:30px;height:30px">A</div></div></header>
        <div class="content">
            @if(session('success'))<div class="notice">✓ &nbsp;{{ session('success') }}</div>@endif
            @if($errors->any())<div class="notice errors">{{ $errors->first() }}</div>@endif

            @yield('content')






        </div>
    </main>
</div>

<style>
    .field-error{color:var(--red);font-size:11px;line-height:1.5}
    .field input[aria-invalid="true"]{border-color:var(--red)}
</style>
<div class="modal-backdrop {{ $errors->product->any() ? 'open' : '' }}" id="productModal">
    <div class="modal">
        <div class="modal-head"><div><h2>Add a product</h2><p>Add a new item to the store catalog.</p></div><button class="close-btn" data-close aria-label="Close">×</button></div>
        <form method="POST" action="{{ route('products.store') }}" id="productForm" novalidate>
            @csrf
            @if($errors->product->any())
                <div class="notice errors" role="alert">Please correct the highlighted fields below.</div>
            @endif
            <div class="form-grid">
                @foreach([
                    ['name', 'productName', 'Product name', 'text', null, null, null, 120, 'e.g. Handmade ceramic bowl', ''],
                    ['code', 'productCode', 'Unique product code', 'text', null, null, null, 40, 'e.g. CER-012', ''],
                    ['price', 'productPrice', 'Price (INR)', 'number', '0.01', '99999999', '0.01', null, '0.00', ''],
                    ['tax_percentage', 'productTax', 'Tax percentage', 'number', '0', '100', '0.01', null, '', '8.25'],
                    ['stock', 'productStock', 'Opening stock', 'number', '0', '1000000', '1', null, '', '0'],
                ] as [$name, $id, $label, $type, $min, $max, $step, $length, $placeholder, $default])
                    <div class="field {{ $name === 'name' ? 'full' : '' }}">
                        <label for="{{ $id }}">{{ $label }} <span aria-hidden="true">*</span></label>
                        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" required
                            value="{{ $errors->product->any() ? old($name, $default) : $default }}"
                            placeholder="{{ $placeholder }}"
                            @if($min !== null) min="{{ $min }}" @endif
                            @if($max !== null) max="{{ $max }}" @endif
                            @if($step !== null) step="{{ $step }}" @endif
                            @if($length !== null) maxlength="{{ $length }}" @endif
                            aria-invalid="{{ $errors->product->has($name) ? 'true' : 'false' }}"
                            @error($name, 'product') aria-describedby="{{ $id }}Error" @enderror>
                        @error($name, 'product')
                            <span class="field-error" id="{{ $id }}Error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                @endforeach
            </div>
            <div class="modal-actions"><button type="button" class="button button-quiet" data-close>Cancel</button><button class="button button-primary">Save product</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop {{ $errors->customer->any() ? 'open' : '' }}" id="customerModal">
    <div class="modal" style="max-width:470px">
        <div class="modal-head"><div><h2>Add a customer</h2><p>Save a customer to use on future orders.</p></div><button class="close-btn" data-close aria-label="Close">×</button></div>
        <form method="POST" action="{{ route('customers.store') }}" id="customerForm" novalidate>
            @csrf
            @if($errors->customer->any())<div class="notice errors" role="alert">Please correct the highlighted fields below.</div>@endif
            <div class="form-grid">
                @foreach([['name', 'customerName', 'Full name', 'text', 120, 'e.g. Jordan Lee'], ['email', 'customerEmail', 'Email address', 'email', 190, 'jordan@example.com']] as [$name, $id, $label, $type, $length, $placeholder])
                    <div class="field full">
                        <label for="{{ $id }}">{{ $label }} <span aria-hidden="true">*</span></label>
                        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" required maxlength="{{ $length }}" placeholder="{{ $placeholder }}"
                            value="{{ $errors->customer->any() ? old($name) : '' }}"
                            aria-invalid="{{ $errors->customer->has($name) ? 'true' : 'false' }}"
                            @error($name, 'customer') aria-describedby="{{ $id }}Error" @enderror>
                        @error($name, 'customer')<span class="field-error" id="{{ $id }}Error" role="alert">{{ $message }}</span>@enderror
                    </div>
                @endforeach
            </div>
            <div class="modal-actions"><button type="button" class="button button-quiet" data-close>Cancel</button><button type="submit" class="button button-primary">Save customer</button></div>
        </form>
    </div>
</div>
<div class="modal-backdrop" id="orderModal"><div class="modal"><div class="modal-head"><div><h2>Create an order</h2><p>Choose a customer and add items to the order.</p></div><button class="close-btn" data-close aria-label="Close">×</button></div><form method="POST" action="{{ route('orders.store') }}" id="orderForm">@csrf<div class="field" style="margin-bottom:16px"><label for="orderCustomer">Customer</label><select id="orderCustomer" name="customer_id" required><option value="">Select a customer</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }} · {{ $customer->email }}</option>@endforeach</select>@if($customers->isEmpty())<span class="form-hint">Add a customer before creating an order.</span>@endif</div><div class="field"><label>Order items</label><div id="orderLines" class="order-lines"></div><button type="button" id="addOrderLine" class="button button-quiet button-small" style="justify-self:start;margin-top:3px">＋ Add another item</button></div><div class="field" style="margin-top:16px"><label for="orderPaidAmount">Customer paid amount (INR)</label><input id="orderPaidAmount" name="paid_amount" type="number" min="0" max="9999999999.99" step="0.01" value="{{ old('paid_amount', '0.00') }}" aria-invalid="{{ $errors->has('paid_amount') ? 'true' : 'false' }}" aria-describedby="orderPaymentHint {{ $errors->has('paid_amount') ? 'orderPaidAmountError' : '' }}"><span class="form-hint" id="orderPaymentHint">Enter the amount received. Partial payments are allowed; enter 0 for an unpaid order.</span>@error('paid_amount')<span class="field-error" id="orderPaidAmountError" role="alert">{{ $message }}</span>@enderror</div><div class="order-summary"><div class="summary-row"><span>Subtotal</span><b id="summarySubtotal">₹0.00</b></div><div class="summary-row"><span>Tax</span><b id="summaryTax">₹0.00</b></div><div class="summary-row total"><span>Total due</span><b id="summaryTotal">₹0.00</b></div><div class="summary-row"><span>Balance due</span><b id="summaryBalance">₹0.00</b></div><div class="summary-row"><span>Balance to return</span><b id="summaryChange">₹0.00</b></div></div><div class="modal-actions"><button type="button" class="button button-quiet" data-close>Cancel</button><button id="submitOrder" class="button button-primary" {{ $customers->isEmpty() || $products->where('stock','>',0)->isEmpty() ? 'disabled style=opacity:.5' : '' }}>Create order</button></div></form></div></div>
<script>
    // Payment modal is shared so it remains available after AJAX table updates.
    const paymentModal = document.createElement('div');
    paymentModal.className = 'modal-backdrop';
    paymentModal.id = 'paymentModal';
    paymentModal.innerHTML = `<div class="modal" style="max-width:470px">
        <div class="modal-head"><div><h2>Pay order balance</h2><p id="paymentOrderLabel"></p></div><button type="button" class="close-btn" data-close aria-label="Close">×</button></div>
        <form id="balancePaymentForm" novalidate>
            <div class="notice errors" id="paymentError" role="alert" hidden></div>
            <div class="field"><label for="balancePaymentAmount">Payment amount (INR)</label><input id="balancePaymentAmount" name="amount" type="number" min="0.01" step="0.01" required aria-describedby="paymentAmountError"><span class="field-error" id="paymentAmountError" role="alert"></span></div>
            <div class="modal-actions"><button type="button" class="button button-quiet" data-close>Cancel</button><button type="submit" class="button button-primary">Save payment</button></div>
        </form></div>`;
    document.body.append(paymentModal);
    const paymentForm = document.getElementById('balancePaymentForm');
    const paymentAmount = document.getElementById('balancePaymentAmount');
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-pay-order]');
        if (!button || paymentForm.dataset.submitting === 'true') return;
        paymentForm.reset();
        paymentForm.action = button.dataset.paymentUrl;
        paymentAmount.value = Number(button.dataset.balance).toFixed(2);
        paymentAmount.max = button.dataset.balance;
        paymentAmount.setAttribute('aria-invalid', 'false');
        document.getElementById('paymentAmountError').textContent = '';
        document.getElementById('paymentError').hidden = true;
        document.getElementById('paymentOrderLabel').textContent = 'Order #' + button.dataset.payOrder.padStart(5, '0') + ' · Balance due: ₹' + Number(button.dataset.balance).toFixed(2);
        paymentModal.classList.add('open');
        paymentAmount.focus();
    });
    paymentForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = paymentForm.querySelector('[type="submit"]');
        if (submit.disabled) return;
        submit.disabled = true;
        submit.textContent = 'Saving…';
        paymentForm.dataset.submitting = 'true';
        const error = document.getElementById('paymentError');
        error.hidden = true;
        paymentAmount.setAttribute('aria-invalid', 'false');
        document.getElementById('paymentAmountError').textContent = '';
        try {
            const url = new URL(paymentForm.action);
            url.searchParams.set('tab', @json($activeTab));
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: new FormData(paymentForm),
            });
            const result = await response.json();
            if (response.status === 422) {
                paymentAmount.setAttribute('aria-invalid', 'true');
                document.getElementById('paymentAmountError').textContent = result.errors?.amount?.[0] || result.message;
                paymentAmount.focus();
                return;
            }
            if (!response.ok) throw new Error('Payment failed');
            const updated = new DOMParser().parseFromString(result.html, 'text/html');
            const current = document.querySelector('.section-view');
            const replacement = updated.getElementById(current?.id);
            if (replacement) current.replaceWith(replacement);
            paymentModal.classList.remove('open');
            const notice = document.createElement('div');
            notice.className = 'notice';
            notice.setAttribute('role', 'status');
            notice.textContent = result.message + ' Remaining balance: ₹' + Number(result.data.balance_due).toFixed(2);
            document.querySelector('.content').prepend(notice);
        } catch (failure) {
            error.hidden = false;
            error.textContent = 'Unable to record payment. Please try again.';
        } finally {
            submit.disabled = false;
            submit.textContent = 'Save payment';
            paymentForm.dataset.submitting = '';
        }
    });
    const customerForm = document.getElementById('customerForm');
    function resetAddForm(form, defaults = {}) {
        form.reset();
        form.querySelectorAll('.field-error, .notice').forEach(node => node.remove());
        form.querySelectorAll('.field input').forEach(input => {
            input.value = defaults[input.name] ?? '';
            input.setAttribute('aria-invalid', 'false');
            input.removeAttribute('aria-describedby');
        });
    }
    function openCustomerForm() {
        if (customerForm.querySelector('[type="submit"]').disabled) return;
        resetAddForm(customerForm);
        document.getElementById('customerModal').classList.add('open');
    }
    customerForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = customerForm.querySelector('[type="submit"]');
        if (submit.disabled) return;
        customerForm.querySelectorAll('.field-error, .notice').forEach(node => node.remove());
        customerForm.querySelectorAll('input').forEach(input => {
            input.setAttribute('aria-invalid', 'false');
            input.removeAttribute('aria-describedby');
        });
        const notice = document.createElement('div');
        notice.className = 'notice errors';
        notice.setAttribute('role', 'alert');
        notice.hidden = true;
        customerForm.prepend(notice);
        submit.disabled = true;
        submit.textContent = 'Saving…';
        try {
            const url = new URL(customerForm.action);
            url.searchParams.set('tab', @json($activeTab));
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(customerForm),
            });
            const result = await response.json();
            if (response.status === 422) {
                notice.hidden = false;
                notice.textContent = 'Please correct the highlighted fields below.';
                Object.entries(result.errors || {}).forEach(([name, messages]) => {
                    const input = customerForm.elements.namedItem(name);
                    if (!input) return;
                    const error = document.createElement('span');
                    error.id = input.id + 'Error';
                    error.className = 'field-error';
                    error.setAttribute('role', 'alert');
                    error.textContent = messages[0];
                    input.setAttribute('aria-invalid', 'true');
                    input.setAttribute('aria-describedby', error.id);
                    input.after(error);
                });
                customerForm.querySelector('[aria-invalid="true"]')?.focus();
                return;
            }
            if (!response.ok) throw new Error('Customer save failed');
            const updated = new DOMParser().parseFromString(result.html, 'text/html');
            const currentSection = document.querySelector('.section-view');
            const newSection = updated.getElementById(currentSection?.id);
            if (newSection) currentSection.replaceWith(newSection);
            const customerSelect = document.getElementById('orderCustomer');
            const option = document.createElement('option');
            option.value = result.data.id;
            option.textContent = result.data.name + ' · ' + result.data.email;
            customerSelect.append(option);
            customerSelect.closest('.field').querySelector('.form-hint')?.remove();
            if (productData.some(product => product.stock > 0)) {
                const orderSubmit = document.getElementById('submitOrder');
                orderSubmit.disabled = false;
                orderSubmit.style.opacity = '';
            }
            customerForm.reset();
            notice.remove();
            document.getElementById('customerModal').classList.remove('open');
            const success = document.createElement('div');
            success.className = 'notice';
            success.setAttribute('role', 'status');
            success.textContent = result.message;
            document.querySelector('.content').prepend(success);
        } catch (error) {
            notice.hidden = false;
            notice.textContent = 'Unable to save the customer. Please try again.';
        } finally {
            submit.disabled = false;
            submit.textContent = 'Save customer';
        }
    });
    const productForm = document.getElementById('productForm');
    function openProductForm(product = null, updateUrl = null) {
        if (productForm.querySelector('.button-primary').disabled) return;
        resetAddForm(productForm, {tax_percentage: '8.25', stock: '0'});
        productForm.action = updateUrl || @json(route('products.store'));
        productForm.dataset.editing = product ? 'true' : '';
        document.querySelector('#productModal h2').textContent = product ? 'Edit product' : 'Add a product';
        document.querySelector('#productModal .modal-head p').textContent = product ? 'Update product details, pricing and stock.' : 'Add a new item to the store catalog.';
        document.querySelector('label[for="productStock"]').textContent = product ? 'Stock on hand *' : 'Opening stock *';
        productForm.querySelector('.button-primary').textContent = product ? 'Update product' : 'Save product';
        if (product) {
            for (const name of ['name', 'code', 'price', 'stock']) productForm.elements.namedItem(name).value = product[name];
            productForm.elements.namedItem('tax_percentage').value = product.tax;
        }
        document.getElementById('productModal').classList.add('open');
    }
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-edit-product]');
        if (!button) return;
        const product = productData.find(item => item.id === Number(button.dataset.editProduct));
        if (product) openProductForm(product, button.dataset.updateUrl);
    });
    productForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = productForm.querySelector('button[type="submit"], .modal-actions .button-primary');
        if (submit.disabled) return;
        productForm.querySelectorAll('.field-error, .notice').forEach(node => node.remove());
        productForm.querySelectorAll('input').forEach(input => {
            input.setAttribute('aria-invalid', 'false');
            input.removeAttribute('aria-describedby');
        });
        const notice = document.createElement('div');
        notice.className = 'notice errors';
        notice.setAttribute('role', 'alert');
        productForm.prepend(notice);
        notice.hidden = true;
        submit.disabled = true;
        submit.textContent = 'Saving…';
        try {
            const url = new URL(productForm.action);
            url.searchParams.set('tab', @json($activeTab));
            const body = new FormData(productForm);
            if (productForm.dataset.editing) body.set('_method', 'PUT');
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body,
            });
            const result = await response.json();
            if (response.status === 422) {
                notice.hidden = false;
                notice.textContent = 'Please correct the highlighted fields below.';
                Object.entries(result.errors || {}).forEach(([name, messages]) => {
                    const input = productForm.elements.namedItem(name);
                    if (!input) return;
                    const error = document.createElement('span');
                    error.id = input.id + 'Error';
                    error.className = 'field-error';
                    error.setAttribute('role', 'alert');
                    error.textContent = messages[0];
                    input.setAttribute('aria-invalid', 'true');
                    input.setAttribute('aria-describedby', error.id);
                    input.after(error);
                });
                productForm.querySelector('[aria-invalid="true"]')?.focus();
                return;
            }
            if (!response.ok) throw new Error('Unable to save the product. Please try again.');
            const updated = new DOMParser().parseFromString(result.html, 'text/html');
            const currentSection = document.querySelector('.section-view');
            const newSection = updated.getElementById(currentSection?.id);
            if (newSection) currentSection.replaceWith(newSection);
            const product = result.data;
            const updatedProduct = {id: product.id, name: product.name, code: product.code,
                price: Number(product.price), tax: Number(product.tax_percentage), stock: Number(product.stock)};
            const productIndex = productData.findIndex(item => item.id === product.id);
            if (productIndex === -1) productData.push(updatedProduct);
            else productData[productIndex] = updatedProduct;
            productOptions = buildProductOptions();
            lines.querySelectorAll('select').forEach(select => {
                const selected = select.value;
                select.innerHTML = '<option value="">Choose a product</option>' + productOptions;
                select.value = selected;
            });
            refreshTotals();
            if (productData.some(item => item.stock > 0) && document.getElementById('orderCustomer').options.length > 1) {
                document.getElementById('submitOrder').disabled = false;
                document.getElementById('submitOrder').style.opacity = '';
            }
            productForm.reset();
            productForm.querySelectorAll('.field-error, .notice').forEach(node => node.remove());
            document.getElementById('productModal').classList.remove('open');
            const success = document.createElement('div');
            success.className = 'notice';
            success.setAttribute('role', 'status');
            success.textContent = result.message;
            document.querySelector('.content').prepend(success);
        } catch (error) {
            notice.hidden = false;
            notice.textContent = 'Unable to save the product. Please try again.';
        } finally {
            submit.disabled = false;
            submit.textContent = productForm.dataset.editing ? 'Update product' : 'Save product';
        }
    });
    const productData       = {{ Illuminate\Support\Js::from($products->map(fn($p)=>['id'=>$p->id,'name'=>$p->name,'code'=>$p->code,'price'=>(float)$p->price,'tax'=>(float)$p->tax_percentage,'stock'=>$p->stock])->values()) }};
    function buildProductOptions() {
        return productData.filter(product => product.stock > 0).map(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = `${product.name} · ₹${product.price.toFixed(2)} · ${product.stock} available`;
            return option.outerHTML;
        }).join('');
    }
    let productOptions = buildProductOptions();
    const lines             = document.getElementById('orderLines');
    const orderForm = document.getElementById('orderForm');
    function openOrderForm() {
        if (orderForm.dataset.submitting === 'true') return;
        resetAddForm(orderForm, {paid_amount: '0.00'});
        document.getElementById('orderCustomer').value = '';
        document.getElementById('orderPaidAmount').setAttribute('aria-describedby', 'orderPaymentHint');
        lines.replaceChildren();
        if (productOptions) addLine();
        refreshTotals();
        document.getElementById('orderModal').classList.add('open');
    }
    orderForm.noValidate = true;
    orderForm.addEventListener('submit', async event => {
        event.preventDefault();
        const submit = document.getElementById('submitOrder');
        if (submit.disabled) return;
        orderForm.querySelectorAll('.field-error, .notice').forEach(node => node.remove());
        orderForm.querySelectorAll('input, select').forEach(input => {
            input.setAttribute('aria-invalid', 'false');
            input.removeAttribute('aria-describedby');
        });
        document.getElementById('orderPaidAmount').setAttribute('aria-describedby', 'orderPaymentHint');
        renumber();
        const notice = document.createElement('div');
        notice.className = 'notice errors';
        notice.setAttribute('role', 'alert');
        notice.hidden = true;
        orderForm.prepend(notice);
        submit.disabled = true;
        submit.textContent = 'Creating…';
        orderForm.dataset.submitting = 'true';
        try {
            const url = new URL(orderForm.action);
            url.searchParams.set('tab', @json($activeTab));
            const response = await fetch(url, {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                body: new FormData(orderForm),
            });
            const result = await response.json();
            if (response.status === 422) {
                notice.hidden = false;
                notice.textContent = 'Please correct the highlighted fields below.';
                Object.entries(result.errors || {}).forEach(([name, messages], index) => {
                    const fieldName = name.replace(/\.([^.]+)/g, '[$1]');
                    const input = orderForm.elements.namedItem(fieldName);
                    const error = document.createElement('span');
                    error.id = 'orderValidationError' + index;
                    error.className = 'field-error';
                    error.setAttribute('role', 'alert');
                    error.textContent = messages[0];
                    if (input) {
                        input.setAttribute('aria-invalid', 'true');
                        input.setAttribute('aria-describedby', error.id);
                        if (input.closest('.order-line')) {
                            error.style.gridColumn = '1 / -1';
                            input.closest('.order-line').append(error);
                        } else input.after(error);
                    } else lines.after(error);
                });
                orderForm.querySelector('[aria-invalid="true"]')?.focus();
                return;
            }
            if (!response.ok) throw new Error('Order creation failed');
            const updated = new DOMParser().parseFromString(result.html, 'text/html');
            const currentSection = document.querySelector('.section-view');
            const newSection = updated.getElementById(currentSection?.id);
            if (newSection) currentSection.replaceWith(newSection);
            productData.splice(0, productData.length, ...result.products.map(product => ({
                id: product.id, name: product.name, code: product.code, price: Number(product.price),
                tax: Number(product.tax_percentage), stock: Number(product.stock),
            })));
            productOptions = buildProductOptions();
            orderForm.reset();
            document.getElementById('orderCustomer').value = '';
            document.getElementById('orderPaidAmount').value = '0.00';
            lines.replaceChildren();
            if (productOptions) addLine();
            refreshTotals();
            notice.remove();
            document.getElementById('orderModal').classList.remove('open');
            const success = document.createElement('div');
            success.className = 'notice';
            success.setAttribute('role', 'status');
            success.textContent = result.message + ' Balance to return: ' + money(Number(result.data.change_due));
            document.querySelector('.content').prepend(success);
        } catch (error) {
            notice.hidden = false;
            notice.textContent = 'Unable to create the order. Please try again.';
        } finally {
            orderForm.dataset.submitting = '';
            submit.disabled = !productData.some(product => product.stock > 0) || document.getElementById('orderCustomer').options.length <= 1;
            submit.style.opacity = submit.disabled ? '.5' : '';
            submit.textContent = 'Create order';
        }
    });
    const money             = value => '₹' + value.toFixed(2);

    function addLine()
    {
        const row       = document.createElement('div'); row.className='order-line';
        row.innerHTML   = `<select name="items[0][product_id]" required><option value="">Choose a product</option>${productOptions}</select><input name="items[0][quantity]" type="number" min="1" step="1" value="1" required aria-label="Quantity"><button class="remove-line" type="button" aria-label="Remove item">×</button>`;
        lines.appendChild(row); row.querySelectorAll('select,input').forEach(el=>el.addEventListener('input',refreshTotals)); row.querySelector('.remove-line').addEventListener('click',()=>{row.remove();renumber();refreshTotals()});renumber();refreshTotals();
    }

    function renumber(){[...lines.querySelectorAll('.order-line')].forEach((row,index)=>{row.querySelector('select').name=`items[${index}][product_id]`;row.querySelector('input').name=`items[${index}][quantity]`})}
    function refreshTotals(){let subtotal=0,tax=0;lines.querySelectorAll('.order-line').forEach(row=>{const product=productData.find(item=>item.id===Number(row.querySelector('select').value));const quantity=Math.max(0,Number(row.querySelector('input').value)||0);if(product){const part=Math.round((product.price*quantity+Number.EPSILON)*100)/100;subtotal+=part;tax+=Math.round((part*product.tax/100+Number.EPSILON)*100)/100}});document.getElementById('summarySubtotal').textContent=money(subtotal);document.getElementById('summaryTax').textContent=money(tax);document.getElementById('summaryTotal').textContent=money(subtotal+tax);const paid=Math.max(0,Number(document.getElementById('orderPaidAmount').value)||0);document.getElementById('summaryBalance').textContent=money(Math.max(0,subtotal+tax-paid));document.getElementById('summaryChange').textContent=money(Math.max(0,paid-subtotal-tax))}
    document.getElementById('orderPaidAmount').addEventListener('input', refreshTotals);
    document.getElementById('addOrderLine').addEventListener('click',addLine);
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-modal]');
        if (!button) return;
        if (button.dataset.modal === 'productModal') {
            openProductForm();
            return;
        }
        if (button.dataset.modal === 'customerModal') {
            openCustomerForm();
            return;
        }
        if (button.dataset.modal === 'orderModal') {
            openOrderForm();
            return;
        }
        document.getElementById(button.dataset.modal).classList.add('open');
    });
    document.querySelectorAll('[data-close]').forEach(button=>button.addEventListener('click',()=>button.closest('.modal-backdrop').classList.remove('open')));
    document.querySelectorAll('.modal-backdrop').forEach(backdrop=>backdrop.addEventListener('click',event=>{if(event.target===backdrop)backdrop.classList.remove('open')}));
    document.addEventListener('keydown',event=>{if(event.key==='Escape')document.querySelectorAll('.modal-backdrop.open').forEach(modal=>modal.classList.remove('open'))});
    document.addEventListener('input', event => {
        const input = event.target;
        if (!input.matches('.table-search')) return;
        const query = input.value.toLowerCase();
        document.querySelectorAll(`#${input.dataset.table} tbody tr`).forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
    document.getElementById('menuToggle')?.addEventListener('click',()=>document.getElementById('sidebar').classList.toggle('open'));
    @if($errors->product->any())
        document.getElementById('productModal').classList.add('open');
    @elseif($errors->has('items') || $errors->has('items.*') || $errors->has('customer_id') || $errors->has('paid_amount'))
        document.getElementById('orderModal').classList.add('open');if(productOptions)addLine();
    @elseif($errors->customer->any())
        document.getElementById('customerModal').classList.add('open');
    @elseif($errors->any())
        document.getElementById('productModal').classList.add('open');
    @endif
</script>
</body>
</html>
