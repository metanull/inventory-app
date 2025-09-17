@csrf
@php($c = $entityColor('languages'))
<dl>
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">Code (3 letters)<span class="text-red-500">*</span></dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="id" value="{{ old('id', $language->id ?? '') }}" @if(isset($language)) readonly @endif class="uppercase tracking-wide block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" maxlength="3" required />
            @error('id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">Internal Name<span class="text-red-500">*</span></dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="internal_name" value="{{ old('internal_name', $language->internal_name ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
            @error('internal_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">Legacy ID</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="backward_compatibility" value="{{ old('backward_compatibility', $language->backward_compatibility ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" maxlength="2" />
            @error('backward_compatibility')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">Default</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <label class="inline-flex items-center space-x-2">
                <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $language->is_default ?? false)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span class="text-sm text-gray-700">Mark as default language</span>
            </label>
            @error('is_default')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
</dl>
<div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50">
    <a href="{{ isset($language) ? route('languages.show', $language) : route('languages.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 shadow-sm">Cancel</a>
    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md {{ $c['button'] }} text-sm font-medium shadow-sm">Save</button>
</div>
 
