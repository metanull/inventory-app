@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @php($c = $entityColor('contacts'))
    
    <div>
        <a href="{{ route('contacts.index') }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
    </div>
    
    <x-entity.header entity="contacts" title="Contact Details">
        <div class="flex gap-2">
            @can(\App\Enums\Permission::UPDATE_DATA->value)
                <a href="{{ route('contacts.edit', $contact) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">
                    <x-heroicon-o-pencil class="w-5 h-5 mr-1" />
                    Edit
                </a>
            @endcan
            @can(\App\Enums\Permission::DELETE_DATA->value)
                <form method="POST" action="{{ route('contacts.destroy', $contact) }}" onsubmit="return confirm('Are you sure you want to delete this contact?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">
                        <x-heroicon-o-trash class="w-5 h-5 mr-1" />
                        Delete
                    </button>
                </form>
            @endcan
        </div>
    </x-entity.header>

    @if(session('success'))
        <x-ui.alert :message="session('success')" type="success" entity="contacts" />
    @endif

    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Internal Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->internal_name }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->formattedPhoneNumber() ?? '-' }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Fax Number</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->formattedFaxNumber() ?? '-' }}</dd>
                </div>

                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->email ?? '-' }}</dd>
                </div>

                @if($contact->translations->count() > 0)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Translations</dt>
                        <dd class="mt-1">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Language</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Label</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($contact->translations as $translation)
                                        <tr>
                                            <td class="px-3 py-2 text-sm text-gray-900">{{ $translation->language->internal_name }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-500">{{ $translation->label }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </dd>
                    </div>
                @endif

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->created_at->format('Y-m-d H:i') }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $contact->updated_at->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
