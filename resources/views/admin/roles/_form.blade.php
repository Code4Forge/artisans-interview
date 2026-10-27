@php($role = $role ?? null)

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="flex flex-col gap-1.5">
        <label for="name" class="text-sm font-semibold text-slate-700">Role Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $role?->name) }}"
            placeholder="e.g. Supervisor, Manager"
            class="w-full rounded-md border px-3.5 py-2.5 text-sm outline-none transition-shadow focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 @error('name') border-red-500 @else border-slate-300 @enderror"
            required
        >
        @error('name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

