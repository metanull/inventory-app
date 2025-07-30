{{-- Welcome Hero Banner Component --}}
<div class="bg-gradient-to-br from-blue-800 via-blue-700 to-blue-900 text-white overflow-hidden shadow-xl sm:rounded-lg">
    <div class="p-6 lg:p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold leading-tight">
                    {{ $title }}
                </h1>
                
                <p class="mt-4 text-blue-100 text-lg leading-relaxed max-w-2xl">
                    {{ $description }}
                </p>
                
                @if(isset($actions))
                    <div class="mt-8 flex flex-wrap gap-4">
                        {{ $actions }}
                    </div>
                @endif
            </div>
            
            @if(isset($illustration))
                <div class="hidden lg:block">
                    {{ $illustration }}
                </div>
            @endif
        </div>
    </div>
</div>
