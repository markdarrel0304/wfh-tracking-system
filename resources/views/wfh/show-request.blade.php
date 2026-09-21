<x-dashboard-layout title="WFH Request Details">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('wfh.my-requests') }}" class="text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to My Requests
            </a>
        </div>

        {{-- Request Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-lg font-semibold text-slate-800">Request Details</h4>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                    ];
                    $color = $statusColors[$wfhRequest->status] ?? 'bg-gray-100 text-gray-700';
                @endphp
                <span class="px-3 py-1 text-sm font-medium {{ $color }} rounded-full">
                    {{ ucfirst($wfhRequest->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Request Type</p>
                    <p class="text-sm font-medium text-slate-800">{{ $wfhRequest->request_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Date Range</p>
                    <p class="text-sm font-medium text-slate-800">
                        {{ $wfhRequest->date_from->format('M d, Y') }}
                        @if ($wfhRequest->date_to && $wfhRequest->date_to != $wfhRequest->date_from)
                            - {{ $wfhRequest->date_to->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Time</p>
                    <p class="text-sm font-medium text-slate-800">
                        {{ $wfhRequest->start_time ? date('g:i A', strtotime($wfhRequest->start_time)) : '---' }} -
                        {{ $wfhRequest->end_time ? date('g:i A', strtotime($wfhRequest->end_time)) : '---' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Submitted On</p>
                    <p class="text-sm font-medium text-slate-800">{{ $wfhRequest->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Reason / Purpose</p>
                <p class="text-sm text-slate-700 mt-1">{{ $wfhRequest->reason }}</p>
            </div>

            @if ($wfhRequest->supporting_document)
            <div class="mt-6">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Supporting Document</p>
                <a href="{{ asset('storage/' . $wfhRequest->supporting_document) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 mt-1 inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View Document
                </a>
            </div>
            @endif

            @if ($wfhRequest->status === 'approved' && $wfhRequest->approved_at)
            <div class="mt-6 pt-6 border-t border-slate-200">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Approved By</p>
                <p class="text-sm font-medium text-slate-800">{{ $wfhRequest->approverUser?->name ?? 'N/A' }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $wfhRequest->approved_at->format('M d, Y g:i A') }}</p>
            </div>
            @endif

            @if ($wfhRequest->status === 'rejected' && $wfhRequest->remarks)
            <div class="mt-6 pt-6 border-t border-slate-200">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Rejection Reason</p>
                <p class="text-sm text-slate-700 mt-1">{{ $wfhRequest->remarks }}</p>
            </div>
            @endif
        </div>
    </div>
</x-dashboard-layout>
