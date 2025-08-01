@extends('layouts.admin')

@section('title', 'إحصائيات الجسر - إدارة البطاقات')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">إحصائيات الجسر</h1>
            <a href="{{ route('admin.wallet-management.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                ← العودة للقائمة
            </a>
        </div>

        <!-- Bridge Status -->
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">حالة الجسر</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-800">حالة الاتصال</p>
                                <p class="text-lg font-semibold text-blue-900">
                                    @if(isset($connectionTest['success']) && $connectionTest['success'])
                                        <span class="text-green-600">متصل</span>
                                    @else
                                        <span class="text-red-600">غير متصل</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">الطلبات الناجحة</p>
                                <p class="text-lg font-semibold text-green-900">
                                    {{ $statistics['successful_requests'] ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-red-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">الطلبات الفاشلة</p>
                                <p class="text-lg font-semibold text-red-900">
                                    {{ $statistics['failed_requests'] ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bridge Information -->
        @if(isset($statistics['bridge_info']))
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">معلومات الجسر</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">اسم الجسر</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statistics['bridge_info']['bridge_name'] ?? 'غير محدد' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">إصدار الجسر</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statistics['bridge_info']['bridge_version'] ?? 'غير محدد' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">عنوان السيرفر</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statistics['bridge_info']['server_ip'] ?? 'غير محدد' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">رابط الداشبورد</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $statistics['bridge_info']['dashboard_url'] ?? 'غير محدد' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif

        <!-- Connection Test Results -->
        @if(isset($connectionTest))
        <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">نتائج اختبار الاتصال</h3>
                @if($connectionTest['success'])
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">اتصال ناجح</h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>تم الاتصال بالجسر بنجاح</p>
                                    @if(isset($connectionTest['response_time']))
                                        <p class="mt-1">وقت الاستجابة: {{ $connectionTest['response_time'] }}ms</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">فشل في الاتصال</h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <p>{{ $connectionTest['error'] ?? 'حدث خطأ غير معروف' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Bridge Logs -->
        @if(isset($logs) && !empty($logs))
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">سجلات الجسر</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">التاريخ</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">المستوى</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">الرسالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">البيانات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log['timestamp'] ?? 'غير محدد' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(($log['level'] ?? '') === 'ERROR')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">خطأ</span>
                                    @elseif(($log['level'] ?? '') === 'WARNING')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">تحذير</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">معلومات</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $log['message'] ?? 'غير محدد' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if(isset($log['data']))
                                        <pre class="text-xs bg-gray-100 p-2 rounded">{{ json_encode($log['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">سجلات الجسر</h3>
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا توجد سجلات</h3>
                    <p class="mt-1 text-sm text-gray-500">لم يتم العثور على سجلات للجسر</p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection 