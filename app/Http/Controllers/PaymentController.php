use App\Services\MidtransService;
use App\Models\Transaction;
use Illuminate\Support\Str;

public function createPayment(MidtransService $midtrans)
{
    $orderId = 'ORDER-' . Str::uuid();
    $amount = 10000;

    Transaction::create([
        'order_id' => $orderId,
        'gross_amount' => $amount,
        'status' => 'pending',
    ]);

    $params = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => $amount,
        ],
    ];

    $snapToken = $midtrans->createTransaction($params);

    return response()->json([
        'snap_token' => $snapToken
    ]);
}
