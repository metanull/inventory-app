<?php

namespace Tests\Web\Traits;

use Illuminate\Testing\TestResponse;

/**
 * Provides reusable form validation assertion methods for web tests.
 *
 * This trait centralizes common validation testing patterns used across
 * authentication tests (LoginTest, RegistrationTest, PasswordResetTest, etc.).
 */
trait TestsFormValidation
{
    /**
     * Assert that a field is required by the form.
     *
     * @param  string  $route  The route name or URL to post to
     * @param  string  $field  The field name that should be required
     * @param  array  $validData  Valid form data (field will be removed from this)
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertFieldRequired(string $route, string $field, array $validData, string $method = 'post'): TestResponse
    {
        // Remove the field from valid data
        $invalidData = $validData;
        unset($invalidData[$field]);

        // Submit without the required field
        $response = $this->$method($route, $invalidData);

        // Assert validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors([$field]);
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that a field must be a valid email address.
     *
     * @param  string  $route  The route name or URL to post to
     * @param  string  $field  The field name that should be a valid email
     * @param  array  $validData  Valid form data (field will be replaced with invalid email)
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertFieldValidEmail(string $route, string $field, array $validData, string $method = 'post'): TestResponse
    {
        // Replace email with invalid format
        $invalidData = array_merge($validData, [
            $field => 'invalid-email-format',
        ]);

        // Submit with invalid email
        $response = $this->$method($route, $invalidData);

        // Assert validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors([$field]);
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that a field must meet minimum length requirement.
     *
     * @param  string  $route  The route name or URL to post to
     * @param  string  $field  The field name that has a min length requirement
     * @param  int  $minLength  The minimum length required
     * @param  array  $validData  Valid form data (field will be replaced with short value)
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertFieldMinLength(string $route, string $field, int $minLength, array $validData, string $method = 'post'): TestResponse
    {
        // Create a value that's too short (one character less than minimum)
        $shortValue = str_repeat('x', max(0, $minLength - 1));
        $invalidData = array_merge($validData, [
            $field => $shortValue,
        ]);

        // Submit with too-short value
        $response = $this->$method($route, $invalidData);

        // Assert validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors([$field]);
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that a field must be unique (e.g., email not already taken).
     *
     * @param  string  $route  The route name or URL to post to
     * @param  string  $field  The field name that must be unique
     * @param  mixed  $existingValue  The value that already exists in database
     * @param  array  $validData  Valid form data (field will be replaced with existing value)
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertFieldUnique(string $route, string $field, mixed $existingValue, array $validData, string $method = 'post'): TestResponse
    {
        // Replace field with existing value
        $invalidData = array_merge($validData, [
            $field => $existingValue,
        ]);

        // Submit with duplicate value
        $response = $this->$method($route, $invalidData);

        // Assert validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors([$field]);
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that a password confirmation field must match the password field.
     *
     * @param  string  $route  The route name or URL to post to
     * @param  string  $passwordField  The password field name (default: 'password')
     * @param  string  $confirmationField  The password confirmation field name (default: 'password_confirmation')
     * @param  array  $validData  Valid form data
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertPasswordConfirmationMatch(
        string $route,
        array $validData,
        string $passwordField = 'password',
        string $confirmationField = 'password_confirmation',
        string $method = 'post'
    ): TestResponse {
        // Set mismatched password confirmation
        $invalidData = array_merge($validData, [
            $passwordField => 'password123',
            $confirmationField => 'different-password',
        ]);

        // Submit with mismatched confirmation
        $response = $this->$method($route, $invalidData);

        // Assert validation error
        $response->assertRedirect();
        $response->assertSessionHasErrors([$passwordField]);
        $this->assertGuest();

        return $response;
    }

    /**
     * Assert that multiple fields are required by the form.
     *
     * @param  string  $route  The route name or URL to post to
     * @param  array  $fields  Array of field names that should be required
     * @param  string  $method  HTTP method to use (default: 'post')
     */
    protected function assertFieldsRequired(string $route, array $fields, string $method = 'post'): TestResponse
    {
        // Submit with empty data
        $response = $this->$method($route, []);

        // Assert validation errors for all fields
        $response->assertRedirect();
        $response->assertSessionHasErrors($fields);
        $this->assertGuest();

        return $response;
    }
}
