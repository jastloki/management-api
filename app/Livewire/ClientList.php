<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Client;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class ClientList extends Component
{
    use WithPagination;

    public array $selectedClients = [];
    public bool $selectAll = false;
    public string $selectedUser = "";
    public bool $showAssignModal = false;
    public bool $showDeleteModal = false;
    public string $search = "";

    protected $queryString = ["search"];

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedClients = $this->clients
                ->pluck("id")
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedClients = [];
        }
    }

    public function updatedSelectedClients(): void
    {
        $this->selectAll =
            count($this->selectedClients) === $this->clients->count();
    }

    public function confirmAssign(): void
    {
        if (empty($this->selectedClients)) {
            $this->addError("selection", "Please select at least one client.");
            return;
        }

        $this->showAssignModal = true;
    }

    public function assignToUser(): void
    {
        $this->validate([
            "selectedUser" => "required|exists:users,id",
        ]);

        Client::query()
            ->whereIn("id", $this->selectedClients)
            ->update([
                "assigned_to" => $this->selectedUser,
            ]);

        $this->showAssignModal = false;
        $this->selectedClients = [];
        $this->selectAll = false;
        $this->selectedUser = "";

        $this->dispatch("alert", [
            "type" => "success",
            "message" => "Clients assigned successfully!",
        ]);
    }

    public function confirmDelete(): void
    {
        if (empty($this->selectedClients)) {
            $this->addError("selection", "Please select at least one client.");
            return;
        }

        $this->showDeleteModal = true;
    }

    public function deleteSelected(): void
    {
        Client::query()->whereIn("id", $this->selectedClients)->delete();

        $this->showDeleteModal = false;
        $this->selectedClients = [];
        $this->selectAll = false;

        $this->dispatch("alert", [
            "type" => "success",
            "message" => "Clients deleted successfully!",
        ]);
    }

    public function confirmConvert(): void
    {
        if (empty($this->selectedClients)) {
            $this->addError("selection", "Please select at least one client.");
            return;
        }

        $this->showConvertModal = true;
    }

    public function convertSelected(): void
    {
        Client::query()
            ->whereIn("id", $this->selectedClients)
            ->update([
                "converted" => true,
            ]);

        $this->showConvertModal = false;
        $this->selectedClients = [];
        $this->selectAll = false;

        $this->dispatch("alert", [
            "type" => "success",
            "message" => "Clients converted successfully!",
        ]);
    }

    public function convertSingle(int $clientId): void
    {
        Client::query()
            ->where("id", $clientId)
            ->update([
                "converted" => true,
            ]);

        $this->dispatch("alert", [
            "type" => "success",
            "message" => "Client converted successfully!",
        ]);
    }

    #[Computed]
    public function clients(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Client::query()
            ->with(["status", "user"])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query
                        ->where("name", "like", "%" . $this->search . "%")
                        ->orWhere("email", "like", "%" . $this->search . "%")
                        ->orWhere("company", "like", "%" . $this->search . "%");
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function users(): \Illuminate\Database\Eloquent\Collection
    {
        return User::query()->orderBy("name")->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view("livewire.client-list", [
            "clients" => $this->clients,
            "users" => $this->users,
        ]);
    }
}
