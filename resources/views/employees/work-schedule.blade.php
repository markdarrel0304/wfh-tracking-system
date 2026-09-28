<x-dashboard-layout title="Work Schedule">
    <div class="space-y-6">
        {{-- Header with actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-800">Work Schedule</h3>
                <p class="text-sm text-slate-500 mt-1">View and manage {{ $employee->first_name }} {{ $employee->last_name }}'s work schedule.</p>
            </div>
            <button onclick="document.getElementById('addScheduleModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Schedule Entry
            </button>
        </div>

        {{-- Employee info --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-medium text-slate-800">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                    <p class="text-sm text-slate-500">{{ $employee->employee_number }} • {{ $employee->department->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        {{-- Schedule table --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Day</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Shift</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Time In</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Time Out</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @if ($scheduleEntries->count() > 0)
                            @foreach ($scheduleEntries as $entry)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->day }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->date->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->shift }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->time_in ? $entry->time_in->format('h:i A') : '---' }}</td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $entry->time_out ? $entry->time_out->format('h:i A') : '---' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'bg-blue-100 text-blue-700',
                                            'work_day' => 'bg-green-100 text-green-700',
                                            'rest_day' => 'bg-gray-100 text-gray-700',
                                            'holiday' => 'bg-yellow-100 text-yellow-700',
                                        ];
                                        $color = $statusColors[$entry->status] ?? 'bg-gray-100 text-gray-700';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium {{ $color }} rounded-full">
                                        {{ ucfirst(str_replace('_', ' ', $entry->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button onclick="editSchedule({{ $entry->id }}, '{{ $entry->day }}', '{{ $entry->date->format('Y-m-d') }}', '{{ $entry->shift }}', '{{ $entry->time_in ? $entry->time_in->format('H:i') : '' }}', '{{ $entry->time_out ? $entry->time_out->format('H:i') : '' }}', '{{ $entry->status }}', '{{ $entry->remark ?? '' }}')" class="text-blue-600 hover:text-blue-800 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('schedule-entries.destroy', $entry) }}" onsubmit="return confirm('Are you sure you want to delete this schedule entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 mx-auto text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    <p>No work schedules found for {{ $employee->first_name }} {{ $employee->last_name }}. Add a schedule entry to get started.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add Schedule Modal --}}
    <div id="addScheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Add Schedule Entry</h3>
            </div>
            <form method="POST" action="{{ route('employees.store-schedule', $employee) }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Day</label>
                    <select name="day" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Date</label>
                    <input type="date" name="date" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
                    <select name="shift" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="Regular">Regular</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                        <option value="Flex">Flex</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time In</label>
                        <input type="time" name="time_in" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time Out</label>
                        <input type="time" name="time_out" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="scheduled">Scheduled</option>
                        <option value="work_day">Work Day</option>
                        <option value="rest_day">Rest Day</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any notes or remarks..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addScheduleModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Add Entry
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Schedule Modal --}}
    <div id="editScheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4">
            <div class="p-6 border-b border-slate-200">
                <h3 class="text-lg font-semibold text-slate-800">Edit Schedule Entry</h3>
            </div>
            <form id="editScheduleForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PATCH')
                <input type="hidden" name="entryId" id="editEntryId">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Shift</label>
                    <select name="shift" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="Regular">Regular</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                        <option value="Flex">Flex</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time In</label>
                        <input type="time" name="time_in" id="editTimeIn" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Time Out</label>
                        <input type="time" name="time_out" id="editTimeOut" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="scheduled">Scheduled</option>
                        <option value="work_day">Work Day</option>
                        <option value="rest_day">Rest Day</option>
                        <option value="holiday">Holiday</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" id="editRemark" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Add any notes or remarks..."></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editScheduleModal').classList.add('hidden')" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Update Entry
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editSchedule(id, day, date, shift, timeIn, timeOut, status, remark) {
            document.getElementById('editEntryId').value = id;
            document.getElementById('editScheduleForm').action = '/work-schedule-entries/' + id;
            document.querySelector('#editScheduleForm select[name="shift"]').value = shift;
            document.getElementById('editTimeIn').value = timeIn;
            document.getElementById('editTimeOut').value = timeOut;
            document.querySelector('#editScheduleForm select[name="status"]').value = status;
            document.getElementById('editRemark').value = remark || '';
            document.getElementById('editScheduleModal').classList.remove('hidden');
        }
    </script>
</x-dashboard-layout>