<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MedicalDocument;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;

class MedicalDocumentController extends Controller
{
    public function index($patientId)
    {
        try {
            $documents = MedicalDocument::where('patient_id', $patientId)->latest()->get();
            return $this->successResponse($documents, 'Documents retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(Request $request, $patientId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:x-ray,report,prescription',
                'file' => 'required|file|mimes:jpeg,png,pdf|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors()->first(), 422);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("documents/{$patientId}", $fileName, 'public');

            $document = MedicalDocument::create([
                'patient_id' => $patientId,
                'type' => $request->type,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            return $this->successResponse($document, 'Document uploaded successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            $document = MedicalDocument::findOrFail($id);
            Storage::disk('public')->delete($document->file_path);
            $document->delete();

            return $this->successResponse(null, 'Document deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
