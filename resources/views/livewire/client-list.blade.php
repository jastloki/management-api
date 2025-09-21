<div x-data="{ showAssignModal: @entangle('showAssignModal'), showDeleteModal: @entangle('showDeleteModal'), showConvertModal: @entangle('showConvertModal') }">
    <div class="relative">
        <!-- Top Actions Bar -->
        <div class="mb-4 flex items-center justify-between gap-4">
            <div class="flex-1">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Search clients..."
                    class="w-full rounded-lg border px-4 py-2 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/50"
                >
            </div>
            <div class="flex items-center gap-2">
                @if(count($selectedClients) > 0)
                    <button
                        wire:click="confirmAssign"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="fas fa-user-plus mr-2"></i>
                        Assign Selected
                    </button>
                    <button
                        wire:click="confirmDelete"
                        class="btn btn-error btn-sm"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>
                        Delete Selected
                    </button>
                @endif
            </div>
        </div>

        <!-- Clients Table -->
        <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="w-12 px-6 py-3">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="checkbox checkbox-sm"
                            >
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Client</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Assigned To</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    value="{{ $client->id }}"
                                    wire:model.live="selectedClients"
                                    class="checkbox checkbox-sm"
                                >
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <span class="avatar-sm">
                                            {{ substr($client->name, 0, 2) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900">{{ $client->name }}</div>
                                        <div class="text-gray-500">{{ $client->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-{{ $client->status_color }}-100 px-2 text-xs font-semibold leading-5 text-{{ $client->status_color }}-800">
                                    {{ $client->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $client->user?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="dropdown dropdown-end">
                                    <button class="btn btn-ghost btn-sm" tabindex="0">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul tabindex="0" class="dropdown-content menu rounded-box w-52 bg-base-100 p-2 shadow">
                                        <li>
                                            <a href="{{ route('admin.clients.show', $client) }}" class="text-gray-600 hover:text-gray-900">
                                                <i class="fas fa-eye mr-2"></i> View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('admin.clients.edit', $client) }}" class="text-gray-600 hover:text-gray-900">
                                                <i class="fas fa-edit mr-2"></i> Edit
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No clients found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- Assign Modal -->
    <div class="modal" x-bind:class="{ 'modal-open': showAssignModal }">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Assign Clients to User
            </h3>
            <div class="modal-body py-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Select User</span>
                    </label>
                    <select wire:model="selectedUser" class="select select-bordered w-full">
                        <option value="">Select a user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedUser')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>
            </div>
            <div class="modal-action">
                <button wire:click="assignToUser" class="btn btn-primary">
                    Assign
                </button>
                <button wire:click="$set('showAssignModal', false)" class="btn">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" x-bind:class="{ 'modal-open': showDeleteModal }">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Confirm Delete
            </h3>
            <div class="modal-body py-4">
                <p>Are you sure you want to delete the selected clients? This action cannot be undone.</p>
            </div>
            <div class="modal-action">
                <button wire:click="deleteSelected" class="btn btn-error">
                    Delete
                </button>
                <button wire:click="$set('showDeleteModal', false)" class="btn">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Convert Confirmation Modal -->
    <div class="modal" x-bind:class="{ 'modal-open': showConvertModal }">
        <div class="modal-box">
            <h3 class="text-lg font-bold">
                Convert to Client
            </h3>
            <div class="modal-body py-4">
                <p>Are you sure you want to convert the selected leads to clients? This will mark them as converted.</p>
            </div>
            <div class="modal-action">
                <button wire:click="convertSelected" class="btn btn-success">
                    Convert
                </button>
                <button wire:click="$set('showConvertModal', false)" class="btn">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    @error('selection')
        <div x-data="{ show: true }" x-show="show" class="alert alert-error mt-4">
            <div class="flex-1">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <label>{{ $message }}</label>
            </div>
            <div class="flex-none">
                <button @click="show = false" class="btn btn-ghost btn-sm">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @enderror
</div>
