@csrf

@php($c = $entityColor('projects'))

<dl>
    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">Internal Name<span class="text-red-500">*</span></dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="internal_name" value="{{ old('internal_name', $project->internal_name ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required />
            @error('internal_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">Launch Date</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="date" name="launch_date" value="{{ old('launch_date', optional($project->launch_date ?? null)->format('Y-m-d')) }}" class="block w-60 px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
            @error('launch_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">Flags</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 space-y-2">
            @php($launched = old('is_launched', ($project->is_launched ?? false) ? '1' : '0'))
            @php($enabled = old('is_enabled', ($project->is_enabled ?? true) ? '1' : '0'))
            <label class="inline-flex items-center gap-2">
                <input type="hidden" name="is_launched" value="0" />
                <input type="checkbox" name="is_launched" value="1" @checked($launched=='1') class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span>Launched</span>
            </label>
            <label class="inline-flex items-center gap-2 ml-6">
                <input type="hidden" name="is_enabled" value="0" />
                <input type="checkbox" name="is_enabled" value="1" @checked($enabled=='1') class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <span>Enabled</span>
            </label>
        </dd>
    </div>

    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">Context</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <select name="context_id" class="block w-full max-w-md px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Default context</option>
                @foreach(($contexts ?? []) as $ctx)
                    <option value="{{ $ctx->id }}" @selected(old('context_id', $project->context_id ?? '')===$ctx->id)>{{ $ctx->internal_name }}</option>
                @endforeach
            </select>
            @error('context_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 bg-gray-50">
        <dt class="text-sm font-medium text-gray-700">Language</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <select name="language_id" class="block w-full max-w-xs px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Default language</option>
                @foreach(($languages ?? []) as $lang)
                    <option value="{{ $lang->id }}" @selected(old('language_id', $project->language_id ?? '')===$lang->id)>{{ $lang->internal_name }} ({{ $lang->id }})</option>
                @endforeach
            </select>
            @error('language_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>

    <div class="px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-gray-700">Legacy ID</dt>
        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
            <input type="text" name="backward_compatibility" value="{{ old('backward_compatibility', $project->backward_compatibility ?? '') }}" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Optional legacy identifier" />
            @error('backward_compatibility')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </dd>
    </div>
</dl>

<div class="px-4 py-4 sm:px-6 flex items-center justify-between bg-gray-50">
    <a href="{{ isset($project) ? route('projects.show', $project) : route('projects.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 shadow-sm">Cancel</a>
    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md {{ $c['button'] }} text-sm font-medium shadow-sm">Save</button>
</div>
