<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vending Machine</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap">

    <style>
        .vending-machine {
            max-width: 900px;
            margin: 50px auto;
        }
        .machine-display {
            background-color: #000;
            color: #0F0; /* Digital green color */
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
            border: 2px solid #0F0;
        }
        .machine-display h2 {
            margin: 0;
            font-family: 'Share Tech Mono', monospace;
            font-size: 2em;
        }
        .product-icon {
            font-size: 30px;
        }
        .coin-icon {
            font-size: 24px;
            color: gold;
        }
        .product-card {
            text-align: center;
            margin-bottom: 20px;
        }
        .product-card .card-body {
            padding: 10px;
        }
        .product-code {
            background-color: #eee;
            padding: 5px;
            border-radius: 3px;
            display: inline-block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
<div class="vending-machine">
    <h1 class="text-center">Vending Machine</h1>
    <div class="machine-display">
        <h2>{{ $displayMessage }}</h2>
        @if(session('lastItem'))
            <p>You received: {{ session('lastItem')->getName() }}</p>
            @if(!empty(session('changeCoins')))
                <p>Your change: {{ implode(', ', session('changeCoins')) }} cents</p>
            @endif
        @endif
    </div>
    <div class="row">

        <div class="col-md-6">
            <h2>Insert Coin</h2>
            <form action="{{ route('vendingMachine.insertCoin') }}" method="POST" class="form-inline mb-3">
                @csrf
                <div class="form-group mb-2">
                    <label for="coin" class="sr-only">Coin</label>
                    <select name="coin" id="coin" class="form-control" required>
                        <option value="" disabled selected>Select Coin</option>
                        <option value="100">1 Dollar (100¢)</option>
                        <option value="50">50¢</option>
                        <option value="25">25¢</option>
                        <option value="10">10¢</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Insert <i class="fas fa-coins"></i></button>
            </form>
            <p>Inserted Coins: {{ implode(', ', $insertedCoins) }} (Total: {{ $totalInserted }}¢)</p>

            <h2>Select Item</h2>
            <form action="{{ route('vendingMachine.selectItem') }}" method="POST" class="form-inline mb-3">
                @csrf
                <div class="form-group mb-2">
                    <label for="item_code" class="sr-only">Item Code</label>
                    <input type="text" name="item_code" id="item_code" class="form-control" placeholder="Item Code" required>
                </div>
                <button type="submit" class="btn btn-success mb-2 ml-2">Get Item <i class="fas fa-shopping-cart"></i></button>
            </form>

            <h2>Service (Admin Only)</h2>
            <form action="{{ route('vendingMachine.service') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="items">Inventory JSON:</label>
                    <textarea name="items" id="items" class="form-control" rows="3" placeholder='{"A1": {"name": "Soda", "price": 150, "count": 10}}'></textarea>
                </div>
                <div class="form-group">
                    <label for="coins">Coins JSON:</label>
                    <textarea name="coins" id="coins" class="form-control" rows="3" placeholder='{"100": 10, "50": 20}'></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Update Inventory <i class="fas fa-tools"></i></button>
            </form>
        </div>

        <div class="col-md-6">
            <h2>Inventory Status</h2>
            <div class="row">
                @foreach($inventory['items'] as $code => $itemData)
                    <div class="col-md-6">
                        <div class="card product-card">
                            <div class="card-body">
                                <div class="product-code">{{ $code }}</div>
                                <h5 class="card-title">{{ $itemData['item']->getName() }}</h5>
                                <p class="card-text"><strong>Price:</strong> {{ $itemData['item']->getPrice() }}¢</p>
                                <p class="card-text"><strong>Stock:</strong> {{ $itemData['count'] }}</p>
                                <i class="fas fa-wine-bottle product-icon"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <h2>Available Coins</h2>
            <ul class="list-inline">
                @foreach($inventory['coins'] as $coinValue => $count)
                    <li class="list-inline-item">
                        <i class="fas fa-coins coin-icon"></i> {{ $coinValue }}¢ x {{ $count }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
</body>
</html>
