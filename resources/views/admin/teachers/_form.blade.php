@php($teacher = $teacher ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="flex flex-col gap-1.5">
        <label for="name" class="text-sm font-semibold text-slate-700">Full Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $teacher?->name) }}"
            placeholder="e.g. Jane Doe"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('name') border-red-500 @else border-slate-300 @enderror"
            required
        >
        @error('name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="email" class="text-sm font-semibold text-slate-700">Email Address <span class="text-red-500">*</span></label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $teacher?->email) }}"
            placeholder="e.g. teacher@school.test"
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
            Password @if ($teacher) <span class="text-xs font-normal text-slate-500">(Optional)</span> @else <span class="text-red-500">*</span> @endif
        </label>
        <input
            id="password"
            name="password"
            type="password"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('password') border-red-500 @else border-slate-300 @enderror"
            @if (! $teacher) required @endif
            autocomplete="new-password"
            placeholder="{{ $teacher ? 'Leave blank to keep current' : 'Min 8 characters' }}"
        >
        @error('password')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-col gap-1.5">
        <label for="password_confirmation" class="text-sm font-semibold text-slate-700">
            Confirm Password @if (! $teacher) <span class="text-red-500">*</span> @endif
        </label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="w-full rounded-md border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            @if (! $teacher) required @endif
            autocomplete="new-password"
            placeholder="Repeat password"
        >
    </div>
</div>
