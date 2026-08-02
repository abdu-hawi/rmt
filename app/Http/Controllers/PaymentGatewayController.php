<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentOrder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;

class PaymentGatewayController extends Controller
{
    private function getEdfapayInitiateUrl(): string
    {
        return config('services.edfapay.initiate_url', 'https://api.edfapay.com/payment/initiate');
    }

    private function getEdfapayStatusUrl(): string
    {
        return config('services.edfapay.status_url', 'https://api.edfapay.com/payment/status');
    }

    private function getApiMerchantId(): string
    {
        return config('services.edfapay.merchant_id');
    }

    private function getApiMerchantPassword(): string
    {
        return config('services.edfapay.merchant_password');
    }

    public function paymentProcess(array $data)
    {
        try {
            $paymentGatewayId = $this->getApiMerchantId();
            $initiateUrl      = $this->getEdfapayInitiateUrl();

            $order_id     = $data['order_id'];
            $order_number = $data['order_number'];
            $amount       = $data['amount'];

            $currency    = 'SAR';
            $description = $data['description'] ?? 'E-commerce Order Payment';

            // بيانات العميل
            $payerFirstName = Auth::check() ? (Auth::user()->first_name ?? 'Guest') : 'Guest';
            $payerLastName  = Auth::check() ? (Auth::user()->last_name ?? 'User') : 'User';
            $payerEmail     = !empty($data['email']) ? $data['email'] : (Auth::check() ? Auth::user()->email : 'guest@example.com');
            $payerAddress   = $data['address'] ?? 'Riyadh, Saudi Arabia';
            $payerCountry   = 'SA';
            $payerCity      = $data['city_name'] ?? 'Riyadh';
            $payerPhone     = $data['phoneNumber'] ?? '+966500000000';
            $payerZip       = $data['postal'] ?? '11564';

            $successUrl = route('edfapay.success', [
                'order_number' => $order_number
            ]);

            // حفظ إيميل الطلب في Redis لمدة 24 ساعة لاستخدامه عند التحقق من Callback Hash
            Redis::connection('payments_conn')->setex('edfapay_email_' . $order_number, 86400, $payerEmail);

            $ip = request()->ip();
            $regex = "/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/";
            if (!preg_match($regex, $ip)) {
                $ip = "53.9.10.172";
            }

            $payload = [
                'action'            => 'SALE',
                'edfa_merchant_id'  => $paymentGatewayId,
                'order_id'          => $order_number,
                'order_amount'      => number_format((float)$amount, 2, '.', ''),
                'order_currency'    => $currency,
                'order_description' => $description,
                'req_token'         => 'N',
                'payer_first_name'  => $payerFirstName,
                'payer_last_name'   => $payerLastName,
                'payer_address'     => $payerAddress,
                'payer_country'     => $payerCountry,
                'payer_city'        => $payerCity,
                'payer_email'       => $payerEmail,
                'payer_phone'       => $payerPhone,
                'payer_zip'         => $payerZip,
                'payer_ip'          => $ip,
                'term_url_3ds'      => $successUrl,
                'recurring_init'    => 'N',
                'auth'              => 'N',
            ];

            $payload['hash'] = $this->calculateEdfaPayInitiateHash($payload);

            $response = Http::asForm()->post($initiateUrl, $payload);

            if ($response->successful() && isset($response->json()['redirect_url'])) {
                return Redirect::to($response->json()['redirect_url']);
            }

            $responseData = $response->json();
            $errorMsg  = $responseData['error_message'] ?? $responseData['message'] ?? $responseData['error'] ?? __("payment_gatways.payment_initialization_failed");
            $errorCode = $responseData['error_code'] ?? 'N/A';

            return redirect()->back()->withErrors([
                'payment_error' => __("payment_gatways.payment_initialization_failed") . ": " . $errorMsg . " (Code: {$errorCode})"
            ]);
        } catch (\Exception $e) {
            Log::channel("edfaPay")->error('Edfapay paymentProcess exception', [
                'error_message' => $e->getMessage(),
                'error_code'    => $e->getCode(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors([
                'payment_error' => __("payment_gatways.payment_initialization_failed") . ": " . $e->getMessage()
            ]);
        }
    }

    private function calculateEdfaPayInitiateHash(array $data): string
    {
        $stringToConcatenate =
            ($data['order_id'] ?? '') .
            ($data['order_amount'] ?? '') .
            ($data['order_currency'] ?? '') .
            ($data['order_description'] ?? '') .
            $this->getApiMerchantPassword();

        return sha1(md5(strtoupper($stringToConcatenate)));
    }

    /**
     * استعلام مباشر عن حالة الدفع من بوابة EdfaPay
     * (يُستخدم كخطوة أخيرة إذا لم نجد البيانات في مفتاح Redis)
     */
    private function paymentStatus(string $order_number): ?array
    {
        try {
            $statusUrl = $this->getEdfapayStatusUrl();

            $payload = [
                'action'           => 'SALE',
                'edfa_merchant_id' => $this->getApiMerchantId(),
                'order_id'         => $order_number,
            ];

            $payload['hash'] = $this->calculateEdfaPayStatusHash($payload);

            $response = Http::asForm()->post($statusUrl, $payload);

            if (!$response->successful()) {
                Log::channel("edfaPay")->warning('Edfapay paymentStatus http error', [
                    'order_id'   => $order_number,
                    'status'     => $response->status(),
                    'body'       => $response->body(),
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::channel("edfaPay")->error('Edfapay paymentStatus exception', [
                'order_id'      => $order_number,
                'error_message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function calculateEdfaPayStatusHash(array $data): string
    {
        $stringToConcatenate =
            ($data['order_id'] ?? '') .
            $this->getApiMerchantPassword();

        return sha1(md5(strtoupper($stringToConcatenate)));
    }

    public function paymentSuccess(Request $request)
    {
        $order_number = $request->query('order_number') ?? $request->input('order_number');

        return redirect()->route('checkout.processing', ['order_number' => $order_number]);
    }

    public function checkPaymentStatusAjax(Request $request)
    {
        $order_number = $request->query('order_number') ?? $request->route('order_number');
        $isFallback   = $request->query('fallback') === 'true';

        if (!$order_number) {
            return response()->json(['status' => 'error', 'message' => 'Order number required'], 400);
        }

        // جلب Order الداخلي لاستخراج order_id المربوط بـ Redis
        $order = \App\Models\Order::where('order_number', $order_number)->first();

        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 444);
        }

        // 7. التأكد من Redis باستعمال order_id الداخلي
        $redisData = Redis::connection('payments_conn')->get("paymentGatewayCallback:{$order->id}");

        if ($redisData) {
            $this->markOrderCompleted($order);

            return response()->json([
                'status' => 'completed',
                'source' => 'redis',
                'data'   => json_decode($redisData, true)
            ]);
        }

        // 8. إذا انتهت 15 محاولة ولم يوجد في Redis، يتم الاستعلام المباشر عبر paymentStatus
        if ($isFallback) {
            $statusResponse = $this->paymentStatus($order_number);

            if ($statusResponse && strtoupper($statusResponse['status'] ?? '') === 'SETTLED') {
                $this->markOrderCompleted($order);

                return response()->json([
                    'status' => 'completed',
                    'source' => 'api_status',
                    'data'   => $statusResponse
                ]);
            }

            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment status check failed or declined'
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    private function markOrderCompleted(\App\Models\Order $order): void
    {
        if ($order->status !== 'completed') {
            $order->update(['status' => 'completed']);
            Log::channel("edfaPay")->info('Order marked as completed', [
                'order_id'      => $order->id,
                'order_number'  => $order->order_number,
            ]);
        }
    }

    public function paymentFailed(Request $request)
    {
        $order_number = $request->query('order_number') ?? $request->query('order_id');
        $errorMessage = $request->query('error_message', __("payment_gatways.payment_failed"));
        $errorCode    = $request->query('error_code');

        Log::error("EdfaPay Payment Failed: " . $errorMessage . ($errorCode ? " (Code: {$errorCode})" : ""), [
            'order_id' => $order_number ?? 'N/A'
        ]);

        return redirect()->back()->withErrors([
            'payment_error' => __("payment_gatways.payment_failed") . ": " . $errorMessage . ($errorCode ? " (Code: {$errorCode})" : "")
        ]);
    }

    /**
     * Callback المستلم من بوابة الدفع EdfaPay
     */
    public function handleCallback(Request $request)
    {
        $callbackData = $request->all();

        // 1. التحقق من الهاش
        if (!$this->verifyEdfaPayCallbackHash($callbackData)) {
            Log::error('Edfapay handleCallback: verifyEdfaPayCallbackHash failed', $callbackData);
            return response('ERROR', 400)->header('Content-Type', 'text/plain');
        }

        $action    = $callbackData['action'] ?? null;
        $result    = $callbackData['result'] ?? null;
        $status    = $callbackData['status'] ?? null;
        $order_number = $callbackData['order_id'] ?? null;
        $trans_id  = $callbackData['trans_id'] ?? null;
        $amount    = $callbackData['amount'] ?? null;
        $cardBrand = $callbackData['card_brand'] ?? null;
        $rrn       = $callbackData['rrn'] ?? null;

        if (!$order_number) {
            Log::error('Edfapay handleCallback: Missing order_id', $callbackData);
            return response('ERROR', 400)->header('Content-Type', 'text/plain');
        }

        // المفتاح القادم من البوابة هو order_number (علاقة الطرف الثالث)
        // نعرّف الداخلي Order::id للعلاقات الداخلية ومفتاح Redis
        $order = Order::where('order_number', $order_number)->first();
        $order_id = $order ? $order->id : $order_number;

        try {
            // 2. تسجيل العملية دائماً (PaymentOrder.order_id = Order::id الداخلي)
            $orderPayment = PaymentOrder::updateOrCreate(
                ['trans_id' => $trans_id],
                [
                    'order_id'   => $order_id,
                    'rrn'        => $rrn,
                    'action'     => $action,
                    'result'     => $result,
                    'status'     => ($result === 'SUCCESS' && $status === 'SETTLED') ? 'completed' : 'failed',
                    'amount'     => $amount,
                    'card_brand' => $cardBrand,
                    'payload'    => $callbackData,
                ]
            );

            // 3. التمرير لـ Redis في حال النجاح فقط باستعمال Order::id الداخلي
            if ($action === 'SALE' && $result === 'SUCCESS' && $status === 'SETTLED') {
                $callbackData['paymentGatewayId'] = $cardBrand;
                $callbackData['orderPaymentId']   = $orderPayment->id;

                Redis::connection('payments_conn')->setex(
                    "paymentGatewayCallback:{$order_id}",
                    18000,
                    json_encode($callbackData)
                );
            }

            return response('OK', 200)->header('Content-Type', 'text/plain');
        } catch (\Throwable $th) {
            Log::error('Edfapay handleCallback: Exception during process', [
                'merchantOrderId' => $order_number,
                'error_message'   => $th->getMessage()
            ]);

            return response('ERROR', 500)->header('Content-Type', 'text/plain');
        }
    }

    private function verifyEdfaPayCallbackHash(array $data): bool
    {
        $orderId      = $data['order_id'] ?? '';
        $transId      = $data['trans_id'] ?? '';
        $cardNumber   = $data['card'] ?? '';
        $receivedHash = $data['hash'] ?? '';

        // استرجاع الإيميل من Redis
        $email = Redis::connection('payments_conn')->get('edfapay_email_' . $orderId);

        if (!$email) {
            Log::error("EdfaPay Hash Error: Email missing in Cache for Order: " . $orderId);
            return false; // إذا لم يجد الإيميل، يفشل الهاش فوراً لحماية النظام
        }

        $first6   = substr($cardNumber, 0, 6);
        $last4    = substr($cardNumber, -4);
        $cardPart = $first6 . $last4;

        $step1 = strrev($email);
        $step2 = $this->getApiMerchantPassword();
        $step3 = $transId;
        $step4 = strrev($cardPart);

        $combinedString = $step1 . $step2 . $step3 . $step4;
        $calculatedHash = md5(strtoupper($combinedString));

        return (strtolower($calculatedHash) === strtolower($receivedHash));
    }
}
