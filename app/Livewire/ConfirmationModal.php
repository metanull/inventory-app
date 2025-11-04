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

    public function showConfirmation(array $data): void
    {
        $this->title = $data['title'] ?? 'Are you sure?';
        $this->message = $data['message'] ?? 'This operation cannot be undone.';
        $this->confirmLabel = $data['confirmLabel'] ?? 'Confirm';
        $this->cancelLabel = $data['cancelLabel'] ?? 'Cancel';
        $this->color = $data['color'] ?? 'red';
        $this->action = $data['action'] ?? null;
        $this->method = $data['method'] ?? 'DELETE';
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
