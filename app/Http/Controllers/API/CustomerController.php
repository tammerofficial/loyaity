<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Requests\EarnPointsRequest;
use App\Http\Requests\RedeemPointsRequest;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('membership_number', 'like', "%{$search}%");
            })
            ->when($request->tier, function ($query, $tier) {
                $query->where('tier', $tier);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return CustomerResource::collection($customers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->validated();
        $data['membership_number'] = $this->generateMembershipNumber();
        $data['joined_at'] = now();

        $customer = Customer::create($data);

        // Create loyalty card
        $loyaltyCard = $customer->loyaltyCards()->create([
            'card_number' => $this->generateCardNumber(),
            'status' => 'active',
            'issued_at' => now(),
        ]);

        $loyaltyCard->generateQRCode()->save();

        return new CustomerResource($customer->load('loyaltyCards'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return new CustomerResource($customer->load(['loyaltyCards', 'transactions' => function ($query) {
            $query->latest()->take(10);
        }]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }

    /**
     * Earn points for a customer
     */
    public function earnPoints(EarnPointsRequest $request, Customer $customer)
    {
        $data = $request->validated();
        
        try {
            $customer->earnPoints($data['points'], $data['description'] ?? null);
            
            return response()->json([
                'message' => 'Points earned successfully',
                'customer' => new CustomerResource($customer->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Redeem points for a customer
     */
    public function redeemPoints(RedeemPointsRequest $request, Customer $customer)
    {
        $data = $request->validated();
        
        try {
            $customer->redeemPoints($data['points'], $data['description'] ?? null);
            
            return response()->json([
                'message' => 'Points redeemed successfully',
                'customer' => new CustomerResource($customer->fresh())
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    private function generateMembershipNumber()
    {
        do {
            $membershipNumber = 'M' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Customer::where('membership_number', $membershipNumber)->exists());

        return $membershipNumber;
    }

    private function generateCardNumber()
    {
        do {
            $cardNumber = 'LC' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (\App\Models\LoyaltyCard::where('card_number', $cardNumber)->exists());

        return $cardNumber;
    }
}
