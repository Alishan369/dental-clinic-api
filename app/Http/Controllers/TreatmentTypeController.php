<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TreatmentType;
use App\Http\Resources\TreatmentTypeResource;
use Illuminate\Support\Facades\Validator;

class TreatmentTypeController extends Controller
{
    public function index()
    {
        $types = TreatmentType::all();
        return $this->successResponse(TreatmentTypeResource::collection($types), 'Treatment types retrieved successfully');
    }

    public function show($id)
    {
        $type = TreatmentType::find($id);
        if (!$type) {
            return $this->errorResponse('Treatment type not found', 404);
        }
        return $this->successResponse(new TreatmentTypeResource($type), 'Treatment type retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type = TreatmentType::create([
            'name' => $request->name,
            'description' => $request->description,
            'base_cost' => $request->base_cost,
        ]);

        return $this->successResponse(new TreatmentTypeResource($type), 'Treatment type created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $type = TreatmentType::find($id);
        if (!$type) {
            return $this->errorResponse('Treatment type not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'base_cost' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $type->update($request->only(['name', 'description', 'base_cost']));

        return $this->successResponse(new TreatmentTypeResource($type), 'Treatment type updated successfully');
    }

    public function destroy($id)
    {
        $type = TreatmentType::find($id);
        if (!$type) {
            return $this->errorResponse('Treatment type not found', 404);
        }
        $type->delete();
        return $this->successResponse(null, 'Treatment type deleted successfully');
    }
}
