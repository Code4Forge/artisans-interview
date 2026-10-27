@php
    $announcement = $announcement ?? null;
@endphp

<div class="flex flex-col gap-5">
    <div class="flex flex-col gap-1.5">
        <label for="title" class="text-sm font-semibold text-slate-700">Title <span class="text-red-500">*</span></label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $announcement?->title) }}"
            placeholder="e.g. Annual Sports Day Schedule"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('title') border-red-500 @else border-slate-300 @enderror"
            required
        >
        @error('title')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="audience" class="text-sm font-semibold text-slate-700">Target Audience <span class="text-red-500">*</span></label>
        <select
            id="audience"
            name="audience"
            onchange="handleAudienceChange(this.value)"
            class="w-full rounded-md border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            required
        >
            @foreach ($audiences as $val => $label)
                <option value="{{ $val }}" {{ old('audience', $announcement?->audience?->value ?? '') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('audience')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @php
        $selectedRecipientIds = old('recipient_ids', $announcement && $announcement->recipients ? $announcement->recipients->pluck('id')->all() : []);
        $currentAudience = old('audience', $announcement?->audience?->value ?? 'students');
        $isCustom = $currentAudience === 'custom';
    @endphp

    <!-- Select2 Custom User Selection Container -->
    <div id="custom-recipients-wrapper" class="{{ $isCustom ? '' : 'hidden' }} flex flex-col gap-2.5 rounded-md border border-indigo-200 bg-indigo-50/30 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-indigo-100 pb-2.5">
            <div>
                <label for="recipient_ids" class="text-sm font-bold text-slate-900">Select Specific Recipients <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-500">Filter and choose specific teachers, students, or parents who will receive this announcement and email.</p>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" id="btn-select-all" class="rounded-md bg-white border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                    Select All
                </button>
                <button type="button" id="btn-clear-all" class="rounded-md bg-white border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-2xs">
                    Clear Selection
                </button>
            </div>
        </div>

        <div class="w-full">
            <select
                id="recipient_ids"
                name="recipient_ids[]"
                multiple="multiple"
                class="w-full select2-recipients rounded-md border border-slate-300 text-sm"
                data-placeholder="Search and select recipients by name, email, or role..."
            >
                @foreach ($customUsers ?? [] as $u)
                    @php
                        $roleName = $u->roles->first()?->name ?? 'user';
                        $isSelected = in_array($u->id, $selectedRecipientIds, false);
                    @endphp
                    <option
                        value="{{ $u->id }}"
                        data-name="{{ $u->name }}"
                        data-email="{{ $u->email }}"
                        data-role="{{ $roleName }}"
                        {{ $isSelected ? 'selected' : '' }}
                    >
                        {{ $u->name }} ({{ $u->email }}) [{{ strtoupper($roleName) }}]
                    </option>
                @endforeach
            </select>
        </div>
        @error('recipient_ids')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="body" class="text-sm font-semibold text-slate-700">Announcement Body / Content <span class="text-red-500">*</span></label>
        <textarea
            id="body"
            name="body"
            rows="6"
            placeholder="Write announcement details here..."
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('body') border-red-500 @else border-slate-300 @enderror"
            required
        >{{ old('body', $announcement?->body) }}</textarea>
        @error('body')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5 border-t border-slate-100 pt-4">
        <label for="attachment" class="text-sm font-semibold text-slate-700">Attachment <span class="text-xs font-normal text-slate-500">(Optional: PDF, DOC, Images up to 10MB)</span></label>
        <input
            id="attachment"
            name="attachment"
            type="file"
            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp"
            class="w-full rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-700 file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
        >
        @error('attachment')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror

        @if ($announcement && $announcement->attachment_path)
            <div class="mt-2 flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 p-2.5">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                    <span class="text-xs font-medium text-slate-700">{{ $announcement->attachment_name ?: 'Current file' }}</span>
                </div>
                <label class="flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                    <input type="checkbox" name="remove_attachment" value="1" class="rounded-sm border-slate-300 text-rose-600 focus:ring-rose-500">
                    Remove attachment
                </label>
            </div>
        @endif
    </div>
</div>

