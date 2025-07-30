@props(['team', 'component' => 'dropdown-link'])

<form method="POST" action="{{ route('current-team.update') }}" x-data>
    @method('PUT')
    @csrf

    <!-- Hidden Team ID -->
    <input type="hidden" name="team_id" value="{{ $team->id }}">

    <x-dynamic-component :component="$component" href="#" x-on:click.prevent="$root.submit();">
        <div class="flex items-center">
            @if (Auth::user()->isCurrentTeam($team))
                <x-heroicon-o-check-circle class="me-2 size-5 text-green-400" />
            @endif

            <div class="truncate">{{ $team->name }}</div>
        </div>
    </x-dynamic-component>
</form>
