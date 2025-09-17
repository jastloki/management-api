<div class="card">
    <div class="card-header bg-white border-0">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h6 class="card-title mb-0">
                    <i class="bi bi-chat-dots me-2 text-primary"></i>Comments
                </h6>
                <p class="text-muted small mb-0">Add and view comments for this client</p>
            </div>
            <span class="badge bg-primary">{{ $comments->count() }}</span>
        </div>
    </div>

    <div class="card-body">
        <!-- Add Comment Form -->
        @can('clients.comment.create')
        <form wire:submit="addComment" class="mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="newTitle" class="form-label">
                        <i class="bi bi-bookmark me-1"></i>Title (Optional)
                    </label>
                    <input type="text"
                           class="form-control @error('newTitle') is-invalid @enderror"
                           id="newTitle"
                           wire:model="newTitle"
                           placeholder="Comment title...">
                    @error('newTitle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="newComment" class="form-label">
                    <i class="bi bi-chat me-1"></i>Comment <span class="text-danger">*</span>
                </label>
                <textarea class="form-control @error('newComment') is-invalid @enderror"
                          id="newComment"
                          wire:model="newComment"
                          rows="3"
                          placeholder="Write your comment here..."></textarea>
                @error('newComment')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    <span wire:ignore>
                        <span x-data="{ count: 0 }"
                              x-init="count = $wire.newComment.length"
                              x-on:input="count = $event.target.value.length">
                            <span x-text="count"></span>/1000 characters
                        </span>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="submit"
                        class="btn btn-primary"
                        wire:loading.attr="disabled"
                        wire:target="addComment">
                    <span wire:loading.remove wire:target="addComment">
                        <i class="bi bi-plus-lg me-1"></i>Add Comment
                    </span>
                    <span wire:loading wire:target="addComment">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Adding...
                    </span>
                </button>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-0 py-2 px-3" role="alert">
                        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>
        </form>
        @endcan

        <hr class="my-4">

        <!-- Comments List -->
        @if($comments->count() > 0)
            <div class="comments-list">
                @foreach($comments as $comment)
                    <div class="comment-item border rounded p-3 mb-3 position-relative"
                         style="background-color: #f8f9fa;">

                        <!-- Comment Header -->
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                    <span class="text-white small fw-bold">
                                        {{ strtoupper(substr($comment->user->name ?? 'U', 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $comment->user->name ?? 'Unknown User' }}</h6>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        {{ $comment->created_at->format('M d, Y \a\t g:i A') }}
                                        @if($comment->created_at != $comment->updated_at)
                                            <span class="text-info">(edited)</span>
                                        @endif
                                    </small>
                                </div>
                            </div>

                            @if($comment->user_id === auth()->id())
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            type="button"
                                            data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item text-danger"
                                                    wire:click="deleteComment({{ $comment->id }})"
                                                    wire:confirm="Are you sure you want to delete this comment?">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Comment Title -->
                        @if($comment->title)
                            <h6 class="text-primary mb-2">
                                <i class="bi bi-bookmark me-1"></i>{{ $comment->title }}
                            </h6>
                        @endif

                        <!-- Comment Content -->
                        <div class="comment-content">
                            <p class="mb-0" style="white-space: pre-wrap;">{{ $comment->comment }}</p>
                        </div>


                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-chat-dots text-muted" style="font-size: 3rem;"></i>
                </div>
                <h6 class="text-muted">No comments yet</h6>
                <p class="text-muted small mb-0">Be the first to add a comment for this client.</p>
            </div>
        @endif
    </div>
</div>
