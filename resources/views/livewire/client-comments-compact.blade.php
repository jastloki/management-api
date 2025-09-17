<div class="client-comments-compact">
    <!-- Flash Message -->
    @if(session("comment-success-{$client->id}"))
        <div class="alert alert-success alert-dismissible fade show mb-2 py-1 px-2 small" role="alert">
            <i class="bi bi-check-circle me-1"></i>{{ session("comment-success-{$client->id}") }}
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Comments Summary -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center">
            <i class="bi bi-chat-dots me-1 text-muted"></i>
            <span class="small text-muted">
                {{ $commentsCount }} comment{{ $commentsCount !== 1 ? 's' : '' }}
            </span>
        </div>
        @can('clients.comment.create')

        <button type="button"
                class="btn btn-sm btn-outline-primary"
                wire:click="toggleCommentForm">
            @if($showCommentForm)
                <i class="bi bi-x-lg"></i>
            @else
                <i class="bi bi-plus-lg"></i>
            @endif
        </button>
        @endcan

    </div>

    <!-- Last Comment Display -->
    @if($lastComment)
        <div class="last-comment p-2 border rounded mb-2" style="background-color: #f8f9fa;">
            <div class="d-flex align-items-start">
                <div class="avatar-xs bg-primary rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0">
                    <span class="text-white" style="font-size: 0.6rem; font-weight: bold;">
                        {{ strtoupper(substr($lastComment->user->name ?? 'U', 0, 1)) }}
                    </span>
                </div>
                <div class="flex-grow-1 min-width-0">
                    @if($lastComment->title)
                        <div class="text-primary small fw-semibold mb-1" style="font-size: 0.75rem;">
                            {{ \Illuminate\Support\Str::limit($lastComment->title, 25) }}
                        </div>
                    @endif
                    <p class="mb-1 small text-dark" style="font-size: 0.75rem; line-height: 1.3;">
                        {{ \Illuminate\Support\Str::limit($lastComment->comment, 50) }}
                    </p>
                    <small class="text-muted" style="font-size: 0.65rem;">
                        <i class="bi bi-person me-1"></i>{{ $lastComment->user->name ?? 'Unknown' }}
                        <i class="bi bi-clock ms-2 me-1"></i>{{ $lastComment->created_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Add Comment Form -->
    @if($showCommentForm)
        <div class="quick-comment-form border rounded p-2 mb-2" style="background-color: #fff;">
            <form wire:submit="addComment">
                <div class="mb-2">
                    <textarea class="form-control form-control-sm @error('newComment') is-invalid @enderror"
                              wire:model="newComment"
                              rows="3"
                              placeholder="Add a quick comment..."
                              style="font-size: 0.8rem;"></textarea>
                    @error('newComment')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 0.65rem;">
                        <span x-data="{ count: 0 }"
                              x-init="count = $wire.newComment.length"
                              x-on:input="count = $event.target.value.length">
                            <span x-text="count"></span>/500
                        </span>
                    </small>

                    <div class="btn-group btn-group-sm">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                wire:click="toggleCommentForm"
                                style="font-size: 0.7rem;">
                            Cancel
                        </button>
                        <button type="submit"
                                class="btn btn-primary btn-sm"
                                wire:loading.attr="disabled"
                                wire:target="addComment"
                                style="font-size: 0.7rem;">
                            <span wire:loading.remove wire:target="addComment">
                                <i class="bi bi-check-lg me-1"></i>Add
                            </span>
                            <span wire:loading wire:target="addComment">
                                <span class="spinner-border spinner-border-sm me-1" style="width: 0.7rem; height: 0.7rem;"></span>
                                Adding...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- No Comments State -->
    @if($commentsCount === 0 && !$showCommentForm)
        <div class="text-center py-2">
            <small class="text-muted" style="font-size: 0.75rem;">
                <i class="bi bi-chat-dots me-1"></i>No comments yet
            </small>
        </div>
    @endif


</div>
