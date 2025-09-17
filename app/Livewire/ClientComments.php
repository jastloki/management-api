<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Client;
use App\Models\ClientComment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClientComments extends Component
{
    public Client $client;
    public string $newComment = "";
    public string $newTitle = "";

    protected $rules = [
        "newComment" => "required|string|min:3|max:1000",
        "newTitle" => "nullable|string|max:255",
    ];

    protected $messages = [
        "newComment.required" => "Comment is required.",
        "newComment.min" => "Comment must be at least 3 characters.",
        "newComment.max" => "Comment cannot exceed 1000 characters.",
        "newTitle.max" => "Title cannot exceed 255 characters.",
    ];

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function addComment(): void
    {
        $this->validate();

        ClientComment::create([
            "client_id" => $this->client->id,
            "user_id" => Auth::id(),
            "comment" => trim($this->newComment),
            "title" => trim($this->newTitle) ?: null,
            "status" => "active",
            "type" => "comment",
        ]);

        $this->reset(["newComment", "newTitle"]);

        $this->dispatch("comment-added");

        session()->flash("success", "Comment added successfully!");
    }

    public function deleteComment(int $commentId): void
    {
        $comment = ClientComment::where("id", $commentId)
            ->where("client_id", $this->client->id)
            ->where("user_id", Auth::id())
            ->first();

        if ($comment) {
            $comment->delete();
            session()->flash("success", "Comment deleted successfully!");
        }
    }

    /**
     * Get all comments for the client.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int,\App\Models\ClientComment>
     */
    public function getCommentsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->client
            ->comments()
            ->with("user")
            ->orderBy("created_at", "desc")
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): \Illuminate\Contracts\View\View
    {
        return view("livewire.client-comments", [
            "comments" => $this->comments,
        ]);
    }
}
