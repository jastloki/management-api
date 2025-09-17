<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Client;
use App\Models\ClientComment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientCommentsCompact extends Component
{
    public Client $client;
    public string $newComment = "";
    public bool $showCommentForm = false;

    protected $rules = [
        "newComment" => "required|string|min:3|max:500",
    ];

    protected $messages = [
        "newComment.required" => "Comment is required.",
        "newComment.min" => "Comment must be at least 3 characters.",
        "newComment.max" => "Comment cannot exceed 500 characters.",
    ];

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function toggleCommentForm(): void
    {
        $this->showCommentForm = !$this->showCommentForm;
        if (!$this->showCommentForm) {
            $this->reset(["newComment"]);
            $this->resetErrorBag();
        }
    }

    public function addComment(): void
    {
        $this->validate();

        ClientComment::create([
            "client_id" => $this->client->id,
            "user_id" => Auth::id(),
            "comment" => trim($this->newComment),
            "title" => null,
            "status" => "active",
            "type" => "comment",
        ]);

        $this->reset(["newComment"]);
        $this->showCommentForm = false;

        $this->dispatch("comment-added");

        session()->flash(
            "comment-success-{$this->client->id}",
            "Comment added successfully!",
        );
    }

    /**
     * Get the last comment for the client.
     *
     * @return \App\Models\ClientComment|null
     */
    public function getLastCommentProperty(): ?\App\Models\ClientComment
    {
        return $this->client
            ->comments()
            ->with("user")
            ->orderBy("created_at", "desc")
            ->first();
    }

    /**
     * Get the total count of comments for the client.
     *
     * @return int
     */
    public function getCommentsCountProperty(): int
    {
        return $this->client->comments()->count();
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view("livewire.client-comments-compact", [
            "lastComment" => $this->lastComment,
            "commentsCount" => $this->commentsCount,
        ]);
    }
}
