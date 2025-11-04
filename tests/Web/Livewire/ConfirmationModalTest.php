<?php

namespace Tests\Web\Livewire;

use App\Livewire\ConfirmationModal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConfirmationModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->assertOk();
    }

    public function test_modal_is_hidden_by_default(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->assertSet('show', false);
    }

    public function test_can_show_confirmation(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [
                'title' => 'Delete Item',
                'message' => 'This will delete the item permanently',
            ])
            ->assertSet('show', true)
            ->assertSet('title', 'Delete Item')
            ->assertSee('Delete Item');
    }

    public function test_can_close_modal(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->set('show', true)
            ->call('close')
            ->assertSet('show', false);
    }

    public function test_confirm_dispatches_event_and_closes(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->set('show', true)
            ->set('action', '/test-action')
            ->set('method', 'DELETE')
            ->call('confirm')
            ->assertDispatched('confirmed')
            ->assertSet('show', false);
    }

    public function test_uses_default_values_when_not_provided(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [])
            ->assertSet('title', 'Are you sure?')
            ->assertSet('message', 'This operation cannot be undone.')
            ->assertSet('confirmLabel', 'Confirm')
            ->assertSet('cancelLabel', 'Cancel')
            ->assertSet('color', 'red');
    }

    public function test_respects_custom_labels(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [
                'confirmLabel' => 'Delete Forever',
                'cancelLabel' => 'Keep It',
            ])
            ->assertSet('confirmLabel', 'Delete Forever')
            ->assertSet('cancelLabel', 'Keep It')
            ->assertSee('Delete Forever')
            ->assertSee('Keep It');
    }

    public function test_supports_different_colors(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', ['color' => 'indigo'])
            ->assertSet('color', 'indigo');
    }

    public function test_stores_action_and_method(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [
                'action' => '/items/123',
                'method' => 'DELETE',
            ])
            ->assertSet('action', '/items/123')
            ->assertSet('method', 'DELETE');
    }

    public function test_resets_state_after_close(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [
                'title' => 'Custom Title',
                'message' => 'Custom Message',
                'confirmLabel' => 'Custom Confirm',
                'cancelLabel' => 'Custom Cancel',
                'color' => 'indigo',
                'action' => '/test',
                'method' => 'POST',
            ])
            ->call('close')
            ->assertSet('title', 'Are you sure?')
            ->assertSet('message', 'This operation cannot be undone.')
            ->assertSet('confirmLabel', 'Confirm')
            ->assertSet('cancelLabel', 'Cancel')
            ->assertSet('color', 'red')
            ->assertSet('action', null)
            ->assertSet('method', 'DELETE');
    }

    public function test_displays_custom_message(): void
    {
        Livewire::test(ConfirmationModal::class)
            ->dispatch('confirm-action', [
                'message' => 'This is a custom warning message',
            ])
            ->assertSee('This is a custom warning message');
    }

    public function test_can_handle_multiple_show_hide_cycles(): void
    {
        $component = Livewire::test(ConfirmationModal::class);

        // First cycle
        $component->dispatch('confirm-action', ['title' => 'First'])
            ->assertSet('show', true)
            ->assertSee('First')
            ->call('close')
            ->assertSet('show', false);

        // Second cycle
        $component->dispatch('confirm-action', ['title' => 'Second'])
            ->assertSet('show', true)
            ->assertSee('Second')
            ->call('close')
            ->assertSet('show', false);
    }
}
