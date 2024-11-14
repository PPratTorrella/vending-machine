@php use App\Presenters\VendingMachinePresenter; @endphp
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vending Machine</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap">
    <link rel="stylesheet" href="{{ asset('css/vending-machine.css') }}">
</head>

<body>
<div class="vending-machine">
    <h1 class="text-center">Vending Machine</h1>
    <h5 class="text-center">{{ $stateName }}</h5>
    <div class="machine-display">
        <h3 class="digital">{{ $displayMessage }}</h3>
    </div>

    <audio id="success-sound" src="{{ asset('sounds/dispense_sound.mp3') }}" preload="auto"></audio>

    @if (session('message'))
        <div class="alert alert-info small-alert" id="flash-message">
            {{ session('message') }}
        </div>
    @endif

    <div class="punch-button">
        <button onclick="document.getElementById('success-sound').play(); punchMachine()" class="btn btn-danger" id="play-sound-btn">
            <i class="fas fa-fist-raised"></i> Punch machine
        </button>
    </div>

    <?php /** @var VendingMachinePresenter $presenter */ ?>
    @if ($presenter && ($presenter->getItem() || count($presenter->getCoins())))
        <div class="alert alert-success">
            @if (($presenter->getItem()))
                <h4>Item Dispensed:</h4>
                <p>{{ $presenter->getItem() }}</p>
            @endif

            @if (count($presenter->getCoins()))
                <h4>Change Returned:</h4>
                <ul>
                    @foreach ($presenter->getCoins() as $coin)
                        <li>{{ $coin }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <h2>Insert Coin</h2>
            <form action="{{ route('vendingMachine.insertCoin') }}" method="POST" class="form-inline mb-3">
                @csrf
                <div class="form-group mb-2">
                    <label for="coin" class="sr-only">Coin</label>
                    <select name="coin" id="coin" class="form-control" required>
                        <option value="" disabled selected>Select Coin</option>
                        @foreach ($coinLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                        <option value="2"> €0.02 (old coin)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2 ml-2">Insert <i class="fas fa-coins"></i></button>
            </form>
            <p>
                Inserted Coins:
                {{ $presenter->formatInsertedCoins($insertedCoins) }}
                (Total: {{ $presenter->formatPrice($totalInserted) }})
            </p>

            <form action="{{ route('vendingMachine.returnCoins') }}" method="GET" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Return Coins <i class="fas fa-undo"></i>
                </button>
            </form>

            <h2>Select Item</h2>
            <form action="{{ route('vendingMachine.selectItem') }}" method="POST" class="form-inline mb-3">
                @csrf
                <div class="form-group mb-2">
                    <label for="item_code" class="sr-only">Item Code</label>
                    <input type="text" name="item_code" id="item_code" class="form-control" placeholder="Item Code" required>
                </div>
                <button type="submit" class="btn btn-success mb-2 ml-2">Get Item <i class="fas fa-shopping-cart"></i></button>
            </form>

            <h2>Service</h2>
            <form action="{{ route('vendingMachine.service') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="items">Inventory JSON:</label>
                    <textarea name="items" id="items" class="form-control" rows="2"> {"A1": {"name": "vodka", "price": 100, "count": 10}} </textarea>
                </div>
                <div class="form-group">
                    <label for="coins">Coins JSON:</label>
                    <textarea name="coins" id="coins" class="form-control" rows="1"> {"100": 10, "25": 10} </textarea>
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
                                <div class="product-code" title="Click to select this item">{{ $code }}</div>
                                <h5 class="card-title">{{ $itemData['item']->getName() }}</h5>
                                <p class="card-text"><strong>Price:</strong> {{ $presenter->formatPrice($itemData['item']->getPrice()) }}</p>
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
                        <i class="fas fa-coins coin-icon"></i> {{ $presenter->formatPrice($coinValue) }} x {{ $count }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="{{ asset('js/vending-machine.js') }}"></script>
<script>
    const punchRouteUrl = "{{ route('vendingMachine.punch') }}";
</script>

</body>
</html>
