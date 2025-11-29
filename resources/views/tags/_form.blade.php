@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
        <x-form.input 
            name="internal_name" 
            :value="old('internal_name', $tag->internal_name ?? '')" 
            required 
        />
    </x-form.field>

    <x-form.field label="Category" name="category" variant="gray">
        <x-form.select 
            name="category" 
            :value="old('category', $tag->category ?? '')"
        >
            <option value="">-- No Category --</option>
            <option value="keyword" @selected(old('category', $tag->category ?? '') === 'keyword')>Keyword</option>
            <option value="material" @selected(old('category', $tag->category ?? '') === 'material')>Material</option>
            <option value="artist" @selected(old('category', $tag->category ?? '') === 'artist')>Artist</option>
            <option value="dynasty" @selected(old('category', $tag->category ?? '') === 'dynasty')>Dynasty</option>
        </x-form.select>
    </x-form.field>

    <x-form.field label="Language" name="language_id" variant="gray">
        <x-form.select 
            name="language_id" 
            :value="old('language_id', $tag->language_id ?? '')"
        >
            <option value="">-- No Language --</option>
            @foreach(\App\Models\Language::all() as $language)
                <option value="{{ $language->id }}" @selected(old('language_id', $tag->language_id ?? '') === $language->id)>
                    {{ $language->name }}
                </option>
            @endforeach
        </x-form.select>
    </x-form.field>

    <x-form.field label="Description" name="description" variant="gray" required>
        <x-form.textarea 
            name="description" 
            :value="old('description', $tag->description ?? '')"
            rows="4"
            required 
        />
    </x-form.field>

    <x-form.field label="Legacy ID" name="backward_compatibility">
        <x-form.input 
            name="backward_compatibility" 
            :value="old('backward_compatibility', $tag->backward_compatibility ?? '')" 
            placeholder="Optional legacy identifier" 
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="tag" 
    :cancel-route="$tag ? route('tags.show', $tag) : route('tags.index')"
/>
