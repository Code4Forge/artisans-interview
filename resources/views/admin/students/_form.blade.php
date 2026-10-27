@php
    $student = $student ?? null;
@endphp

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="flex flex-col gap-1.5">
        <label for="name" class="text-sm font-semibold text-slate-700">Student Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $student?->name) }}"
            placeholder="e.g. John Smith"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('name') border-red-500 @else border-slate-300 @enderror"
            required
        >
        @error('name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="email" class="text-sm font-semibold text-slate-700">Student Email <span class="text-red-500">*</span></label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $student?->email) }}"
            placeholder="e.g. student@school.test"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('email') border-red-500 @else border-slate-300 @enderror"
            required
        >
        @error('email')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="flex flex-col gap-1.5">
        <label for="password" class="text-sm font-semibold text-slate-700">
            Password @if ($student) <span class="text-xs font-normal text-slate-500">(Optional)</span> @else <span class="text-red-500">*</span> @endif
        </label>
        <input
            id="password"
            name="password"
            type="password"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('password') border-red-500 @else border-slate-300 @enderror"
            @if (! $student) required @endif
            autocomplete="new-password"
            placeholder="{{ $student ? 'Leave blank to keep current' : 'Min 8 characters' }}"
        >
        @error('password')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="password_confirmation" class="text-sm font-semibold text-slate-700">
            Confirm Password @if (! $student) <span class="text-red-500">*</span> @endif
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="w-full rounded-md border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            @if (! $student) required @endif
            autocomplete="new-password"
            placeholder="Repeat password"
        >
    </div>
</div>

<!-- Parents Section (Add More Dynamic Repeater) -->
<div class="mt-4 border-t border-slate-200 pt-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-slate-900">Linked Parents</h3>
            <p class="text-xs text-slate-500">Add one or more parents/guardians for this student. They can be created or updated dynamically.</p>
        </div>
        <button
            type="button"
            onclick="addParentRow()"
            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add More Parent
        </button>
    </div>

    <div id="parents-container" class="space-y-3">
        @php
            $existingParents = old('parents');
            if ($existingParents === null) {
                if ($student && $student->parents->isNotEmpty()) {
                    $existingParents = $student->parents->map(fn($p) => [
                        'name' => $p->name,
                        'email' => $p->email,
                        'phone' => $p->phone,
                        'relationship' => $p->pivot?->relationship ?? \App\Enums\RelationshipStatus::Mother->value,
                    ])->toArray();
                } else {
                    $existingParents = [];
                }
            }
        @endphp

        @if (empty($existingParents))
            <p id="no-parents-hint" class="text-xs text-slate-400 italic py-2">No parents linked. Click "Add More Parent" above to add one.</p>
        @endif

        @foreach ($existingParents as $index => $parentItem)
            <div class="parent-row flex flex-wrap sm:flex-nowrap items-end gap-3 p-3.5 rounded-md border border-slate-200 bg-slate-50/50">
                <div class="w-full sm:w-1/4 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-700">Parent Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="parents[{{ $index }}][name]"
                        value="{{ $parentItem['name'] ?? '' }}"
                        placeholder="e.g. Mary Smith"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div class="w-full sm:w-1/4 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-700">Parent Email <span class="text-red-500">*</span></label>
                    <input
                        type="email"
                        name="parents[{{ $index }}][email]"
                        value="{{ $parentItem['email'] ?? '' }}"
                        placeholder="e.g. parent@school.test"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div class="w-full sm:w-1/4 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-700">Contact Number</label>
                    <input
                        type="text"
                        name="parents[{{ $index }}][phone]"
                        value="{{ $parentItem['phone'] ?? '' }}"
                        placeholder="e.g. +1 555 123 4567"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                </div>
                <div class="w-full sm:w-1/5 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-700">Relationship</label>
                    <select
                        name="parents[{{ $index }}][relationship]"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                    >
                        @foreach (\App\Enums\RelationshipStatus::cases() as $rel)
                            <option value="{{ $rel->value }}" {{ ($parentItem['relationship'] ?? '') === $rel->value ? 'selected' : '' }}>{{ $rel->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pb-1">
                    <button
                        type="button"
                        onclick="removeParentRow(this)"
                        class="action-btn action-btn-delete"
                        title="Remove Parent"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

