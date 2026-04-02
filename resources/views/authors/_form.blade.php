@csrf

<div class="p-6 space-y-6">
    <div class="space-y-6">
        <x-form.field label="Name" name="name" variant="gray" required>
            <x-form.input 
                name="name" 
                :value="old('name', $author->name ?? '')" 
                required 
                placeholder="Author's full name"
            />
        </x-form.field>

        <x-form.field label="First Name" name="firstname" variant="gray">
            <x-form.input 
                name="firstname" 
                :value="old('firstname', $author->firstname ?? '')" 
                placeholder="First name"
            />
        </x-form.field>

        <x-form.field label="Last Name" name="lastname" variant="gray">
            <x-form.input 
                name="lastname" 
                :value="old('lastname', $author->lastname ?? '')" 
                placeholder="Last name"
            />
        </x-form.field>

        <x-form.field label="Given Name" name="givenname" variant="gray">
            <x-form.input 
                name="givenname" 
                :value="old('givenname', $author->givenname ?? '')" 
                placeholder="Given name (if different)"
            />
        </x-form.field>

        <x-form.field label="Original Name" name="originalname" variant="gray">
            <x-form.input 
                name="originalname" 
                :value="old('originalname', $author->originalname ?? '')" 
                placeholder="Name in original script"
            />
        </x-form.field>

        <x-form.field label="Internal Name" name="internal_name" variant="gray">
            <x-form.input 
                name="internal_name" 
                :value="old('internal_name', $author->internal_name ?? '')" 
                placeholder="Optional internal reference name"
            />
        </x-form.field>

        <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
            <x-form.input 
                name="backward_compatibility" 
                :value="old('backward_compatibility', $author->backward_compatibility ?? '')" 
                placeholder="Optional legacy identifier"
            />
        </x-form.field>
    </div>
</div>

<x-form.actions 
    entity="authors" 
    :cancel-route="$author ? route('authors.show', $author) : route('authors.index')"
/>
