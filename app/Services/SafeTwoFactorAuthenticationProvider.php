<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Laravel\Fortify\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;

class SafeTwoFactorAuthenticationProvider extends TwoFactorAuthenticationProvider
{
    /**
     * Verify the given token.
     * This extends Fortify's provider to handle invalid base32 secrets gracefully.
     *
     * @param  string  $secret
     * @param  string  $code
     * @return bool
     */
    public function verify($secret, $code)
    {
        try {
            return parent::verify($secret, $code);
        } catch (InvalidCharactersException $e) {
            // Handle invalid Base32 characters in secret
            return false;
        } catch (SecretKeyTooShortException $e) {
            // Handle secret that's too short
            return false;
        } catch (IncompatibleWithGoogleAuthenticatorException $e) {
            // Handle other Google2FA compatibility issues
            return false;
        } catch (\Exception $e) {
            // Handle any other exception that might occur during TOTP validation
            Log::warning('TOTP verification failed with exception', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            return false;
        }
    }
}
