{{--
    CSRF Token Refresh Component
    
    This component fixes 419 "Page Expired" errors that occur when password managers
    autofill login forms with stale CSRF tokens. The script removes any old token
    (saved by the password manager) and injects a fresh token from the meta tag
    before form submission.
    
    Usage:
        <form method="POST" action="{{ route('login') }}" id="login-form">
            <!-- form fields (no @csrf directive needed) -->
        </form>
        <x-auth.csrf-refresh formId="login-form" />
    
    Laravel Documentation: https://laravel.com/docs/12.x/csrf#csrf-x-csrf-token
--}}

@props(['formId'])

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('{{ $formId }}');
        if (!form) {
            console.warn('CSRF refresh: Form with ID "{{ $formId }}" not found');
            return;
        }
        
        form.addEventListener('submit', function(e) {
            // Remove any old token field (password manager may have restored it)
            const oldToken = this.querySelector('input[name="_token"]');
            if (oldToken) {
                oldToken.remove();
            }
            
            // Add fresh token from meta tag
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (!metaTag) {
                console.error('CSRF refresh: Meta tag with name="csrf-token" not found');
                return;
            }
            
            const token = metaTag.getAttribute('content');
            if (token) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = '_token';
                input.value = token;
                this.appendChild(input);
            }
        });
    });
</script>
