<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;

class ClientsImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure
{
    use SkipsFailures;

    public function __construct(protected string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @param array $row
     *
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
}
