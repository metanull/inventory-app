@csrf

@php($c = $entityColor('partners'))

<dl>
    {{-- Internal Name --}}
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">
            Internal Name<span class="text-red-500">*</span>
        </dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="internal_name" value="{{ old('internal_name', $partner->internal_name ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
            @error('internal_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    {{-- Type --}}
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">
            Type<span class="text-red-500">*</span>
        </dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            @php($currentType = old('type', $partner->type ?? ''))
            <select name="type" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="">Select type...</option>
                <option value="museum" @selected($currentType==='museum')>Museum</option>
                <option value="institution" @selected($currentType==='institution')>Institution</option>
                <option value="individual" @selected($currentType==='individual')>Individual</option>
            </select>
            @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    {{-- Country --}}
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">
            Country
        </dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <livewire:country-select :value="old('country_id', $partner->country_id ?? null)" name="country_id" label="" />
        </dd>
    </div>

    {{-- Backward Compatibility --}}
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">
            Legacy ID
        </dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="backward_compatibility" value="{{ old('backward_compatibility', $partner->backward_compatibility ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional legacy identifier" />
            @error('backward_compatibility')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
</dl>

<div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50">
    <a href="{{ $partner ? route('partners.show', $partner) : route('partners.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-sm">Cancel</a>
    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md {{ $c['button'] }} text-sm font-medium shadow-sm">Save</button>
</div>
