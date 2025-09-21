<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ShouldQueueWithoutChain;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;

class ClientsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithBatchInserts,
    WithChunkReading,
    ShouldQueueWithoutChain
{
    use SkipsFailures;

    protected bool $converted = true;

    public function __construct(
        protected string $fileName,
        bool $converted = true,
    ) {
        $this->fileName = $fileName;
        $this->converted = $converted;
    }

    /**
     * @param array $row
     * b
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Client([
            "name" => $row["name"],
            "email" => $row["email"],
            "phone" => $row["phone"] ?? null,
            "company" => $row["company"] ?? null,
            "address" => $row["address"] ?? null,
            "status" => $row["status"] ?? "active",
            "email_status" => "pending",
            "imported_from" => $this->fileName,
            "converted" => false,
        ]);
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            "name" => "required|string|max:255",
            "email" => ["required", "email", Rule::unique("clients", "email")],
            "phone" => "nullable|string|max:20",
            "company" => "nullable|string|max:255",
            "address" => "nullable|string",
            "status" => "nullable|in:active,inactive",
        ];
    }

    /**
     * Custom attribute names for validation errors
     */
    public function customValidationAttributes()
    {
        return [
            "name" => "client name",
            "email" => "email address",
            "phone" => "phone number",
            "company" => "company name",
            "address" => "address",
            "status" => "status",
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            "email.unique" =>
                "A client with this email address already exists.",
            "status.in" => 'Status must be either "active" or "inactive".',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
