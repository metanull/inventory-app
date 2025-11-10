<?php

namespace App\Livewire;

use Livewire\Component;

class ConfirmationModal extends Component
{
    public bool $show = false;

    public string $title = 'Are you sure?';

    public string $message = 'This operation cannot be undone.';

    public string $confirmLabel = 'Confirm';

    public string $cancelLabel = 'Cancel';

    public string $color = 'red';

    public ?string $action = null;

    public string $method = 'DELETE';

    protected $listeners = ['confirm-action' => 'showConfirmation'];

    public function showConfirmation(
        ?string $title = null,
        ?string $message = null,
        ?string $confirmLabel = null,
        ?string $cancelLabel = null,
        ?string $color = null,
        ?string $action = null,
        ?string $method = null
    ): void {
        $this->title = $title ?? 'Are you sure?';
        $this->message = $message ?? 'This operation cannot be undone.';
        $this->confirmLabel = $confirmLabel ?? 'Confirm';
        $this->cancelLabel = $cancelLabel ?? 'Cancel';
        $this->color = $color ?? 'red';
        $this->action = $action;
        $this->method = $method ?? 'DELETE';
        $this->show = true;
    }

    public function confirm(): void
    {
        $this->dispatch('confirmed', [
            'action' => $this->action,
            'method' => $this->method,
        ]);
        $this->close();
    }

    public function close(): void
    {
        $this->show = false;
        $this->resetState();
    }

    protected function resetState(): void
    {
        $this->title = 'Are you sure?';
        $this->message = 'This operation cannot be undone.';
        $this->confirmLabel = 'Confirm';
        $this->cancelLabel = 'Cancel';
        $this->color = 'red';
        $this->action = null;
        $this->method = 'DELETE';
    }

    public function render()
    {
        return view('livewire.confirmation-modal');
    }
}
