@php
use Carbon\Carbon;

$initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
$weekStart = now()->copy()->startOfWeek(Carbon::MONDAY);
$weekDays = collect(range(0, 6))->map(fn ($offset) => $weekStart->copy()->addDays($offset));
$todayWfhRequest = $approvedWfhRequests->first(fn ($request) => now()->betweenIncluded($request->date_from, $request->date_to));
$statusColors = [
    'scheduled' => 'bg-blue-100 text-blue-700',
    'work_day' => 'bg-emerald-100 text-emerald-700',
    'rest_day' => 'bg-slate-100 text-slate-700',
    'holiday' => 'bg-amber-100 text-amber-700',
];
@endphp

<x-dashboard-layout title="My Profile">
    <div class="mx-auto max-w-6xl space-y-6 pb-8">
        <header class="border-b border-slate-200 pb-6">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">Account settings</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">My profile</h1>
            <p class="mt-2 text-sm text-slate-600">Keep your personal information and account security up to date.</p>
        </header>

        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
                <div class="flex items-center gap-5">
                    <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full bg-blue-600 text-center text-2xl font-bold leading-[5rem] text-white">
                        @if ($employee->photo)
                            <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->first_name }} {{ $employee->last_name }}" class="h-full w-full object-cover">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $employee->first_name }} {{ $employee->last_name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $employee->user->email }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">{{ $employee->department->name ?? 'No department' }}</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst(auth()->user()->role ?? 'employee') }}</span>
                        </div>
                    </div>
                </div>
                <a href="#change-password" class="inline-flex justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Change password</a>
            </div>
        </section>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 sm:px-8">
                        <h2 class="font-semibold text-slate-900">Personal information</h2>
                        <p class="mt-1 text-sm text-slate-500">Update the contact details used by your team.</p>
                    </div>

                    <form method="POST" action="{{ route('my-profile.update') }}" enctype="multipart/form-data" class="space-y-8 px-6 py-7 sm:px-8">
                        @csrf
                        @method('PATCH')

                        @if (session('profile_success'))
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('profile_success') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                                <p class="font-semibold">Please check the highlighted fields.</p>
                                <ul class="mt-2 list-inside list-disc space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="flex flex-col gap-4 border-b border-slate-100 pb-7 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-4">
                                <div id="profilePhotoPreview" class="h-16 w-16 shrink-0 overflow-hidden rounded-full bg-blue-600 text-center text-xl font-bold leading-[4rem] text-white">
                                    @if ($employee->photo)
                                        <img src="{{ asset('storage/' . $employee->photo) }}" alt="Profile photo" class="h-full w-full object-cover">
                                    @else
                                        {{ $initials }}
                                    @endif
                                </div>
                                <div><h3 class="font-semibold text-slate-900">Profile photo</h3><p class="mt-1 text-sm text-slate-500">JPG or PNG, maximum file size 2 MB. The preview uses the same circular crop as your profile.</p></div>
                            </div>
                            <label for="profilePhotoInput" class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50">Upload photo</label>
                            <input type="file" id="profilePhotoInput" name="photo" accept="image/jpeg,image/png" class="sr-only">
                            <p id="profilePhotoFileName" class="text-xs text-slate-500 sm:basis-full">No new photo selected.</p>
                        </div>

                        <div>
                            <h3 class="mb-5 text-sm font-semibold text-slate-900">Basic details</h3>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <div><label for="first_name" class="mb-2 block text-sm font-medium text-slate-700">First name <span class="text-red-500">*</span></label><input id="first_name" type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('first_name') ? 'border-red-500' : '' }}">@error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div><label for="middle_name" class="mb-2 block text-sm font-medium text-slate-700">Middle name</label><input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('middle_name') ? 'border-red-500' : '' }}">@error('middle_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div><label for="last_name" class="mb-2 block text-sm font-medium text-slate-700">Last name <span class="text-red-500">*</span></label><input id="last_name" type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}" required class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('last_name') ? 'border-red-500' : '' }}">@error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div><label for="phone" class="mb-2 block text-sm font-medium text-slate-700">Phone number</label><input id="phone" type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('phone') ? 'border-red-500' : '' }}">@error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div><label class="mb-2 block text-sm font-medium text-slate-700">Date of birth</label><x-profile-date-picker name="date_of_birth" :value="old('date_of_birth', $employee->date_of_birth?->format('Y-m-d'))" label="Select date of birth" :required="false" :yearRange="['past' => 100, 'future' => 0]" />@error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div><label for="gender" class="mb-2 block text-sm font-medium text-slate-700">Gender</label><select id="gender" name="gender" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('gender') ? 'border-red-500' : '' }}"><option value="">Select gender</option><option value="male" @selected(old('gender', $employee->gender) === 'male')>Male</option><option value="female" @selected(old('gender', $employee->gender) === 'female')>Female</option><option value="other" @selected(old('gender', $employee->gender) === 'other')>Other</option></select>@error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                                <div class="sm:col-span-2 lg:col-span-3"><label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email address</label><input id="email" type="email" value="{{ $employee->user->email }}" disabled class="block w-full rounded-lg border-slate-200 bg-slate-50 text-sm text-slate-500"><p class="mt-1 text-xs text-slate-500">Email is managed by your administrator.</p></div>
                                <div class="sm:col-span-2 lg:col-span-3"><label for="address" class="mb-2 block text-sm font-medium text-slate-700">Home address</label><input id="address" type="text" name="address" value="{{ old('address', $employee->address) }}" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('address') ? 'border-red-500' : '' }}">@error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            </div>
                        </div>

                        <div class="flex justify-end border-t border-slate-200 pt-6"><button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Save changes</button></div>
                    </form>
                </section>

                <section id="change-password" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 sm:px-8"><h2 class="font-semibold text-slate-900">Password and security</h2><p class="mt-1 text-sm text-slate-500">Use a new, unique password to keep your account secure.</p></div>
                    <form method="POST" action="{{ route('my-profile.change-password') }}" class="space-y-6 px-6 py-7 sm:px-8">
                        @csrf
                        @if (session('password_success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('password_success') }}</div>@endif
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div><label for="current_password" class="mb-2 block text-sm font-medium text-slate-700">Current password</label><input id="current_password" type="password" name="current_password" autocomplete="current-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('current_password') ? 'border-red-500' : '' }}">@error('current_password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label for="password" class="mb-2 block text-sm font-medium text-slate-700">New password</label><input id="password" type="password" name="password" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('password') ? 'border-red-500' : '' }}">@error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">Confirm new password</label><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="block w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}">@error('password_confirmation')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="flex justify-end border-t border-slate-200 pt-6"><button type="submit" class="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-700 focus:ring-offset-2">Update password</button></div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-6">
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-semibold text-slate-900">Employment details</h2>
                    <dl class="mt-5 space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Department</dt><dd class="text-right font-medium text-slate-800">{{ $employee->department->name ?? 'N/A' }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Role</dt><dd class="font-medium text-slate-800">{{ ucfirst(auth()->user()->role ?? 'employee') }}</dd></div>
                        <div class="flex items-center justify-between gap-4"><dt class="text-slate-500">Employee ID</dt><dd class="font-medium text-slate-800">{{ $employee->employee_number ?? 'N/A' }}</dd></div>
                    </dl>
                </section>

                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4"><div><h2 class="font-semibold text-slate-900">This week</h2><p class="mt-1 text-sm text-slate-500">Your work schedule</p></div><a href="{{ route('my-work-schedule') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all</a></div>
                    <div class="p-5">
                        <div class="grid grid-cols-7 gap-1.5">
                            @foreach ($weekDays as $day)
                                @php $wfhForDay = $approvedWfhRequests->first(fn ($request) => $day->betweenIncluded($request->date_from, $request->date_to)); @endphp
                                <a href="{{ route('my-work-schedule', ['date' => $day->format('Y-m-d')]) }}" class="rounded-lg px-1 py-2 text-center text-xs transition {{ $day->isToday() ? 'bg-blue-600 text-white' : ($wfhForDay ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-slate-50 text-slate-400 hover:bg-slate-100') }}"><span class="block text-[10px] font-medium uppercase">{{ $day->format('D') }}</span><span class="mt-1 block font-bold">{{ $day->format('d') }}</span></a>
                            @endforeach
                        </div>
                        @if ($todayWfhRequest)
                            <div class="mt-5 rounded-lg bg-slate-50 p-4"><div class="flex items-center justify-between gap-3"><span class="text-sm font-medium text-slate-700">Today</span><span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Approved WFH</span></div><dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-500">Hours</dt><dd class="font-medium text-slate-700">{{ Carbon::parse($todayWfhRequest->start_time)->format('g:i A') }} – {{ Carbon::parse($todayWfhRequest->end_time)->format('g:i A') }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Arrangement</dt><dd class="font-medium text-slate-700">{{ $todayWfhRequest->request_type }}</dd></div></dl></div>
                        @else
                            <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">No approved WFH request for today.</p>
                        @endif
                    </div>
                </section>
            </aside>
        </div>
    </div>

    @pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const photoInput = document.getElementById('profilePhotoInput');
            const photoPreview = document.getElementById('profilePhotoPreview');
            const photoFileName = document.getElementById('profilePhotoFileName');
            let previewUrl = null;

            photoInput?.addEventListener('change', () => {
                const [photo] = photoInput.files;

                if (! photo) {
                    return;
                }

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }

                previewUrl = URL.createObjectURL(photo);
                photoPreview.innerHTML = `<img src="${previewUrl}" alt="Selected profile photo preview" class="h-full w-full object-cover">`;
                photoFileName.textContent = `${photo.name} selected. Review the circular preview before saving.`;
            });
        });
    </script>
    @endPushOnce
</x-dashboard-layout>
