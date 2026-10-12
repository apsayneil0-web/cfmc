<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Shared storage logic for a farmer's document fields (Valid ID, Certificate
 * of Title, Barangay Certification, RSBSA), each of which accepts one or more
 * uploaded files. Used by both the Manager's membership form and the Admin's
 * farmer-registration form, which otherwise duplicate this exact behavior.
 */
trait StoresFarmerDocuments
{
    /**
     * Store every uploaded file submitted under a document field (e.g.
     * "certificate_of_title[]") and return their storage paths, for a brand
     * new farmer record with nothing on file yet.
     */
    private function storeFarmerDocumentPaths(Request $request, string $field): ?string
    {
        $paths = $this->storeUploadedFiles($request, $field);

        return $paths ? json_encode($paths) : null;
    }

    /**
     * Apply a document field's edits: delete any existing files the user
     * checked for removal, store any newly uploaded ones, and merge the
     * result back into a single JSON array for the column — or null if
     * nothing is left on file at all.
     */
    private function applyFarmerDocumentUpdate(Request $request, Farmer $farmer, array &$updateData, string $field, string $pathColumn): void
    {
        $remaining = $farmer->documentPaths($pathColumn);
        $toRemove = (array) $request->input('remove_'.$field, []);

        if ($toRemove) {
            foreach ($toRemove as $path) {
                if (in_array($path, $remaining, true)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $remaining = array_values(array_diff($remaining, $toRemove));
        }

        $newPaths = $this->storeUploadedFiles($request, $field);
        $final = array_merge($remaining, $newPaths);

        $updateData[$pathColumn] = $final ? json_encode($final) : null;
    }

    /**
     * Store every file submitted for a field, whether it arrived as a single
     * upload or an array of them, and return their storage paths.
     */
    private function storeUploadedFiles(Request $request, string $field): array
    {
        if (! $request->hasFile($field)) {
            return [];
        }

        $files = $request->file($field);
        $files = is_array($files) ? $files : [$files];

        return collect($files)
            ->filter()
            // Several files in one batch can share an original name (e.g.
            // a phone camera's generic "IMG_0001.jpg"), so time() alone
            // isn't unique enough once more than one file can be stored
            // per field — a short random prefix avoids silent overwrites.
            ->map(fn ($file) => $file->storeAs('farmer_documents', time().'_'.Str::random(6).'_'.$file->getClientOriginalName(), 'public'))
            ->values()
            ->all();
    }
}
