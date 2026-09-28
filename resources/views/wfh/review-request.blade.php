<x-dashboard-layout title="Review WFH Request">
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <a href="{{ route('wfh.approval') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 transition hover:text-blue-700">← Back to approvals</a>
                <p class="mt-4 text-sm font-semibold uppercase tracking-wider text-blue-600">Request review</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Review remote-work request</h1>
                <p class="mt-2 text-sm text-slate-600">Confirm the request details, then approve or decline it.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-800">Pending review</span>
        </div>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <article class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5"><h2 class="text-lg font-bold text-slate-900">Request details</h2><p class="mt-1 text-sm text-slate-500">Submitted by {{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}.</p></div>
                <div class="space-y-6 p-6">
                    <div class="rounded-xl bg-slate-50 p-4"><p class="font-semibold text-slate-900">{{ $wfhRequest->employee->first_name }} {{ $wfhRequest->employee->last_name }}</p><p class="mt-1 text-sm text-slate-600">{{ $wfhRequest->employee->user->email }}</p><p class="mt-1 text-sm text-slate-500">{{ $wfhRequest->employee->department?->name ?? 'No department assigned' }}</p></div>
                    <dl class="grid gap-4 sm:grid-cols-2"><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Arrangement</dt><dd class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->request_type }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Requested dates</dt><dd class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->date_from->format('M j, Y') }}@if (! $wfhRequest->date_from->isSameDay($wfhRequest->date_to)) – {{ $wfhRequest->date_to->format('M j, Y') }}@endif</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Work hours</dt><dd class="mt-1 font-semibold text-slate-800">{{ date('g:i A', strtotime($wfhRequest->start_time)) }} – {{ date('g:i A', strtotime($wfhRequest->end_time)) }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Submitted</dt><dd class="mt-1 font-semibold text-slate-800">{{ $wfhRequest->created_at->format('M j, Y g:i A') }}</dd></div></dl>
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4"><p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Employee reason</p><p class="mt-2 text-sm leading-6 text-slate-700">{{ $wfhRequest->reason }}</p>@if ($wfhRequest->supporting_document)<a href="{{ route('wfh.requests.supporting-document', $wfhRequest) }}" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">Download supporting document</a>@endif</div>
                </div>
            </article>

            <form method="POST" action="{{ route('wfh.requests.approval', $wfhRequest) }}" enctype="multipart/form-data" class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @csrf
                @method('PATCH')
                <h2 class="text-lg font-bold text-slate-900">Decision</h2><p class="mt-1 text-sm text-slate-500">The employee will be notified after you decide.</p>
                <label for="remarks" class="mt-5 block text-sm font-semibold text-slate-800">Decision note <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea id="remarks" name="remarks" rows="6" maxlength="1000" class="mt-2 w-full rounded-lg border-slate-300 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Add a note for the employee.">{{ old('remarks') }}</textarea>
                @error('remarks')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                <label for="reviewer_document" class="mt-5 block text-sm font-semibold text-slate-800">Supporting document <span class="font-normal text-slate-400">(optional)</span></label>
                <p class="mt-1 text-xs leading-5 text-slate-500">Visible to the employee after you make a decision. PDF, Word, or image up to 2 MB.</p>
                <input id="reviewer_document" type="file" name="reviewer_document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" class="mt-2 block w-full rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-600 file:mr-3 file:border-0 file:bg-blue-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-blue-700">
                @error('reviewer_document')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                <div class="mt-5 grid gap-3"><button type="submit" name="status" value="approved" class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Approve request</button><button type="submit" name="status" value="rejected" class="rounded-lg border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Not approve</button></div>
            </form>
        </section>
    </div>
</x-dashboard-layout>
