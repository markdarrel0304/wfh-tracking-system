<x-dashboard-layout title="Employee Profile">
    <div class="space-y-6">
        {{-- Back button --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.index') }}" class="text-blue-600 hover:text-blue-800 transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Employee List
            </a>
        </div>

        {{-- Profile header --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col md:flex-row gap-6">
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-bold">
                        {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-1">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
                            <p class="text-slate-500 mt-1">{{ $employee->employee_number }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Department</p>
                            <p class="text-sm font-medium text-slate-800 mt-1">{{ $employee->department->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Position</p>
                            <p class="text-sm font-medium text-slate-800 mt-1">{{ $employee->position ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Email</p>
                            <p class="text-sm font-medium text-slate-800 mt-1">{{ $employee->user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider">Phone</p>
                            <p class="text-sm font-medium text-slate-800 mt-1">{{ $employee->phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Profile Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4">Edit Profile</h4>
            <form method="POST" action="{{ route('employees.profile', $employee) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- Personal Information --}}
                <div class="border-b border-slate-200 pb-6">
                    <h5 class="text-sm font-semibold text-slate-700 mb-4">Personal Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                            <input type="text" name="first_name" value="{{ $employee->first_name }}" required
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Middle Name</label>
                            <input type="text" name="middle_name" value="{{ $employee->middle_name }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                            <input type="text" name="last_name" value="{{ $employee->last_name }}" required
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ $employee->phone }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Address</label>
                            <input type="text" name="address" value="{{ $employee->address }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Gender</label>
                            <select name="gender" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Gender</option>
                                <option value="male" {{ $employee->gender === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $employee->gender === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ $employee->gender === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Date of Birth</label>
                            <input type="date" name="date_of_birth" value="{{ $employee->date_of_birth?->format('Y-m-d') }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nationality</label>
                            <input type="text" name="nationality" value="{{ $employee->nationality }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Marital Status</label>
                            <select name="marital_status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Status</option>
                                <option value="single" {{ $employee->marital_status === 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ $employee->marital_status === 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ $employee->marital_status === 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ $employee->marital_status === 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Emergency Contact --}}
                <div class="pt-6">
                    <h5 class="text-sm font-semibold text-slate-700 mb-4">Emergency Contact</h5>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Contact Name</label>
                            <input type="text" name="emergency_contact_name" value="{{ $employee->emergency_contact_name }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Relationship</label>
                            <input type="text" name="emergency_contact_relationship" value="{{ $employee->emergency_contact_relationship }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                            <input type="text" name="emergency_contact_phone" value="{{ $employee->emergency_contact_phone }}"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6">
                    <a href="{{ route('employees.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Employment Details --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h4 class="text-lg font-semibold text-slate-800 mb-4">Employment Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Employee ID</label>
                    <p class="text-slate-600">{{ $employee->employee_number }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Hire Date</label>
                    <p class="text-slate-600">{{ $employee->date_hired ? $employee->date_hired->format('F j, Y') : 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">{{ ucfirst($employee->status) }}</span>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
