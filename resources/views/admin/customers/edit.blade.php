@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-bold mb-6">Edit Customer: {{ $customer->name }}</h1>

                <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ $customer->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $customer->email }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="available_points" class="block text-sm font-medium text-gray-700">Available Points</label>
                            <input type="text" name="available_points" id="available_points" value="{{ number_format($customer->available_points) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="tier" class="block text-sm font-medium text-gray-700">Tier</label>
                            <input type="text" name="tier" id="tier" value="{{ $customer->tier }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-4">Adjust Points</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="points_to_add" class="block text-sm font-medium text-gray-700">Add Points</label>
                                <input type="number" name="points_to_add" id="points_to_add" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="points_to_redeem" class="block text-sm font-medium text-gray-700">Redeem Points</label>
                                <input type="number" name="points_to_redeem" id="points_to_redeem" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <input type="text" name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Update Customer</button>
                    </div>
                </form>

                        <div class="mt-12">
            <h2 class="text-xl font-semibold mb-4"> Apple Wallet</h2>
            <p class="text-gray-600 mb-4">Add this customer's loyalty card to Apple Wallet on iPhone.</p>
            <div class="flex gap-3 flex-wrap">
                <a href="{{ route('admin.customers.wallet-qr', $customer) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2">
                    📱 Show QR Code
                </a>
                <a href="{{ route('admin.customers.wallet-pass', $customer) }}" 
                   class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 flex items-center gap-2">
                     Download Pass
                </a>
            </div>
        </div>
            </div> 
        </div>
    </div>
</div>
@endsection
